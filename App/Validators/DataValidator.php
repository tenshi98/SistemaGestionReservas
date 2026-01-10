<?php
/*
* Clase DataValidator
*
* Proporciona métodos para validación y sanitización de datos
* Incluye reglas comunes y personalizadas
*
* @package App\Validators
*/

//Seteo del Namespace
namespace App\Validators;

//Se crea la clase
class DataValidator {
    /*
    *===========================================================================
    * @vars
    */
    private $errors = []; //@var array Errores de validación

    /*
    *===========================================================================
    * Valida que un campo sea requerido
    *
    * @param mixed   $value      Valor a validar
    * @param string  $fieldName  Nombre del campo
    * @return bool True si es válido
    */
    public function required($value, $fieldName) {
        if (empty($value) && $value !== '0' && $value !== 0) {
            $this->errors[$fieldName] = "El campo {$fieldName} es requerido";
            return false;
        }
        return true;
    }

    /*
    *===========================================================================
    * Valida formato de email
    *
    * @param string  $value      Valor a validar
    * @param string  $fieldName  Nombre del campo
    * @return bool True si es válido
    */
    public function email($value, $fieldName) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$fieldName] = "El campo {$fieldName} debe ser un email válido";
            return false;
        }
        return true;
    }

    /*
    *===========================================================================
    * Valida formato de fecha (Y-m-d)
    *
    * @param string  $value      Valor a validar
    * @param string  $fieldName  Nombre del campo
    * @return bool True si es válido
    */
    public function date($value, $fieldName) {
        $d = \DateTime::createFromFormat('Y-m-d', $value);
        if (!$d || $d->format('Y-m-d') !== $value) {
            $this->errors[$fieldName] = "El campo {$fieldName} debe ser una fecha válida (Y-m-d)";
            return false;
        }
        return true;
    }

    /*
    *===========================================================================
    * Valida longitud mínima
    *
    * @param string  $value      Valor a validar
    * @param int     $min        Longitud mínima
    * @param string  $fieldName  Nombre del campo
    * @return bool True si es válido
    */
    public function minLength($value, $min, $fieldName) {
        if (strlen($value) < $min) {
            $this->errors[$fieldName] = "El campo {$fieldName} debe tener al menos {$min} caracteres";
            return false;
        }
        return true;
    }

    /*
    *===========================================================================
    * Valida longitud máxima
    *
    * @param string  $value      Valor a validar
    * @param int     $max        Longitud máxima
    * @param string  $fieldName  Nombre del campo
    * @return bool True si es válido
    */
    public function maxLength($value, $max, $fieldName) {
        if (strlen($value) > $max) {
            $this->errors[$fieldName] = "El campo {$fieldName} no debe exceder {$max} caracteres";
            return false;
        }
        return true;
    }

    /*
    *===========================================================================
    * Valida que sea un número entero
    *
    * @param mixed  $value      Valor a validar
    * @param string $fieldName  Nombre del campo
    * @return bool True si es válido
    */
    public function integer($value, $fieldName) {
        if (!filter_var($value, FILTER_VALIDATE_INT) && $value !== 0) {
            $this->errors[$fieldName] = "El campo {$fieldName} debe ser un número entero";
            return false;
        }
        return true;
    }

    /*
    *===========================================================================
    * Sanitiza una cadena de texto
    *
    * @param string $value Valor a sanitizar
    * @return string Valor sanitizado
    */
    public function sanitizeString($value) {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /*
    *===========================================================================
    * Sanitiza un email
    *
    * @param string $value Valor a sanitizar
    * @return string Email sanitizado
    */
    public function sanitizeEmail($value) {
        return filter_var(trim($value), FILTER_SANITIZE_EMAIL);
    }

    /*
    *===========================================================================
    * Sanitiza un número entero
    *
    * @param mixed $value Valor a sanitizar
    * @return int Número sanitizado
    */
    public function sanitizeInt($value) {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /*
    *===========================================================================
    * Valida múltiples campos con reglas
    *
    * @param array $data  Datos a validar
    * @param array $rules Reglas de validación
    * @return bool True si todos son válidos
    *
    * Ejemplo de uso:
    * $rules = [
    *     'email'  => ['required', 'email'],
    *     'nombre' => ['required', 'minLength:3']
    * ];
    */
    public function validate($data, $rules) {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                // Parsear regla y parámetros
                $parts    = explode(':', $rule);
                $ruleName = $parts[0];
                $params   = isset($parts[1]) ? explode(',', $parts[1]) : [];

                // Ejecutar validación
                switch ($ruleName) {
                    case 'required':                                              $this->required($value, $field); break;
                    case 'email':     if (!empty($value)) {                       $this->email($value, $field); } break;
                    case 'date':      if (!empty($value)) {                       $this->date($value, $field); } break;
                    case 'minLength': if (!empty($value) && isset($params[0])) {  $this->minLength($value, (int)$params[0], $field); } break;
                    case 'maxLength': if (!empty($value) && isset($params[0])) {  $this->maxLength($value, (int)$params[0], $field); } break;
                    case 'integer':   if (!empty($value)) {                       $this->integer($value, $field); } break;
                }
            }
        }

        return empty($this->errors);
    }

    /*
    *===========================================================================
    * Obtiene los errores de validación
    *
    * @return array Errores
    */
    public function getErrors() {
        return $this->errors;
    }

    /*
    *===========================================================================
    * Limpia los errores
    *
    * @return void
    */
    public function clearErrors() {
        $this->errors = [];
    }
}
