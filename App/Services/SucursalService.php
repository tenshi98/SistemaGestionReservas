<?php
/*
* Clase SucursalService
*
* Servicio para gestionar la lógica de negocio relacionada con sucursales
* Orquesta operaciones entre repositorios y validadores
*
* @package App\Services
*/

//Seteo del Namespace
namespace App\Services;

//Se instancias otras clases
use App\Core\Logger;
use App\Repositories\SucursalRepository;
use App\Validators\DataValidator;

//Se crea la clase
class SucursalService {
    /*
    *===========================================================================
    * @vars
    */
    private $sucursalRepository; //@var SucursalRepository  Repositorio de sucursales
    private $validator;          //@var DataValidator       Validador de datos
    private $logger;             //@var Logger              Logger para registrar eventos

    /*
    *===========================================================================
    * Constructor
    *
    * @param SucursalRepository|null  $sucursalRepository  Repositorio de sucursales
    * @param DataValidator|null       $validator           Validador
    * @param Logger|null              $logger              Logger
    */
    public function __construct($sucursalRepository = null, $validator = null, $logger = null) {
        $this->sucursalRepository  = $sucursalRepository ?? new SucursalRepository();
        $this->validator           = $validator ?? new DataValidator();
        $this->logger              = $logger ?? new Logger();
    }

    /*
    *===========================================================================
    * Obtiene o crea una sucursal por nombre
    * Valida y sanitiza el nombre antes de procesar
    *
    * @param string $nombre Nombre de la sucursal
    * @return int ID de la sucursal
    * @throws \Exception Si la validación falla
    */
    public function getOrCreateSucursal($nombre) {
        // Sanitizar nombre
        $nombre = $this->validator->sanitizeString($nombre);

        // Validar
        if (!$this->validator->validate(['nombre' => $nombre], ['nombre' => ['required', 'minLength:2', 'maxLength:255']])) {
            $errors = $this->validator->getErrors();
            $this->logger->warning('Validación de sucursal falló', ['errors' => $errors]);
            throw new \Exception('Datos de sucursal inválidos: ' . json_encode($errors));
        }

        // Obtener o crear
        $idSucursal = $this->sucursalRepository->getOrCreate($nombre);

        return $idSucursal;
    }

    /*
    *===========================================================================
    * Obtiene todas las sucursales
    *
    * @return array Lista de sucursales
    */
    public function getAllSucursales() {
        return $this->sucursalRepository->findAll();
    }
}
