<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;



class RoleController extends Controller
{
    
    function __construct()
    {
        
        $this->middleware('role:Administrador|Super');
    }
    
    
    public function index()
    {
        //$roles = Role::all();
        // traer en la pariable permisos , los permiso asigando a este rol
        $roles_permisos = Role::with('permissions')->get();
        echo json_encode($roles_permisos);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permission = Permission::get();
        return view('roles.create', compact('permission'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Role $role)
    {
        $this->validate(request(), [
            'name' => 'required|unique:roles,name|alpha|max:20',
           
        ]);
        $role->create([
            'name' => request('name'),
            'guard_name' => 'web'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'El rol se creo correctamente'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::find($id);
        $permissions = Permission::All();
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();
        return view('modules.configuracion.users.rol', compact('role','permissions', 'rolePermissions'));
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
            'name' => 'required',
        ]);
        
        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->save();
        $role->syncPermissions($request->input('permission'));

        return response()->json([
            "success" => true,
            "message" => "Rol Actualizado"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::table("roles")->where('id', $id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'El rol se Elimino correctamente'
        ]);
    }
}
