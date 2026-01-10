<?php
/*
* Clase Config
*
* Gestiona la carga y acceso a las variables de configuración desde el archivo .env
* Implementa el patrón Singleton para garantizar una única instancia
*
* @package App\Core
*/

//Seteo del Namespace
namespace App\Core;

//Se crea la clase
class Config {
    /*
    *===========================================================================
    * @vars
    */
    private static $instance = null; //@var Config|null   Instancia única de la clase
    private $config          = [];   //@var array         Almacena las variables de configuración cargadas

    /*
    *===========================================================================
    * Constructor privado para implementar Singleton
    * Carga automáticamente las variables del archivo .env
    */
    private function __construct() {
        $this->loadEnv();
    }

    /*
    *===========================================================================
    * Obtiene la instancia única de Config
    *
    * @return Config Instancia de la clase
    */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /*
    *===========================================================================
    * Carga las variables de entorno desde el archivo .env
    *
    * @throws \Exception Si el archivo .env no existe
    * @return void
    */
    private function loadEnv() {
        $envFile = dirname(__DIR__, 2) . '/.env';

        if (!file_exists($envFile)) {
            throw new \Exception('Archivo .env no encontrado');
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parsear línea KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key    = trim($key);
                $value  = trim($value);

                // Remover comillas si existen
                $value = trim($value, '"\'');

                $this->config[$key] = $value;

                // También establecer en $_ENV y putenv para compatibilidad
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    /*
    *===========================================================================
    * Obtiene el valor de una variable de configuración
    *
    * @param string $key Nombre de la variable
    * @param mixed $default Valor por defecto si la variable no existe
    * @return mixed Valor de la configuración o valor por defecto
    */
    public static function get($key, $default = null) {
        $instance = self::getInstance();
        return $instance->config[$key] ?? $default;
    }

    /*
    *===========================================================================
    * Obtiene todas las variables de configuración
    *
    * @return array Todas las configuraciones cargadas
    */
    public static function all() {
        $instance = self::getInstance();
        return $instance->config;
    }

    /*
    *===========================================================================
    * Verifica si existe una variable de configuración
    *
    * @param string $key Nombre de la variable
    * @return bool True si existe, false en caso contrario
    */
    public static function has($key) {
        $instance = self::getInstance();
        return isset($instance->config[$key]);
    }

    /*
    *===========================================================================
    * Valida que las configuraciones requeridas estén presentes
    *
    * @param array $required Array de claves requeridas
    * @throws \Exception Si falta alguna configuración requerida
    * @return void
    */
    public static function validate(array $required) {
        $missing = [];

        foreach ($required as $key) {
            if (!self::has($key)) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new \Exception(
                'Configuraciones requeridas faltantes: ' . implode(', ', $missing)
            );
        }
    }
}
