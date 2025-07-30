<?php

namespace App\Livewire\Profile;

use App\Models\Inventario\Tienda;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SetTiendaInProfile extends Component
{
    public $state = [];

    public function mount()
    {
        $user = Auth::user();
        $this->state = [
            'tienda_id' => $user->tienda_id,
            'print_type' => $user->print_type,
            'printer_ip' => $user->printer_ip,
            'printer_name' => $user->printer_name,
        ];
    }

    public function updateTienda()
    {
        $this->validate([
            'state.tienda_id' => 'required|exists:tiendas,id',
        ]);
        $tienda = (int) $this->state['tienda_id'];
      
        Auth::user()->update([
            'tienda_id' => $tienda,
            'print_type' => $this->state['print_type'],
            'printer_ip' => $this->state['printer_ip'],
            'printer_name' => $this->state['printer_name'],
        ]);

        $this->dispatch('saved');
    }


    public function render()
    {
         $tiendas = Tienda::all(); // O puedes aplicar un filtro si es necesario

        return view('profile.update-tienda-settings', [
            'tiendas' => $tiendas,
        ]);
    }
}
