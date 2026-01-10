<?php
/*
* Clase Sucursal
*
* Modelo que representa una sucursal
* Corresponde a la tabla sucursal_listado
*
* @package App\Models
*/

//Seteo del Namespace
namespace App\Models;

//Se crea la clase
class Sucursal {
    /*
    *===========================================================================
    * @vars
    */
    public $idSucursal; //@var int    ID de la sucursal
    public $Nombre;     //@var string Nombre de la sucursal

    /*
    *===========================================================================
    * Constructor
    *
    * @param array $data Datos de la sucursal
    */
    public function __construct($data = []) {
        $this->idSucursal = $data['idSucursal'] ?? null;
        $this->Nombre     = $data['Nombre'] ?? null;
    }

    /*
    *===========================================================================
    * Convierte el modelo a array
    *
    * @return array Datos del modelo
    */
    public function toArray() {
        return [
            'idSucursal' => $this->idSucursal,
            'Nombre'     => $this->Nombre
        ];
    }
}
