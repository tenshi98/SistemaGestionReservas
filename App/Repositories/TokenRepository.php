<?php
/*
* Clase TokenRepository
*
* Repositorio para gestionar operaciones de base de datos relacionadas con tokens
* Proporciona abstracción de acceso a datos para la tabla tokens_listado
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
class TokenRepository {
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
    * @param Database|null  $db      Instancia de base de datos
    * @param Logger|null    $logger  Logger
    */
    public function __construct($db = null, $logger = null) {
        $this->db     = $db ?? Database::getInstance();
        $this->logger = $logger ?? new Logger();
    }

    /*
    *===========================================================================
    * Busca un token por su valor
    *
    * @param string $token Token a buscar
    * @return array|null Datos del token o null si no existe
    */
    public function findByToken($token) {
        try {
            $sql    = "SELECT idToken, Token, Nombre FROM tokens_listado WHERE Token = :token LIMIT 1";
            $stmt   = $this->db->query($sql, ['token' => $token]);
            $result = $stmt->fetch();

            if ($result) {
                $this->logger->info('Token encontrado', ['idToken' => $result['idToken']]);
                return $result;
            }

            $this->logger->warning('Token no encontrado', ['token' => substr($token, 0, 10) . '...']);
            return null;
        } catch (PDOException $e) {
            $this->logger->error('Error al buscar token', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /*
    *===========================================================================
    * Obtiene todos los tokens
    *
    * @return array Lista de tokens
    */
    public function findAll() {
        try {
            $sql  = "SELECT idToken, Token, Nombre FROM tokens_listado";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error('Error al obtener tokens', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /*
    *===========================================================================
    * Crea un nuevo token
    *
    * @param array $data Datos del token
    * @return int ID del token creado
    */
    public function create($data) {
        try {
            $sql = "INSERT INTO tokens_listado (Token, Nombre) VALUES (:token, :nombre)";
            $this->db->query($sql, [
                'token'  => $data['Token'],
                'nombre' => $data['Nombre']
            ]);

            $id = $this->db->lastInsertId();
            $this->logger->info('Token creado', ['idToken' => $id]);

            return $id;
        } catch (PDOException $e) {
            $this->logger->error('Error al crear token', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
