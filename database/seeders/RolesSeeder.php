<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * php artisan db:seed --class=RolesSeeder
     */
    public function run(): void
    {
        // crear el rol "SuperAdmin" y invitado
        $super = Role::create(['name' => 'Super']);
        $admin = Role::create(['name' => 'Administrador' ]);
        $role_cajero = Role::create(['name' => 'cajero' ]);
                      
         $user = User::find(1);
         $user->assignRole([$super]); 

         $user = User::find(2);
         $user->assignRole([$admin]);        
         
         $user = User::find(3);
         $user->assignRole($role_cajero);        

        
    }
}
