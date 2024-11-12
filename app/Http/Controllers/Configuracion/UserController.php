<?php

namespace App\Http\Controllers\Configuracion;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        
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
            $user->username = $request->username;
            $user->email = $request->email;
            $user->estado = $request->estado;
            $user->password = bcrypt('12345678');
            $user->save();
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

        try {
            $user = User::find($id);
            $user->name = $request->name;
            $user->username = $request->username;
            $user->email = $request->email;
            $user->estado = $request->estado;
            $user->save();
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'name' => "Error al actualizar el usuario <b>{$id} {$request->name}</b>
                              <br> {$e->getMessage()}"
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => "Error al actualizar el usuario <b>{$id} {$request->name}</b>
                              <br> {$e->getMessage()}"
                ],
                422
            );
        }

        return response()->json(
            [
                'success' => true,
                'message' => "El Usuario <b>{$id} {$request->name}</b> se actualizo correctamente"
            ],
            200
        );
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