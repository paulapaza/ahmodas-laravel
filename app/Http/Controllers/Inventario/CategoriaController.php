<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriaRequest;
use App\Models\Inventario\Categoria;
use Illuminate\Http\Request;
use App\Services\AjaxResponseService;

class CategoriaController extends Controller
{
    //index
    public function index()
    {
        

        $categorias = Categoria::with('categoriaPadre')->get();
        // añadir un campo adicional a la respuesta (nombre_visible) que es la concatenacion de los nombres de las categorias padres
        $categorias->map(function ($categoria) {
            $nombre_visible = $categoria->nombre;
            $categoria_padre = $categoria->categoriaPadre;
            while ($categoria_padre != null) {
                $nombre_visible = $categoria_padre->nombre . ' / ' . $nombre_visible;
                $categoria_padre = $categoria_padre->categoriaPadre;
            }
            $categoria->nombre_visible = $nombre_visible;
            return $categoria;
        });

        return response()->json($categorias, 200);
    }
    //store
    public function store(CategoriaRequest $request)
    {

    
        $categoria = Categoria::create($request->all());
        //guardar el parent path en la tabla en la columna nombre_visible
        $nombre_visible = $categoria->nombre;
        $categoria_padre = $categoria->categoriaPadre;
        
        while ($categoria_padre != null) {
            $nombre_visible = $categoria_padre->nombre . ' / ' . $nombre_visible;
            $categoria_padre = $categoria_padre->categoriaPadre;
        }
        $categoria->nombre_visible = $nombre_visible;
        $categoria->save();
        return (new AjaxResponseService)->successStore($categoria, 201);

    }
    //destroy
    public function destroy($id)
    {

        $categoria = Categoria::find($id);
        $categoria->delete();
        return (new AjaxResponseService)->successDestroy($categoria, 200);
    }
    //update
    public function update(CategoriaRequest $request, $id)
    {

        // comprobar que la categoria no sea una Se detectó una recurrencia
        if ($request->categoria_padre_id == $id) {
            return (new AjaxResponseService)->error('No se puede asignar una categoria como su propia categoria padre', 400);
        }
        // comprorbar que la categoria elegida como padre no sea una categoria hija de la categoria a actualizar o descendiente de la categoria a actualizar
        if ($request->categoria_padre_id != null) {

            $categoria = Categoria::find($id); // registro a actualizar
            $categoria_padre = Categoria::find($request->categoria_padre_id); //
            $parent_path = $categoria_padre->nombre_visible;
            if(strpos($parent_path, $categoria->nombre) !== false) {
                return (new AjaxResponseService)->error('No se puede asignar una categoria descendiente como categoria padre', 200);
            }
        }
        $categoria = Categoria::find($id);
        $categoria->update($request->all());
        //guardar el parent path en la tabla en la columna nombre_visible y de lo registros hijos
        
        $nombre_visible = $categoria->nombre;
        $categoria_padre = $categoria->categoriaPadre;
        while ($categoria_padre != null) {
            $nombre_visible = $categoria_padre->nombre . ' / ' . $nombre_visible;
            $categoria_padre = $categoria_padre->categoriaPadre;
        }
        $categoria->nombre_visible = $nombre_visible;
        $categoria->save();

        //actualizar el parent path de los registros hijos
        $hijos = Categoria::where('categoria_padre_id', $categoria->id)->get();
        
        foreach ($hijos as $hijo) {
            $nombre_visible = $categoria->nombre_visible. ' / ' . $hijo->nombre;
            $hijo->nombre_visible = $nombre_visible;
            $hijo->save();
        }

        
        return (new AjaxResponseService)->successUpdate($categoria, 200);
    }
}
