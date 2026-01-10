<?php
/*
* Clase ApiController
*
* Controlador para los endpoints de la API v1
* Maneja operaciones de reservas (postData y filter)
*
* @package App\Controllers
*/

//Seteo del Namespace
namespace App\Controllers;

//Se instancias otras clases
use App\Core\Logger;
use App\Helpers\ResponseHelper;
use App\Services\ReservaService;

//Se crea la clase
class ApiController {
    /*
    *===========================================================================
    * @vars
    */
    private $reservaService; //@var ReservaService   Servicio de reservas
    private $logger;         //@var Logger           Logger para registrar eventos

    /*
    *===========================================================================
    * Constructor
    */
    public function __construct() {
        $this->reservaService = new ReservaService();
        $this->logger         = new Logger();
    }

    /*
    *===========================================================================
    * Endpoint para recibir datos de reservas
    * POST /API/v1/postData
    *
    * @param \stdClass $request Objeto request (incluye idToken del middleware)
    * @return void
    */
    public function postData($request) {
        try {
            // Variables
            $idToken = $request->idToken; // Obtener idToken del middleware de autenticación
            $data    = $request->body;    // Obtener datos del body

            if (empty($data)) {
                // Respuesta
                ResponseHelper::error('No se recibieron datos', 400);
                return;
            }
            // Procesar reserva
            $result = $this->reservaService->processReservaData($data, $idToken);
            // Respuesta
            ResponseHelper::success($result);
        } catch (\Exception $e) {
            $this->logger->error('Error en postData', [
                'error'   => $e->getMessage(),
                'idToken' => $request->idToken ?? null
            ]);
            // Respuesta
            ResponseHelper::error($e->getMessage(), 400);
        }
    }

    /*
    *===========================================================================
    * Endpoint para filtrar reservas
    * POST /API/v1/filter
    *
    * @param \stdClass $request Objeto request (incluye idToken del middleware)
    * @return void
    */
    public function filter($request) {
        try {
            // Variables
            $idToken  = $request->idToken;                                          // Obtener idToken del middleware de autenticación
            $criteria = $request->body ?? [];                                       // Obtener criterios de filtrado del body
            $reservas = $this->reservaService->filterReservas($criteria, $idToken); // Filtrar reservas
            // Respuesta
            ResponseHelper::success([
                'total'    => count($reservas),
                'reservas' => $reservas
            ], 'Reservas filtradas correctamente');
        } catch (\Exception $e) {
            $this->logger->error('Error en filter', [
                'error'   => $e->getMessage(),
                'idToken' => $request->idToken ?? null
            ]);
            // Respuesta
            ResponseHelper::error($e->getMessage(), 400);
        }
    }
}
