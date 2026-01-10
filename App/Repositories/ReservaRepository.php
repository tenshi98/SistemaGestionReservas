<?php
/*
* Clase ReservaRepository
*
* Repositorio para gestionar operaciones de base de datos relacionadas con reservas
* Proporciona abstracción de acceso a datos para la tabla reservas_listado
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
class ReservaRepository {
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
    * Busca reservas por Email y Fecha
    *
    * @param string $email  Email a buscar
    * @param string $fecha  Fecha a buscar
    * @return array|null Datos de la reserva más reciente o null si no existe
    */
    public function findByEmailAndFecha($email, $fecha) {
        try {
            $sql = "SELECT * FROM reservas_listado 
                    WHERE Email = :email AND Fecha = :fecha 
                    ORDER BY idReservas DESC 
                    LIMIT 1";

            $stmt = $this->db->query($sql, [
                'email' => $email,
                'fecha' => $fecha
            ]);

            $result = $stmt->fetch();

            if ($result) {
                $this->logger->info('Reserva encontrada', [
                    'idReservas' => $result['idReservas'],
                    'email'      => $email
                ]);
            }

            return $result ?: null;
        } catch (PDOException $e) {
            $this->logger->error('Error al buscar reserva', [
                'email' => $email,
                'fecha' => $fecha,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /*
    *===========================================================================
    * Crea una nueva reserva
    *
    * @param array $data Datos de la reserva
    * @return int ID de la reserva creada
    */
    public function create($data) {
        try {
            $sql = "INSERT INTO reservas_listado (
                        idToken, Nombre, ApellidoPat, ApellidoMat, NombreCompleto, 
                        Email, idSucursal, Etapa, Fecha, FechaAcceso, idSendMail
                    ) VALUES (
                        :idToken, :nombre, :apellidoPat, :apellidoMat, :nombreCompleto,
                        :email, :idSucursal, :etapa, :fecha, :fechaAcceso, :idSendMail
                    )";

            $params = [
                'idToken'        => $data['idToken'],
                'nombre'         => $data['Nombre'] ?? '',
                'apellidoPat'    => $data['ApellidoPat'] ?? '',
                'apellidoMat'    => $data['ApellidoMat'] ?? '',
                'nombreCompleto' => $data['NombreCompleto'],
                'email'          => $data['Email'],
                'idSucursal'     => $data['idSucursal'],
                'etapa'          => $data['Etapa'],
                'fecha'          => $data['Fecha'],
                'fechaAcceso'    => $data['FechaAcceso'],
                'idSendMail'     => $data['idSendMail'] ?? 0
            ];

            $this->db->query($sql, $params);
            $id = $this->db->lastInsertId();

            $this->logger->info('Reserva creada', [
                'idReservas' => $id,
                'email'      => $data['Email']
            ]);

            return $id;
        } catch (PDOException $e) {
            $this->logger->error('Error al crear reserva', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
    }

    /*
    *===========================================================================
    * Actualiza una reserva existente
    *
    * @param int    $id    ID de la reserva
    * @param array  $data  Datos a actualizar
    * @return bool True si se actualizó correctamente
    */
    public function update($id, $data) {
        try {
            $sql = "UPDATE reservas_listado SET 
                        idSucursal = :idSucursal,
                        Etapa = :etapa,
                        FechaAcceso = :fechaAcceso
                    WHERE idReservas = :id";

            $params = [
                'id'          => $id,
                'idSucursal'  => $data['idSucursal'],
                'etapa'       => $data['Etapa'],
                'fechaAcceso' => $data['FechaAcceso']
            ];

            $this->db->query($sql, $params);

            $this->logger->info('Reserva actualizada', [
                'idReservas' => $id
            ]);

            return true;
        } catch (PDOException $e) {
            $this->logger->error('Error al actualizar reserva', [
                'id'    => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /*
    *===========================================================================
    * Filtra reservas según criterios
    * Incluye JOIN con sucursal_listado
    *
    * @param array $criteria Criterios de filtrado
    * @return array Lista de reservas filtradas
    */
    public function filter($criteria) {
        try {
            $sql = "SELECT r.*, s.Nombre as NombreSucursal 
                    FROM reservas_listado r
                    INNER JOIN sucursal_listado s ON r.idSucursal = s.idSucursal
                    WHERE 1=1";

            $params = [];

            // Filtro por idToken (siempre presente)
            if (isset($criteria['idToken'])) {
                $sql              .= " AND r.idToken = :idToken";
                $params['idToken'] = $criteria['idToken'];
            }

            // Filtro por idSucursal (puede ser uno o varios)
            if (isset($criteria['idSucursal'])) {
                if (is_array($criteria['idSucursal'])) {
                    $placeholders = [];
                    foreach ($criteria['idSucursal'] as $index => $sucursalId) {
                        $key            = "idSucursal{$index}";
                        $placeholders[] = ":{$key}";
                        $params[$key]   = $sucursalId;
                    }
                    $sql .= " AND r.idSucursal IN (" . implode(',', $placeholders) . ")";
                } else {
                    $sql .= " AND r.idSucursal = :idSucursal";
                    $params['idSucursal'] = $criteria['idSucursal'];
                }
            }

            // Filtro por Etapa
            if (isset($criteria['Etapa']) && !empty($criteria['Etapa'])) {
                $sql            .= " AND r.Etapa = :etapa";
                $params['etapa'] = $criteria['Etapa'];
            }

            // Filtro por Fecha (específica o rango)
            if (isset($criteria['Fecha'])) {
                $sql            .= " AND r.Fecha = :fecha";
                $params['fecha'] = $criteria['Fecha'];
            } elseif (isset($criteria['FechaInicio']) && isset($criteria['FechaFin'])) {
                $sql                  .= " AND r.Fecha BETWEEN :fechaInicio AND :fechaFin";
                $params['fechaInicio'] = $criteria['FechaInicio'];
                $params['fechaFin']    = $criteria['FechaFin'];
            }

            // Filtro por idSendMail
            if (isset($criteria['idSendMail'])) {
                $sql                 .= " AND r.idSendMail = :idSendMail";
                $params['idSendMail'] = $criteria['idSendMail'];
            }

            // Ordenar por más reciente
            $sql .= " ORDER BY r.idReservas DESC";

            $stmt    = $this->db->query($sql, $params);
            $results = $stmt->fetchAll();

            $this->logger->info('Filtro de reservas ejecutado', [
                'criteria'      => $criteria,
                'results_count' => count($results)
            ]);

            return $results;
        } catch (PDOException $e) {
            $this->logger->error('Error al filtrar reservas', [
                'criteria' => $criteria,
                'error'    => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /*
    *===========================================================================
    * Obtiene todas las reservas
    *
    * @return array Lista de todas las reservas
    */
    public function findAll() {
        try {
            $sql = "SELECT r.*, s.Nombre as NombreSucursal 
                    FROM reservas_listado r
                    INNER JOIN sucursal_listado s ON r.idSucursal = s.idSucursal
                    ORDER BY r.idReservas DESC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error('Error al obtener todas las reservas', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
