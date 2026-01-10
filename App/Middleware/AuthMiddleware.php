<?php
/*
* Clase AuthMiddleware
*
* Middleware para autenticación mediante Bearer Token
* Valida el token contra la tabla tokens_listado
* Inyecta el idToken en el request para uso posterior
*
* @package App\Middleware
*/

//Seteo del Namespace
namespace App\Middleware;

//Se instancias otras clases
use App\Core\Logger;
use App\Helpers\ResponseHelper;
use App\Repositories\TokenRepository;

//Se crea la clase
class AuthMiddleware {
    /*
    *===========================================================================
    * @vars
    */
    private $tokenRepository; //@var TokenRepository  Repositorio de tokens
    private $logger;          //@var Logger           Logger para registrar eventos

    /*
    *===========================================================================
    * Constructor
    *
    * @param TokenRepository|null  $tokenRepository  Repositorio de tokens
    * @param Logger|null           $logger           Logger
    */
    public function __construct($tokenRepository = null, $logger = null) {
        $this->tokenRepository = $tokenRepository ?? new TokenRepository();
        $this->logger          = $logger ?? new Logger();
    }

    /*
    *===========================================================================
    * Maneja la autenticación del request
    *
    * @param \stdClass $request Objeto request
    * @return \stdClass|false Request modificado con idToken o false si falla
    */
    public function handle($request) {
        // Extraer token del header Authorization
        $token = $this->extractToken($request);

        if (!$token) {
            $this->logger->warning('Token no proporcionado', [
                'uri' => $request->uri,
                'ip'  => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            // Respuesta
            ResponseHelper::unauthorized('Token de autenticación no proporcionado');
            return false;
        }

        // Validar token en base de datos
        $tokenData = $this->tokenRepository->findByToken($token);

        if (!$tokenData) {
            $this->logger->warning('Token inválido', [
                'token' => substr($token, 0, 10) . '...',
                'uri'   => $request->uri,
                'ip'    => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            // Respuesta
            ResponseHelper::unauthorized('Token de autenticación inválido');
            return false;
        }

        // Inyectar idToken en el request
        $request->idToken   = $tokenData['idToken'];
        $request->tokenName = $tokenData['Nombre'];

        $this->logger->info('Autenticación exitosa', [
            'idToken'   => $tokenData['idToken'],
            'tokenName' => $tokenData['Nombre']
        ]);

        return $request;
    }

    /*
    *===========================================================================
    * Extrae el token del header Authorization
    *
    * @param \stdClass $request Objeto request
    * @return string|null Token extraído o null si no existe
    */
    private function extractToken($request) {
        $authHeader = $request->headers['AUTHORIZATION'] ?? null;

        if (!$authHeader) {
            return null;
        }

        // Formato esperado: "Bearer {token}"
        if (preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
