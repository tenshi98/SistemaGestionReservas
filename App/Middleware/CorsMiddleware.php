<?php
/*
* Clase CorsMiddleware
*
* Middleware para manejar CORS (Cross-Origin Resource Sharing)
* Permite que la API sea consumida desde diferentes orígenes
*
* @package App\Middleware
*/

//Seteo del Namespace
namespace App\Middleware;

//Se instancias otras clases
use App\Core\Config;

//Se crea la clase
class CorsMiddleware {
    /*
    *===========================================================================
    * @vars
    */
    private $allowedOrigins; //@var string Orígenes permitidos
    private $allowedMethods; //@var string Métodos HTTP permitidos
    private $allowedHeaders; //@var string Headers permitidos

    /*
    *===========================================================================
    * Constructor
    */
    public function __construct() {
        $this->allowedOrigins = Config::get('CORS_ALLOWED_ORIGINS', '*');
        $this->allowedMethods = Config::get('CORS_ALLOWED_METHODS', 'GET,POST,OPTIONS');
        $this->allowedHeaders = Config::get('CORS_ALLOWED_HEADERS', 'Content-Type,Authorization');
    }

    /*
    *===========================================================================
    * Maneja los headers CORS del request
    *
    * @param \stdClass $request Objeto request
    * @return \stdClass Request sin modificar
    */
    public function handle($request) {
        // Establecer headers CORS
        header('Access-Control-Allow-Origin: ' . $this->allowedOrigins);
        header('Access-Control-Allow-Methods: ' . $this->allowedMethods);
        header('Access-Control-Allow-Headers: ' . $this->allowedHeaders);
        header('Access-Control-Max-Age: 86400'); // 24 horas

        // Si es una solicitud OPTIONS (preflight), responder inmediatamente
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        return $request;
    }
}
