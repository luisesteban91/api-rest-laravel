<?php 
namespace App;
use Illuminate\Support\Facades\DB;

class DBFunctions{
    public static function getDistribuidores($lat, $long){
        try {
            $data = null;
            $sql = "SELECT `id_distribuidor` AS id, `clave_interna` AS id_nissan, `nombre` AS name, `domicilio` AS address, `lat` AS lat, `lng` as lng,  111.045 * DEGREES(ACOS(COS(RADIANS($lat)) * COS(RADIANS(lat)) * COS(RADIANS(lng) - RADIANS($long)) + SIN(RADIANS($lat)) * SIN(RADIANS(lat)))) AS distance_in_km FROM `distribuidores` WHERE estatus = 1 HAVING distance_in_km <= 500 ORDER BY distance_in_km ASC LIMIT 0,6;";
            $query = DB::select($sql);
            $data = $query;
        } catch (\Throwable $e) {
            throw $e;
        }
        return $data;
    }
}
?>