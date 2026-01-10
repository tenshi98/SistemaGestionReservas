<?php
/*
* Clase CronController
*
* Controlador para endpoints de cron jobs
* Maneja tareas programadas del sistema
*
* @package App\Controllers
*/

//Seteo del Namespace
namespace App\Controllers;

//Se instancias otras clases
use App\Core\Logger;
use App\Helpers\ResponseHelper;
use App\Repositories\TokenRepository;

//Se crea la clase
class CronController {
    /*
    *===========================================================================
    * @vars
    */
    private $tokenRepository; //@var tokenRepository  Repositorio de tokens
    private $logger;          //@var Logger           Logger para registrar eventos

    /*
    *===========================================================================
    * Constructor
    */
    public function __construct() {
        $this->tokenRepository = new TokenRepository();
        $this->logger          = new Logger();
    }

    /*
    *===========================================================================
    * Cron de prueba
    * Valida token y retorna mensaje de prueba
    *
    * @param \stdClass $request Objeto request
    * @return void
    */
    public function cron1($request) {
        // Obtener token desde parámetros de la URL
        $token = $request->params['token'] ?? null;

        if (!$token) {
            $this->logger->warning('Cron ejecutado sin token', [
                'uri' => $request->uri
            ]);
            // Respuesta
            ResponseHelper::unauthorized('Token no proporcionado');
            return;
        }

        // Validar token
        $tokenData = $this->tokenRepository->findByToken($token);

        if (!$tokenData) {
            $this->logger->warning('Cron ejecutado con token inválido', [
                'token' => substr($token, 0, 10) . '...'
            ]);
            // Respuesta
            ResponseHelper::unauthorized('Token inválido');
            return;
        }

        // Guardar idToken
        $idToken = $tokenData['idToken'];

        $this->logger->info('Cron1 ejecutado', [
            'idToken'   => $idToken,
            'tokenName' => $tokenData['Nombre']
        ]);

        // Respuesta de prueba
        ResponseHelper::success([
            'cron'        => 'cron1',
            'response'    => 'Hola mundo',
            'executed_at' => date('Y-m-d H:i:s'),
            'token_name'  => $tokenData['Nombre']
        ], 'Cron ejecutado correctamente');
    }
}
