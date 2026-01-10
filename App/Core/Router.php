<?php
/*
* Clase Router
*
* Sistema de enrutamiento robusto con soporte para:
* - Métodos HTTP (GET, POST, PUT, DELETE, etc.)
* - Parámetros dinámicos en rutas
* - Middleware pipeline
* - Manejo de errores 404
*
* @package App\Core
*/

//Seteo del Namespace
namespace App\Core;

//Se crea la clase
class Router {
    /*
    *===========================================================================
    * @vars
    */
    private $routes           = []; //@var array   Rutas registradas
    private $globalMiddleware = []; //@var array   Middleware globales
    private $logger;                //@var Logger  Logger para registrar eventos

    /*
    *===========================================================================
    * Constructor
    *
    * @param Logger|null $logger Instancia del logger
    */
    public function __construct($logger = null) {
        $this->logger = $logger ?? new Logger();
    }

    /*
    *===========================================================================
    * Registra una ruta GET
    *
    * @param string          $path        Ruta (puede incluir parámetros como /user/{id})
    * @param callable|array  $handler     Función o array [clase, método] que maneja la ruta
    * @param array           $middleware  Middleware específicos para esta ruta
    * @return void
    */
    public function get($path, $handler, array $middleware = []) {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    /*
    *===========================================================================
    * Registra una ruta POST
    *
    * @param string          $path        Ruta
    * @param callable|array  $handler     Handler de la ruta
    * @param array           $middleware  Middleware específicos
    * @return void
    */
    public function post($path, $handler, array $middleware = []) {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    /*
    *===========================================================================
    * Registra una ruta para cualquier método HTTP
    *
    * @param string          $method      Método HTTP
    * @param string          $path        Ruta
    * @param callable|array  $handler     Handler de la ruta
    * @param array           $middleware  Middleware específicos
    * @return void
    */
    private function addRoute($method, $path, $handler, array $middleware = []) {
        $this->routes[] = [
            'method'     => $method,
            'path'       => $path,
            'handler'    => $handler,
            'middleware' => $middleware,
            'pattern'    => $this->convertToPattern($path)
        ];
    }

    /*
    *===========================================================================
    * Convierte una ruta con parámetros a expresión regular
    * Ejemplo: /user/{id} -> /user/([^/]+)
    *
    * @param string $path Ruta original
    * @return string Patrón regex
    */
    private function convertToPattern($path) {
        // Escapar caracteres especiales excepto {}
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /*
    *===========================================================================
    * Extrae los nombres de parámetros de una ruta
    *
    * @param string $path Ruta con parámetros
    * @return array Nombres de parámetros
    */
    private function extractParamNames($path) {
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $path, $matches);
        return $matches[1] ?? [];
    }

    /*
    *===========================================================================
    * Agrega middleware global que se ejecutará en todas las rutas
    *
    * @param callable|object $middleware Middleware a agregar
    * @return void
    */
    public function addGlobalMiddleware($middleware) {
        $this->globalMiddleware[] = $middleware;
    }

    /*
    *===========================================================================
    * Despacha la solicitud a la ruta correspondiente
    *
    * @return void
    */
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remover trailing slash excepto para la raíz
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }

        $this->logger->info('Solicitud recibida', [
            'method' => $method,
            'uri'    => $uri,
            'ip'     => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);

        // Buscar ruta coincidente
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Remover el match completo
                array_shift($matches);

                // Extraer nombres de parámetros y crear array asociativo
                $paramNames = $this->extractParamNames($route['path']);
                $params     = [];

                foreach ($paramNames as $index => $name) {
                    $params[$name] = $matches[$index] ?? null;
                }

                // Ejecutar middleware y handler
                $this->executeRoute($route, $params);
                return;
            }
        }

        // No se encontró la ruta
        $this->handleNotFound($uri);
    }

    /*
    *===========================================================================
    * Ejecuta el middleware pipeline y el handler de la ruta
    *
    * @param array $route   Información de la ruta
    * @param array $params  Parámetros extraídos de la URL
    * @return void
    */
    private function executeRoute($route, $params) {
        try {
            // Combinar middleware globales y específicos de la ruta
            $middleware = array_merge($this->globalMiddleware, $route['middleware']);

            // Crear objeto request simple
            $request = new \stdClass();
            $request->params   = $params;
            $request->query    = $_GET;
            $request->body     = $this->getRequestBody();
            $request->headers  = $this->getRequestHeaders();
            $request->method   = $_SERVER['REQUEST_METHOD'];
            $request->uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            // Ejecutar middleware
            foreach ($middleware as $mw) {
                $result = $this->executeMiddleware($mw, $request);

                // Si el middleware retorna false, detener ejecución
                if ($result === false) {
                    return;
                }

                // Si el middleware modificó el request, actualizarlo
                if ($result instanceof \stdClass) {
                    $request = $result;
                }
            }

            // Ejecutar handler
            $handler = $route['handler'];

            if (is_callable($handler)) {
                call_user_func($handler, $request);
            } elseif (is_array($handler) && count($handler) === 2) {
                list($class, $method) = $handler;
                $controller = new $class();
                $controller->$method($request);
            }
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    /*
    *===========================================================================
    * Ejecuta un middleware
    *
    * @param callable|object  $middleware  Middleware a ejecutar
    * @param \stdClass        $request     Objeto request
    * @return mixed Resultado del middleware
    */
    private function executeMiddleware($middleware, $request) {
        if (is_callable($middleware)) {
            return call_user_func($middleware, $request);
        } elseif (is_object($middleware) && method_exists($middleware, 'handle')) {
            return $middleware->handle($request);
        }

        return true;
    }

    /*
    *===========================================================================
    * Obtiene el cuerpo de la solicitud
    *
    * @return array|null Datos del cuerpo parseados
    */
    private function getRequestBody() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            return json_decode($json, true) ?? [];
        }

        return $_POST;
    }

    /*
    *===========================================================================
    * Obtiene los headers de la solicitud
    *
    * @return array Headers de la solicitud
    */
    private function getRequestHeaders() {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[$header] = $value;
            }
        }

        // Agregar Authorization header si existe
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers['AUTHORIZATION'] = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $headers['AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        return $headers;
    }

    /*
    *===========================================================================
    * Maneja errores 404
    *
    * @param string $uri URI solicitada
    * @return void
    */
    private function handleNotFound($uri) {
        $this->logger->warning('Ruta no encontrada', ['uri' => $uri]);

        http_response_code(404);
        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'error'   => 'Ruta no encontrada',
            'uri'     => $uri
        ], JSON_UNESCAPED_UNICODE);
    }

    /*
    *===========================================================================
    * Maneja errores generales
    *
    * @param \Exception $e Excepción capturada
    * @return void
    */
    private function handleError($e) {
        $this->logger->error('Error en la aplicación', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString()
        ]);

        http_response_code(500);
        header('Content-Type: application/json');

        $response = [
            'success' => false,
            'error'   => 'Error interno del servidor'
        ];

        // Mostrar detalles solo en desarrollo
        if (Config::get('APP_DEBUG', false) === 'true') {
            $response['debug'] = [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ];
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}
