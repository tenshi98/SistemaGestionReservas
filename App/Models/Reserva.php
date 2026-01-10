<?php
/*
* Clase Reserva
*
* Modelo que representa una reserva
* Corresponde a la tabla reservas_listado
*
* @package App\Models
*/

//Seteo del Namespace
namespace App\Models;

//Se crea la clase
class Reserva {
    /*
    *===========================================================================
    * @vars
    */
    public $idReservas;      //@var int     ID de la reserva
    public $idToken;         //@var int     ID del token asociado
    public $Nombre;          //@var string  Nombre
    public $ApellidoPat;     //@var string  Apellido paterno
    public $ApellidoMat;     //@var string  Apellido materno
    public $NombreCompleto;  //@var string  Nombre completo
    public $Email;           //@var string  Email
    public $idSucursal;      //@var int     ID de la sucursal
    public $Etapa;           //@var string  Etapa de la reserva
    public $Fecha;           //@var string  Fecha de la reserva
    public $FechaAcceso;     //@var string  Fecha y hora de acceso
    public $idSendMail;      //@var int     ID de envío de email

    /*
    *===========================================================================
    * Constructor
    *
    * @param array $data Datos de la reserva
    */
    public function __construct($data = []) {
        $this->idReservas      = $data['idReservas'] ?? null;
        $this->idToken         = $data['idToken'] ?? null;
        $this->Nombre          = $data['Nombre'] ?? null;
        $this->ApellidoPat     = $data['ApellidoPat'] ?? null;
        $this->ApellidoMat     = $data['ApellidoMat'] ?? null;
        $this->NombreCompleto  = $data['NombreCompleto'] ?? null;
        $this->Email           = $data['Email'] ?? null;
        $this->idSucursal      = $data['idSucursal'] ?? null;
        $this->Etapa           = $data['Etapa'] ?? null;
        $this->Fecha           = $data['Fecha'] ?? null;
        $this->FechaAcceso     = $data['FechaAcceso'] ?? null;
        $this->idSendMail      = $data['idSendMail'] ?? 0;
    }

    /*
    *===========================================================================
    * Convierte el modelo a array
    *
    * @return array Datos del modelo
    */
    public function toArray() {
        return [
            'idReservas'      => $this->idReservas,
            'idToken'         => $this->idToken,
            'Nombre'          => $this->Nombre,
            'ApellidoPat'     => $this->ApellidoPat,
            'ApellidoMat'     => $this->ApellidoMat,
            'NombreCompleto'  => $this->NombreCompleto,
            'Email'           => $this->Email,
            'idSucursal'      => $this->idSucursal,
            'Etapa'           => $this->Etapa,
            'Fecha'           => $this->Fecha,
            'FechaAcceso'     => $this->FechaAcceso,
            'idSendMail'      => $this->idSendMail
        ];
    }
}
