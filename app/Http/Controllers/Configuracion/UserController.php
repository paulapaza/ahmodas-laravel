<?php

namespace App\Http\Controllers\Configuracion;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {

          $users = User::with('roles', 'tienda')->get();
        //dd($users);
        return response()->json($users, 200);
    }
    public function show($id)
    {
        $user = User::find($id);

        return view('configuracion.users.user', compact('user'));
    }
    public function store(UserRequest $request) 
    {
   
         try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->estado = $request->estado;
            $user->password = bcrypt("12345678");
            $user->print_type = $request->print_type;
            $user->restriccion_precio_minimo = $request->restriccion_precio_minimo;
            $user->tienda_id = (int)$request->tienda_id; // Asignamos la tienda al usuario
            $user->save();

            // asignamos el rol
            
            $user->assignRole($request->role);

        } catch (\Exception $e) {
            return ([
                'success' => false,
                'message' => "Ocurrio un error al crear el usuario <b>{$request->name}</b> <br> {$e->getMessage()}"
            ]);
        }

        return ([
            'success' => true,
            'message' => "El Usuario <b>{$request->name}</b> se creo correctamente"
        ]);
    }

    public function update(UserRequest $request, $id)
    {
        //dd($request->all());
        try {
            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->estado = $request->estado;
            $user->print_type = $request->print_type;
            $user->restriccion_precio_minimo = $request->restriccion_precio_minimo;
            $user->tienda_id = (int)$request->tienda_id; // Asignamos la tienda al usuario
            $user->save();
            
            DB::table('model_has_roles')->where('model_id',$id)->delete();
            $user->assignRole($request->role);
                      
            return response()->json(
                [
                    'success' => true,
                    'message' => "El Usuario <b>{$id} {$request->name}</b> se actualizo correctamente"
                ],
                200
            );

        } catch (\Exception $e) {
        
            return response()->json(
                [
                    'success' => false,
                    'message' => "Error al actualizar el usuario <b>{$id} {$request->name}</b>
                              <br>Mensaje de error: <br> {$e->getMessage()}"
                ],
                422
            );
        }
    }

    public function destroy($id)
    {
        //comprovar si el usuario tiene rol admin
        $user = User::find($id);
        if ($user->role == 'admin') {
            return response()->json(
                [
                    'success' => false,
                    'message' => "El Usuario <b>{$id} {$user->name}</b> no se puede eliminar"
                ],
                200
            );
        }
        
        try {

            $user = User::find($id);
            $user->delete();
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => "Error al eliminar el usuario <b>{$id} {$user->name}</b>
                              <br> {$e->getMessage()}"
                ],
                422
            );
        }
        return response()->json(
            [
                'success' => true,
                'message' => "El Usuario <b>{$id} {$user->name}</b> se eliminó permanentemente"
            ],
            200
        );
    }
}