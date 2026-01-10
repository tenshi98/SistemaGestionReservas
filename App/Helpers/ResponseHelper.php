<?php
/*
* Clase ResponseHelper
*
* Proporciona métodos para generar respuestas JSON estandarizadas
* Maneja códigos HTTP apropiados y formato consistente
*
* @package App\Helpers
*/

//Seteo del Namespace
namespace App\Helpers;

//Se crea la clase
class ResponseHelper {
    /*
    *===========================================================================
    * Envía una respuesta JSON exitosa
    *
    * @param mixed   $data        Datos a enviar
    * @param string  $message     Mensaje opcional
    * @param int     $statusCode  Código HTTP (por defecto 200)
    * @return void
    */
    public static function success($data = null, $message = 'Operación exitosa', $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /*
    *===========================================================================
    * Envía una respuesta JSON de error
    *
    * @param string  $message     Mensaje de error
    * @param int     $statusCode  Código HTTP (por defecto 400)
    * @param array   $errors      Errores detallados opcionales
    * @return void
    */
    public static function error($message = 'Error en la solicitud', $statusCode = 400, $errors = []) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        $response = [
            'success' => false,
            'error'   => $message
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /*
    *===========================================================================
    * Envía una respuesta de no autorizado (401)
    *
    * @param string $message Mensaje de error
    * @return void
    */
    public static function unauthorized($message = 'No autorizado') {
        self::error($message, 401);
    }

    /*
    *===========================================================================
    * Envía una respuesta de prohibido (403)
    *
    * @param string $message Mensaje de error
    * @return void
    */
    public static function forbidden($message = 'Acceso prohibido') {
        self::error($message, 403);
    }

    /*
    *===========================================================================
    * Envía una respuesta de no encontrado (404)
    *
    * @param string $message Mensaje de error
    * @return void
    */
    public static function notFound($message = 'Recurso no encontrado') {
        self::error($message, 404);
    }

    /*
    *===========================================================================
    * Envía una respuesta de error del servidor (500)
    *
    * @param string $message Mensaje de error
    * @return void
    */
    public static function serverError($message = 'Error interno del servidor') {
        self::error($message, 500);
    }

    /*
    *===========================================================================
    * Envía una respuesta de demasiadas solicitudes (429)
    *
    * @param string  $message     Mensaje de error
    * @param int     $retryAfter  Segundos para reintentar
    * @return void
    */
    public static function tooManyRequests($message = 'Demasiadas solicitudes', $retryAfter = 60) {
        http_response_code(429);
        header('Content-Type: application/json; charset=utf-8');
        header('Retry-After: ' . $retryAfter);

        $response = [
            'success'     => false,
            'error'       => $message,
            'retry_after' => $retryAfter
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /*
    *===========================================================================
    * Envía una respuesta JSON personalizada
    *
    * @param array  $data        Datos a enviar
    * @param int    $statusCode  Código HTTP
    * @return void
    */
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
