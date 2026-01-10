<?php
/*
* Clase SucursalRepository
*
* Repositorio para gestionar operaciones de base de datos relacionadas con sucursales
* Proporciona abstracción de acceso a datos para la tabla sucursal_listado
*
* @package App\Repositories
*/

//Seteo del Namespace
namespace App\Repositories;

//Se instancias otras clases
use App\Core\Database;
use App\Core\Logger;
use PDOException;

//Se crea la clase
class SucursalRepository {
    /*
    *===========================================================================
    * @vars
    */
    private $db;     //@var Database  Instancia de la base de datos
    private $logger; //@var Logger    Logger para registrar eventos

    /*
    *===========================================================================
    * Constructor
    *
    * @param Database|null $db      Instancia de base de datos
    * @param Logger|null   $logger  Logger
    */
    public function __construct($db = null, $logger = null) {
        $this->db     = $db ?? Database::getInstance();
        $this->logger = $logger ?? new Logger();
    }

    /*
    *===========================================================================
    * Busca una sucursal por su nombre
    *
    * @param string $nombre Nombre de la sucursal
    * @return array|null Datos de la sucursal o null si no existe
    */
    public function findByNombre($nombre) {
        try {
            $sql    = "SELECT idSucursal, Nombre FROM sucursal_listado WHERE Nombre = :nombre LIMIT 1";
            $stmt   = $this->db->query($sql, ['nombre' => $nombre]);
            $result = $stmt->fetch();

            if ($result) {
                $this->logger->info('Sucursal encontrada', [
                    'idSucursal' => $result['idSucursal'],
                    'nombre'     => $nombre
                ]);
                return $result;
            }

            return null;
        } catch (PDOException $e) {
            $this->logger->error('Error al buscar sucursal', [
                'nombre' => $nombre,
                'error'  => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /*
    *===========================================================================
    * Busca una sucursal por su ID
    *
    * @param int $id ID de la sucursal
    * @return array|null Datos de la sucursal o null si no existe
    */
    public function findById($id) {
        try {
            $sql  = "SELECT idSucursal, Nombre FROM sucursal_listado WHERE idSucursal = :id LIMIT 1";
            $stmt = $this->db->query($sql, ['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->logger->error('Error al buscar sucursal por ID', [
                'id'    => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /*
    *===========================================================================
    * Obtiene todas las sucursales
    *
    * @return array Lista de sucursales
    */
    public function findAll() {
        try {
            $sql  = "SELECT idSucursal, Nombre FROM sucursal_listado ORDER BY Nombre";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error('Error al obtener sucursales', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /*
    *===========================================================================
    * Crea una nueva sucursal
    * Valida que el nombre sea único
    *
    * @param string $nombre Nombre de la sucursal
    * @return int ID de la sucursal creada
    * @throws \Exception Si el nombre ya existe
    */
    public function create($nombre) {
        try {
            // Verificar que no exista
            $existing = $this->findByNombre($nombre);
            if ($existing) {
                throw new \Exception("Ya existe una sucursal con el nombre: {$nombre}");
            }

            $sql = "INSERT INTO sucursal_listado (Nombre) VALUES (:nombre)";
            $this->db->query($sql, ['nombre' => $nombre]);

            $id = $this->db->lastInsertId();
            $this->logger->info('Sucursal creada', [
                'idSucursal' => $id,
                'nombre'     => $nombre
            ]);

            return $id;
        } catch (PDOException $e) {
            $this->logger->error('Error al crear sucursal', [
                'nombre' => $nombre,
                'error'  => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /*
    *===========================================================================
    * Obtiene o crea una sucursal por nombre
    * Si existe, retorna su ID; si no, la crea
    *
    * @param string $nombre Nombre de la sucursal
    * @return int ID de la sucursal
    */
    public function getOrCreate($nombre) {
        $sucursal = $this->findByNombre($nombre);

        if ($sucursal) {
            return $sucursal['idSucursal'];
        }

        return $this->create($nombre);
    }
}
