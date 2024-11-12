<?php

namespace App\Http\Controllers\Odoocpe;

use App\Models\OdooCpe\OdooDb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;


class OdooDbController extends Controller
{
    
    public function index(){
        $databases = OdooDb::all();
        foreach ($databases as $db){
            $db->password = "**********";
        }
        return json_encode($databases);
    }
    
    public function store(Request $request){

        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:4', // Asegurar que la contraseña tenga al menos 8 caracteres
            'estado' => 'required|in:0,1', // Solo permite 0 o 1
        ]);
        
        $OdooDb = new OdooDb();
        $OdooDb->name = $request->name;
        $OdooDb->url = $request->url;
        $OdooDb->username = $request->username;
        $OdooDb->password = Crypt::encryptString($request->password);
        $OdooDb->estado = $request->estado;
        $OdooDb->save();

        if($OdooDb->estado==1){
            OdooDb::where('id', '!=', $OdooDb->id)->update(['estado' => 0]); 
        }

        return response()->json([
            "success" => true,
            "message" => "registro creado",
           
        ]);
    }
    public function update(Request $request, $id){

        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:4', // Asegurar que la contraseña tenga al menos 8 caracteres
            'estado' => 'required|in:0,1', // Solo permite 0 o 1
        ]);

         // Encontrar el registro a actualizar
         $OdooDb = OdooDb::find($id);
         if (!$OdooDb) {
             return response()->json([
                 "success" => false,
                 "message" => "Registro no encontrado",
             ], 404);
         }
        
        $OdooDb = OdooDb::find($id);
        $OdooDb->name = $request->name;
        $OdooDb->url = $request->url;
        $OdooDb->username = $request->username;
        $OdooDb->password = Crypt::encryptString($request->password);
        $OdooDb->estado = $request->estado;
        $OdooDb->save();

        if($OdooDb->estado==1){
            OdooDb::where('id', '!=', $id)->update(['estado' => 0]); 
        }

        return response()->json([
            "success" => true,
            "message" => "registro actulizado",
           
        ]);
    }
    public function destroy(Request $request, $id){

        
         // Encontrar el registro a actualizar
         $OdooDb = OdooDb::find($id);
         if (!$OdooDb) {
             return response()->json([
                 "success" => false,
                 "message" => "Registro no encontrado",
             ], 404);
         }
        
        $OdooDb = OdooDb::find($id);
        
        $OdooDb->delete();

        
        return response()->json([
            "success" => true,
            "message" => "registro eliminado",
           
        ]);
    }


    
    
  
}
