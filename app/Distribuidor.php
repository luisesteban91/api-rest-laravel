<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Distribuidor extends Model {

    protected $table = "distribuidores";

    protected $fillable = [
        'clave_interna','nombre', 'estado', 'codido_postal', 'lat','lng', 'estatus', 'domicilio','zona'
    ];
    
}