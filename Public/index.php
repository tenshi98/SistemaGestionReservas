<?php
/*
* Punto de entrada de la aplicación
*
* Este archivo inicializa la aplicación, configura el autoloading,
* registra las rutas y despacha las solicitudes
*/

// Mostrar errores en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Definir directorio raíz
define('ROOT_DIR', dirname(__DIR__));

// Autoloader simple para PSR-4
spl_autoload_register(function ($class) {
    // Convertir namespace a ruta de archivo
    $prefix = 'App\\';
    $base_dir = ROOT_DIR . '/App/';

    // Verificar si la clase usa el namespace App
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Obtener el nombre relativo de la clase
    $relative_class = substr($class, $len);

    // Reemplazar namespace separators con directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Si el archivo existe, requerirlo
    if (file_exists($file)) {
        require $file;
    }
});

// Importar clases necesarias
use App\Core\Config;
use App\Core\Router;
use App\Core\Logger;
use App\Middleware\CorsMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\AuthMiddleware;
use App\Controllers\IndexController;
use App\Controllers\CronController;
use App\Controllers\ApiController;

try {
    // Validar configuraciones requeridas
    Config::validate(['DB_HOST', 'DB_NAME', 'DB_USER']);

    // Crear instancias
    $logger = new Logger();
    $router = new Router($logger);

    // Middleware globales
    $corsMiddleware      = new CorsMiddleware();
    $rateLimitMiddleware = new RateLimitMiddleware($logger);

    $router->addGlobalMiddleware($corsMiddleware);
    $router->addGlobalMiddleware($rateLimitMiddleware);

    // Middleware de autenticación (se usa solo en rutas específicas)
    $authMiddleware = new AuthMiddleware(null, $logger);

    // ========================================
    // Registro de Rutas
    // ========================================

    // Ruta pública: Documentación
    $router->get('/', [IndexController::class, 'index']);

    // Ruta de cron (autenticación vía parámetro GET)
    $router->get('/Cron/cron1/{token}', [CronController::class, 'cron1']);

    // Rutas de API (requieren Bearer Token)
    $router->post('/API/v1/postData', [ApiController::class, 'postData'], [$authMiddleware]);
    $router->post('/API/v1/filter', [ApiController::class, 'filter'], [$authMiddleware]);

    // Despachar solicitud
    $router->dispatch();
} catch (Exception $e) {
    // Manejo de errores globales
    $logger = $logger ?? new Logger();
    $logger->error('Error fatal en la aplicación', [
        'error' => $e->getMessage(),
        'file'  => $e->getFile(),
        'line'  => $e->getLine()
    ]);

    http_response_code(500);
    header('Content-Type: application/json');

    $response = [
        'success' => false,
        'error'   => 'Error interno del servidor'
    ];

    // Mostrar detalles solo en desarrollo
    if (Config::get('APP_DEBUG', 'false') === 'true') {
        $response['debug'] = [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString()
        ];
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
