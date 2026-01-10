<?php
/*
* Clase Database
*
* Gestiona la conexión a la base de datos MySQL usando PDO
* Implementa Singleton para reutilización de conexión
* Incluye manejo robusto de excepciones y reconexión automática
*
* @package App\Core
*/

//Seteo del Namespace
namespace App\Core;

//Se instancias otras clases
use PDO;
use PDOException;

//Se crea la clase
class Database {
    /*
    *===========================================================================
    * @vars
    */
    private static $instance = null;  //@var Database|null  Instancia única de la clase
    private $connection      = null;  //@var PDO|null       Conexión PDO a la base de datos
    private $logger;                  //@var Logger         Logger para registrar eventos
    private $config          = [];    //@var array          Configuración de la base de datos
    private $maxRetries      = 3;     //@var int            Número máximo de intentos de reconexión

    /*
    *===========================================================================
    * Constructor privado para implementar Singleton
    *
    * @param Logger|null $logger Instancia del logger
    */
    private function __construct($logger = null) {
        $this->logger = $logger ?? new Logger();
        $this->loadConfig();
    }

    /*
    *===========================================================================
    * Obtiene la instancia única de Database
    *
    * @param Logger|null $logger Instancia del logger
    * @return Database Instancia de la clase
    */
    public static function getInstance($logger = null) {
        if (self::$instance === null) {
            self::$instance = new self($logger);
        }
        return self::$instance;
    }

    /*
    *===========================================================================
    * Carga la configuración de la base de datos desde Config
    *
    * @return void
    */
    private function loadConfig() {
        $this->config = [
            'host'    => Config::get('DB_HOST', 'localhost'),
            'name'    => Config::get('DB_NAME', 'testapi'),
            'user'    => Config::get('DB_USER', 'root'),
            'pass'    => Config::get('DB_PASS', ''),
            'charset' => Config::get('DB_CHARSET', 'utf8mb4')
        ];
    }

    /*
    *===========================================================================
    * Obtiene la conexión PDO a la base de datos
    * Crea la conexión si no existe o si se perdió
    *
    * @param int $attempt Número de intento actual (para reconexión)
    * @return PDO Conexión PDO
    * @throws PDOException Si no se puede establecer la conexión
    */
    public function getConnection($attempt = 1) {
        try {
            // Verificar si la conexión existe y está activa
            if ($this->connection === null || !$this->isConnectionAlive()) {
                $this->connect();
            }

            return $this->connection;
        } catch (PDOException $e) {
            $this->logger->error('Error al conectar con la base de datos', [
                'error'   => $e->getMessage(),
                'attempt' => $attempt
            ]);

            // Reintentar si no se ha alcanzado el máximo
            if ($attempt < $this->maxRetries) {
                sleep(1); // Esperar 1 segundo antes de reintentar
                return $this->getConnection($attempt + 1);
            }

            throw new PDOException(
                'No se pudo establecer conexión con la base de datos después de ' . $this->maxRetries . ' intentos: ' . $e->getMessage()
            );
        }
    }

    /*
    *===========================================================================
    * Establece la conexión con la base de datos
    *
    * @return void
    * @throws PDOException Si falla la conexión
    */
    private function connect() {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $this->config['host'],
            $this->config['name'],
            $this->config['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE             => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES    => false,
            PDO::ATTR_PERSISTENT          => false,
            PDO::MYSQL_ATTR_INIT_COMMAND  => "SET NAMES " . $this->config['charset']
        ];

        $this->connection = new PDO(
            $dsn,
            $this->config['user'],
            $this->config['pass'],
            $options
        );

        $this->logger->info('Conexión a base de datos establecida correctamente');
    }

    /*
    *===========================================================================
    * Verifica si la conexión está activa
    *
    * @return bool True si la conexión está activa, false en caso contrario
    */
    private function isConnectionAlive() {
        try {
            if ($this->connection === null) {
                return false;
            }

            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /*
    *===========================================================================
    * Ejecuta una consulta preparada
    *
    * @param string  $sql     Consulta SQL
    * @param array   $params  Parámetros para la consulta
    * @return \PDOStatement Statement ejecutado
    * @throws PDOException Si falla la ejecución
    */
    public function query($sql, array $params = []) {
        try {
            $connection = $this->getConnection();
            $stmt       = $connection->prepare($sql);
            $stmt->execute($params);

            return $stmt;
        } catch (PDOException $e) {
            $this->logger->error('Error al ejecutar consulta', [
                'sql'   => $sql,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /*
    *===========================================================================
    * Inicia una transacción
    *
    * @return bool True si se inició correctamente
    */
    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }

    /*
    *===========================================================================
    * Confirma una transacción
    *
    * @return bool True si se confirmó correctamente
    */
    public function commit() {
        return $this->getConnection()->commit();
    }

    /*
    *===========================================================================
    * Revierte una transacción
    *
    * @return bool True si se revirtió correctamente
    */
    public function rollback() {
        return $this->getConnection()->rollBack();
    }

    /*
    *===========================================================================
    * Obtiene el ID del último registro insertado
    *
    * @return string ID del último insert
    */
    public function lastInsertId()  {
        return $this->getConnection()->lastInsertId();
    }

    /*
    *===========================================================================
    * Cierra la conexión a la base de datos
    *
    * @return void
    */
    public function closeConnection() {
        $this->connection = null;
        $this->logger->info('Conexión a base de datos cerrada');
    }
}
