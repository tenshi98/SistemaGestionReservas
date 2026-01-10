<?php
/*
* Clase Token
*
* Modelo que representa un token autorizado en el sistema
* Corresponde a la tabla tokens_listado
*
* @package App\Models
*/

//Seteo del Namespace
namespace App\Models;

//Se crea la clase
class Token {
    /*
    *===========================================================================
    * @vars
    */
    public $idToken; //@var int    ID del token
    public $Token;   //@var string Token de autenticación
    public $Nombre;  //@var string Nombre descriptivo del token

    /*
    *===========================================================================
    * Constructor
    *
    * @param array $data Datos del token
    */
    public function __construct($data = []) {
        $this->idToken = $data['idToken'] ?? null;
        $this->Token   = $data['Token'] ?? null;
        $this->Nombre  = $data['Nombre'] ?? null;
    }

    /*
    *===========================================================================
    * Convierte el modelo a array
    *
    * @return array Datos del modelo
    */
    public function toArray() {
        return [
            'idToken' => $this->idToken,
            'Token'   => $this->Token,
            'Nombre'  => $this->Nombre
        ];
    }
}
