<?php
/*
* Clase Logger
*
* Sistema de registro de eventos con soporte para múltiples niveles (info, warning, error)
* Implementa rotación de logs por fecha y formato estructurado
*
* @package App\Core
*/

//Seteo del Namespace
namespace App\Core;

//Se crea la clase
class Logger {
    /*
    *===========================================================================
    * @vars
    */
    const LEVEL_INFO    = 'info';     //@var string  Nivel de log: info
    const LEVEL_WARNING = 'warning';  //@var string  Nivel de log: warning
    const LEVEL_ERROR   = 'error';    //@var string  Nivel de log: error
    private $logPath;                 //@var string  Directorio donde se almacenan los logs
    private $minLevel;                //@var string  Nivel mínimo de log a registrar

    /*
    *===========================================================================
    * @var array Mapeo de niveles a valores numéricos para comparación
    */
    private $levelPriority = [
        self::LEVEL_INFO    => 1,
        self::LEVEL_WARNING => 2,
        self::LEVEL_ERROR   => 3
    ];

    /*
    *===========================================================================
    * Constructor
    *
    * @param string|null  $logPath   Ruta del directorio de logs
    * @param string       $minLevel  Nivel mínimo de log a registrar
    */
    public function __construct($logPath = null, $minLevel = self::LEVEL_INFO) {
        $this->logPath  = $logPath ?? Config::get('LOG_PATH', 'logs/');
        $this->minLevel = $minLevel;

        // Crear directorio de logs si no existe
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    /*
    *===========================================================================
    * Registra un mensaje de información
    *
    * @param string $message  Mensaje a registrar
    * @param array  $context  Contexto adicional
    * @return void
    */
    public function info($message, array $context = []) {
        $this->log(self::LEVEL_INFO, $message, $context);
    }

    /*
    *===========================================================================
    * Registra un mensaje de advertencia
    *
    * @param string $message  Mensaje a registrar
    * @param array  $context  Contexto adicional
    * @return void
    */
    public function warning($message, array $context = []) {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }

    /*
    *===========================================================================
    * Registra un mensaje de error
    *
    * @param string $message  Mensaje a registrar
    * @param array  $context  Contexto adicional
    * @return void
    */
    public function error($message, array $context = [])  {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }

    /*
    *===========================================================================
    * Registra un mensaje con el nivel especificado
    *
    * @param string $level    Nivel del log
    * @param string $message  Mensaje a registrar
    * @param array  $context  Contexto adicional
    * @return void
    */
    private function log($level, $message, array $context = []) {
        // Verificar si el nivel es suficiente para registrar
        if ($this->levelPriority[$level] < $this->levelPriority[$this->minLevel]) {
            return;
        }

        // Generar nombre de archivo con fecha
        $filename = $this->logPath . date('Y-m-d') . '.log';

        // Formatear mensaje
        $timestamp  = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logEntry   = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        // Escribir en archivo
        file_put_contents($filename, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /*
    *===========================================================================
    * Registra un mensaje en un archivo de log específico
    * Útil para logs separados por funcionalidad
    *
    * @param string $filename  Nombre del archivo (sin extensión)
    * @param string $level     Nivel del log
    * @param string $message   Mensaje a registrar
    * @param array  $context   Contexto adicional
    * @return void
    */
    public function logToFile($filename, $level, $message, array $context = []) {
        $filepath   = $this->logPath . $filename . '.log';
        $timestamp  = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logEntry   = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        file_put_contents($filepath, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
