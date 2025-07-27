<?php

namespace App\Console\Commands;

use BaconQrCode\Common\Mode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CrearPermisos extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan app:crear-permisos
     * 
     * @var string
     */
    protected $signature = 'app:crear-permisos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea roles y permisos por defecto para la aplicacion';

    /**
     * Execute the console command.
     */
    public function handle()
    {
     
     
        $super = Role::where('name', 'Super')->first();
        $admin = Role::where('name', 'Administrador')->first();
        $role_cajero = Role::where('name', 'cajero')->first();


        // no te olvides de limpiar la cache 
        // php artisan permission:cache-reset
        $permissions = [
            [
                'name' => 'ver-POS',
                'descripcion' => 'ver menu pos',
                'categoria' => 'menu lateral',
                'roles' => [$super, $admin, $role_cajero]
            ],
                [
                    'name' => 'ver-moneda',
                    'descripcion' => 'ver select moneda en el pos',
                    'categoria' => 'sub menu superior',
                    'roles' => [$super, $admin]
                ],
                [
                    'name' => 'ver-tipo-de-venta',
                    'descripcion' => 'ver select tipo de venta en el pos .local o exportacion',
                    'categoria' => 'sub menu superior',
                    'roles' => [$super, $admin]
                ],
            [
                'name' => 'ver-Ventas',
                'descripcion' => 'ver menu ventas',
                'categoria' => 'menu lateral',
                'roles' => [$super, $admin, $role_cajero]
            ],
                    [
                        'name' => 'ver-lista-ventas',
                        'descripcion' => 'ver ventas->ventas',
                        'categoria' => 'sub menu superior',
                        'roles' => [$super, $admin]
                    ],
                    [
                        'name' => 'ver-visor-ventas',
                        'descripcion' => 'ver  ventas->visor ventas',
                        'categoria' => 'sub menu superior',
                        'roles' => [$super, $admin, $role_cajero]
                    ],
                    [
                        'name' => 'ver-visor-ventas',
                        'descripcion' => 'ver submenu ventas->visor ventas',
                        'categoria' => 'sub menu',
                        'roles' => [$super, $admin, $role_cajero]
                    ],
                    [
                        'name' => 'ver-visor-ventas-por-tienda',
                        'descripcion' => 'ver submenu ventas->visor ventas por tienda',
                        'categoria' => 'sub menu',
                        'roles' => [$super, $admin, $role_cajero]
                    ],

                       
                [
                    'name' => 'ver-inventario',
                    'descripcion' => 'ver menu lateral inventario  ',
                    'categoria' => 'menu lateral',
                    'roles' => [$super, $admin]
                ],
                [
                    'name' => 'ver-facturacion',
                    'descripcion' => 'ver menu lateral facturacion  ',
                    'categoria' => 'menu lateral',
                    'roles' => [$super, $admin]
                ],
               
            [
                'name' => 'ver-configuracion',
                'descripcion' => 'ver menu configuracion',
                'categoria' => 'menu lateral',
                'roles' => [$super, $admin]
            ],
                [
                    'name' => 'ver-configuracion-general',
                    'descripcion' => 'ver submenu configuracion -> configuracion general',
                    'categoria' => 'sub menu superior',
                    'roles' => [$super, $admin]
                ],
                [
                    'name' => 'ver-usuarios',
                    'descripcion' => 'ver submenu configuracion -> usuarios',
                    'categoria' => 'sub menu superior',
                    'roles' => [$super, $admin]
                ],
                    [
                        'name' => 'ver-submenu-usuarios',
                        'descripcion' => 'ver submenu configuracion -> usuarios -> usuarios',
                        'categoria' => 'sub menu superior item',
                        'roles' => [$super, $admin]
                    ],
                    [
                        'name' => 'ver-submenu-roles',
                        'descripcion' => 'ver submenu configuracion -> usuarios ->roles',
                        'categoria' => 'sub menu superior item',
                        'roles' => [$super, $admin]
                    ],
                    [
                        'name' => 'ver-submenu-permisos',
                        'descripcion' => 'ver submenu configuracion -> usuarios ->permisos',
                        'categoria' => 'sub menu superior item',
                        'roles' => [$super, $admin]
                    ],
           
            [
                'name' => 'anular-venta',
                'descripcion' => 'ver anular venta',
                'categoria' => 'modulo ventas',
                'roles' => [$super, $admin]
            ],
          
            
           

        ];
    
        foreach ($permissions as $permission) {
            
            if (Permission::where('name', $permission['name'])->count() == 0) {
                
                Permission::where('name', $permission['name'])->delete();
                
                Permission::create([
                    'name' => $permission['name'],
                    'descripcion' => $permission['descripcion'],
                    'categoria' => $permission['categoria']
                ])->assignRole($permission['roles']); 
    
                $this->info("Permiso {$permission['name']} creado");

            } else {
                $this->info("Permiso {$permission['name']} ya existe");
            }
                
           
        }
      
    }
}
                
            