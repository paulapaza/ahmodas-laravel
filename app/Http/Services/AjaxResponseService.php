<?php
namespace App\Http\Services;

use Illuminate\Http\JsonResponse;

class AjaxResponseService
{
    
    //update
    public function successUpdate($data, int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => "El registro, $data->nombre, se ha actualizado correctamente",
            'data' => $data,
        ];
        
        return response()->json($response, $status);
    }
    public function successStore($data, int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => "el registro, $data->nombre, ha guardado correctamente",
            'data' => $data,
        ];
        
        return response()->json($response, $status);
    }
    public function successDestroy($data, int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => "El registro, $data->nombre, se ha eliminado correctamente",
            'data' => $data,
        ];
        
        return response()->json($response, $status);
    }

    
    public function error(string $message, int $status = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}