<?php
namespace App\Http\Controllers\Odoocpe;

use App\Http\Controllers\Controller;
use App\Models\OdooCpe\Odoo;


class OdooUbigeoController extends Controller
{
    public function mostrarDepartamentos(){
        $Odoo = New Odoo;
        $provincias = $Odoo->departamentos();
        //dd($provincias);
        return $provincias;
    }
    //mostrar ciudades
    public function mostrarProvincias($state_id){
        $Odoo = New Odoo;
        $ciudades = $Odoo->Provincias($state_id);
        return $ciudades;
    }
    //mostrar distritos
    public function mostrarDistritos($city_id){
        $Odoo = New Odoo;
        $distritos = $Odoo->distritos($city_id);
        return $distritos;
    }
    //mostrar ubigeo
    public function mostrarUbigeo($district_id){
        $Odoo = New Odoo;
        $ubigeo = $Odoo->ubigeo($district_id);
        return $ubigeo;
    }
    
}