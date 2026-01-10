<?php
/*
* Clase RateLimitMiddleware
*
* Middleware para control de rate limiting (límite de solicitudes)
* Previene abuso mediante limitación de requests por IP
* Utiliza archivos temporales para almacenar contadores
*
* @package App\Middleware
*/

//Seteo del Namespace
namespace App\Middleware;

//Se instancias otras clases
use App\Core\Config;
use App\Core\Logger;
use App\Helpers\ResponseHelper;

//Se crea la clase
class RateLimitMiddleware {
    /*
    *===========================================================================
    * @vars
    */
    private $maxRequests; //@var int     Número máximo de solicitudes permitidas
    private $timeWindow;  //@var int     Ventana de tiempo en segundos
    private $logger;      //@var Logger  Logger para registrar eventos
    private $storageDir;  //@var string  Directorio para almacenar datos de rate limit

    /*
    *===========================================================================
    * Constructor
    *
    * @param Logger|null $logger Logger
    */
    public function __construct($logger = null) {
        $this->maxRequests = (int) Config::get('RATE_LIMIT_REQUESTS', 100);
        $this->timeWindow  = (int) Config::get('RATE_LIMIT_WINDOW', 60);
        $this->logger      = $logger ?? new Logger();
        $this->storageDir  = sys_get_temp_dir() . '/rate_limit/';

        // Crear directorio si no existe
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    /*
    *===========================================================================
    * Maneja el rate limiting del request
    *
    * @param \stdClass $request Objeto request
    * @return \stdClass|false Request sin modificar o false si se excede el límite
    */
    public function handle($request) {
        $ip       = $this->getClientIp();
        $key      = $this->generateKey($ip);
        $filepath = $this->storageDir . $key . '.json';

        // Obtener datos actuales
        $data = $this->getData($filepath);
        $now  = time();

        // Limpiar requests antiguos fuera de la ventana de tiempo
        $data['requests'] = array_filter($data['requests'], function ($timestamp) use ($now) {
            return ($now - $timestamp) < $this->timeWindow;
        });

        // Verificar si se excede el límite
        if (count($data['requests']) >= $this->maxRequests) {
            $this->logger->warning('Rate limit excedido', [
                'ip'       => $ip,
                'requests' => count($data['requests']),
                'max'      => $this->maxRequests
            ]);

            $retryAfter = $this->timeWindow - ($now - min($data['requests']));
            // Respuesta
            ResponseHelper::tooManyRequests(
                'Límite de solicitudes excedido. Intente nuevamente más tarde.',
                $retryAfter
            );
            return false;
        }

        // Agregar request actual
        $data['requests'][] = $now;
        $this->saveData($filepath, $data);

        return $request;
    }

    /*
    *===========================================================================
    * Obtiene la IP del cliente
    *
    * @return string IP del cliente
    */
    private function getClientIp() {
        // Verificar headers de proxy
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];

                // Si es una lista de IPs, tomar la primera
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }

                return $ip;
            }
        }

        return 'unknown';
    }

    /*
    *===========================================================================
    * Genera una clave única para el almacenamiento
    *
    * @param string $ip IP del cliente
    * @return string Clave generada
    */
    private function generateKey($ip) {
        return md5($ip);
    }

    /*
    *===========================================================================
    * Obtiene los datos de rate limit desde el archivo
    *
    * @param string $filepath Ruta del archivo
    * @return array Datos de rate limit
    */
    private function getData($filepath) {
        if (!file_exists($filepath)) {
            return ['requests' => []];
        }

        $content = file_get_contents($filepath);
        $data    = json_decode($content, true);

        return $data ?? ['requests' => []];
    }

    /*
    *===========================================================================
    * Guarda los datos de rate limit en el archivo
    *
    * @param string  $filepath  Ruta del archivo
    * @param array   $data      Datos a guardar
    * @return void
    */
    private function saveData($filepath, $data) {
        file_put_contents($filepath, json_encode($data), LOCK_EX);
    }

    /*
    *===========================================================================
    * Limpia archivos antiguos de rate limit (mantenimiento)
    * Se recomienda ejecutar periódicamente mediante cron
    *
    * @return void
    */
    public function cleanup() {
        $files = glob($this->storageDir . '*.json');
        $now   = time();

        foreach ($files as $file) {
            // Eliminar archivos más antiguos que la ventana de tiempo
            if (($now - filemtime($file)) > $this->timeWindow) {
                unlink($file);
            }
        }
    }
}
