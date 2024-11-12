<?php

namespace App\Models\Facturacion\Sunat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoAfectacion extends Model
{
    use HasFactory;
    protected $table = 'sunat_tipo_afectacion';
    /*
    `codigo` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`descripcion` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`codigo_afectacion` VARCHAR(4) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`nombre_afectacion` VARCHAR(4) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`tipo_afectacion` VARCHAR(4) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`estado` VARCHAR(15) NOT NULL DEFAULT 'activo' COLLATE 'utf8mb4_unicode_ci', */
    protected $fillable = ['codigo', 'codigo_afectacion', 'nombre_afectacion', 'tipo_afectacion', 'estado'];
    public $timestamps = false;
}
