<?php 
namespace Database\Factories\Inventario;

use App\Models\Inventario\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

   public function definition(): array
{
    $costo = $this->faker->randomFloat(2, 15, 100);
    $precio = round(($costo * 1.3) * 2) / 2;
    $precioMinimo = round(($precio - 10) * 2) / 2;

    $prendas = [
        'Pantalón de dama', 'Pantalón de hombre', 'Camisa formal',
        'Polera deportiva', 'Vestido de verano', 'Falda casual',
        'Short de jean', 'Chompa tejida', 'Blusa manga larga',
        'Casaca impermeable', 'Buzo unisex', 'Top deportivo',
        'Polera oversize', 'Jean skinny', 'Leggings térmicos',
        'Terno elegante', 'Camiseta básica', 'Polo estampado',
        'Chaleco acolchado', 'Zapatilla urbana'
    ];

    return [
        'codigo_barras' => str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
        'nombre' => $this->faker->randomElement($prendas),
        'costo_unitario' => round($costo, 2),
        'precio_unitario' => number_format($precio, 2, '.', ''),
        'precio_minimo' => number_format($precioMinimo, 2, '.', ''),
        'categoria_id' => 1,
        'marca_id' => 1,
        'estado' => 1,
    ];
}

}
