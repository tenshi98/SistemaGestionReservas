<?php
/*
* Clase ReservaService
*
* Servicio para gestionar la lógica de negocio relacionada con reservas
* Orquesta operaciones complejas entre múltiples repositorios
* Maneja transacciones y validaciones de negocio
*
* @package App\Services
*/

//Seteo del Namespace
namespace App\Services;

//Se instancias otras clases
use App\Core\Database;
use App\Core\Logger;
use App\Repositories\ReservaRepository;
use App\Repositories\SucursalRepository;
use App\Validators\DataValidator;

//Se crea la clase
class ReservaService {
    /*
    *===========================================================================
    * @vars
    */
    private $reservaRepository;  //@var ReservaRepository   Repositorio de reservas
    private $sucursalRepository; //@var SucursalRepository  Repositorio de sucursales
    private $validator;          //@var DataValidator       Validador de datos
    private $db;                 //@var Database            Base de datos para transacciones
    private $logger;             //@var Logger              Logger para registrar eventos

    /*
    *===========================================================================
    * Constructor
    *
    * @param ReservaRepository|null   $reservaRepository   Repositorio de reservas
    * @param SucursalRepository|null  $sucursalRepository  Repositorio de sucursales
    * @param DataValidator|null       $validator           Validador
    * @param Database|null            $db                  Base de datos
    * @param Logger|null              $logger              Logger
    */
    public function __construct(
        $reservaRepository  = null,
        $sucursalRepository = null,
        $validator          = null,
        $db                 = null,
        $logger            = null
    ) {
        $this->reservaRepository  = $reservaRepository ?? new ReservaRepository();
        $this->sucursalRepository = $sucursalRepository ?? new SucursalRepository();
        $this->validator          = $validator ?? new DataValidator();
        $this->db                 = $db ?? Database::getInstance();
        $this->logger             = $logger ?? new Logger();
    }

    /*
    *===========================================================================
    * Procesa la recepción de datos de una reserva
    * Implementa la lógica de insert/update según Email y Fecha
    *
    * @param array  $data     Datos de la reserva
    * @param int    $idToken  ID del token autenticado
    * @return array Resultado de la operación
    * @throws \Exception Si la validación o procesamiento falla
    */
    public function processReservaData($data, $idToken) {
        // Validar datos requeridos
        $rules = [
            'NombreCompleto'  => ['required', 'maxLength:255'],
            'Email'           => ['required', 'email', 'maxLength:255'],
            'Sucursal'        => ['required', 'maxLength:255'],
            'Etapa'           => ['required', 'maxLength:255']
        ];

        if (!$this->validator->validate($data, $rules)) {
            $errors = $this->validator->getErrors();
            $this->logger->warning('Validación de reserva falló', ['errors' => $errors]);
            throw new \Exception('Datos de reserva inválidos: ' . json_encode($errors));
        }

        // Sanitizar datos
        $nombreCompleto  = $this->validator->sanitizeString($data['NombreCompleto']);
        $email           = $this->validator->sanitizeEmail($data['Email']);
        $sucursalNombre  = $this->validator->sanitizeString($data['Sucursal']);
        $etapa           = $this->validator->sanitizeString($data['Etapa']);

        // Datos opcionales
        $nombre      = isset($data['Nombre']) ? $this->validator->sanitizeString($data['Nombre']) : '';
        $apellidoPat = isset($data['ApellidoPat']) ? $this->validator->sanitizeString($data['ApellidoPat']) : '';
        $apellidoMat = isset($data['ApellidoMat']) ? $this->validator->sanitizeString($data['ApellidoMat']) : '';

        try {
            // Iniciar transacción
            $this->db->beginTransaction();

            // Obtener o crear sucursal
            $idSucursal = $this->sucursalRepository->getOrCreate($sucursalNombre);

            // Fecha actual
            $fecha       = date('Y-m-d');
            $fechaAcceso = date('Y-m-d H:i:s');

            // Buscar si existe reserva con mismo Email y Fecha
            $reservaExistente = $this->reservaRepository->findByEmailAndFecha($email, $fecha);

            if ($reservaExistente) {
                // Actualizar reserva existente
                $updateData = [
                    'idSucursal'  => $idSucursal,
                    'Etapa'       => $etapa,
                    'FechaAcceso' => $fechaAcceso
                ];

                $this->reservaRepository->update($reservaExistente['idReservas'], $updateData);

                $this->db->commit();

                $this->logger->info('Reserva actualizada', [
                    'idReservas' => $reservaExistente['idReservas'],
                    'email'      => $email
                ]);

                return [
                    'action'     => 'updated',
                    'idReservas' => $reservaExistente['idReservas'],
                    'message'    => 'Reserva actualizada correctamente'
                ];
            } else {
                // Crear nueva reserva
                $createData = [
                    'idToken'         => $idToken,
                    'Nombre'          => $nombre,
                    'ApellidoPat'     => $apellidoPat,
                    'ApellidoMat'     => $apellidoMat,
                    'NombreCompleto'  => $nombreCompleto,
                    'Email'           => $email,
                    'idSucursal'      => $idSucursal,
                    'Etapa'           => $etapa,
                    'Fecha'           => $fecha,
                    'FechaAcceso'     => $fechaAcceso,
                    'idSendMail'      => 0
                ];

                $idReservas = $this->reservaRepository->create($createData);

                $this->db->commit();

                $this->logger->info('Reserva creada', [
                    'idReservas' => $idReservas,
                    'email'      => $email
                ]);

                return [
                    'action'     => 'created',
                    'idReservas' => $idReservas,
                    'message'    => 'Reserva creada correctamente'
                ];
            }
        } catch (\Exception $e) {
            // Revertir transacción en caso de error
            $this->db->rollback();

            $this->logger->error('Error al procesar reserva', [
                'error' => $e->getMessage(),
                'email' => $email ?? 'unknown'
            ]);

            throw $e;
        }
    }

    /*
    *===========================================================================
    * Filtra reservas según criterios
    *
    * @param array  $criteria  Criterios de filtrado
    * @param int    $idToken   ID del token autenticado
    * @return array Lista de reservas filtradas
    */
    public function filterReservas($criteria, $idToken) {
        // Agregar idToken a los criterios
        $criteria['idToken'] = $idToken;

        // Sanitizar criterios
        if (isset($criteria['Etapa'])) {
            $criteria['Etapa'] = $this->validator->sanitizeString($criteria['Etapa']);
        }

        if (isset($criteria['Fecha'])) {
            $criteria['Fecha'] = $this->validator->sanitizeString($criteria['Fecha']);
        }

        if (isset($criteria['FechaInicio'])) {
            $criteria['FechaInicio'] = $this->validator->sanitizeString($criteria['FechaInicio']);
        }

        if (isset($criteria['FechaFin'])) {
            $criteria['FechaFin'] = $this->validator->sanitizeString($criteria['FechaFin']);
        }

        if (isset($criteria['idSendMail'])) {
            $criteria['idSendMail'] = $this->validator->sanitizeInt($criteria['idSendMail']);
        }

        // Sanitizar idSucursal (puede ser array o valor único)
        if (isset($criteria['idSucursal'])) {
            if (is_array($criteria['idSucursal'])) {
                $criteria['idSucursal'] = array_map(function ($id) {
                    return $this->validator->sanitizeInt($id);
                }, $criteria['idSucursal']);
            } else {
                $criteria['idSucursal'] = $this->validator->sanitizeInt($criteria['idSucursal']);
            }
        }

        // Ejecutar filtro
        $reservas = $this->reservaRepository->filter($criteria);

        return $reservas;
    }
}
