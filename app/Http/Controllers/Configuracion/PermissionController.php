<?php

namespace App\Http\Controllers\Configuracion;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    function __construct()
    {
        
        $this->middleware('role:Super');
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $permissions = Permission::all();
        echo json_encode($permissions);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Permission $permissions)
    {
        $permissions->create([
            'name' => request('name'),
            'descripcion'=> request('descripcion'),
            'guard_name' => 'web'
           
        ]);

        return response()->json([
            'success' => true,
            'message' => 'El el permiso se creo correctamente'
        ]);  
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->validate($request, [
            'descripcion' => 'required|regex:/^[\pL\s\-]+$/u',
            //'permission' => 'required',
        ]);

        $permissions = Permission::find($id);
        $permissions->descripcion = $request->input('descripcion');
        $permissions->save();

        //$role->syncPermissions($request->input('permission'));

        return response()->json([
            "success" => true,
            "message" => "Permiso Actualizado"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::table("permissions")->where('id', $id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'El permiso se Elimino correctamente'
        ]);
    }
}
