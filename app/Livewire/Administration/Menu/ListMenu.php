<?php

namespace App\Livewire\Administration\Menu;

use Flux\Flux;
use App\Models\Menu;
use Livewire\Component;
use Livewire\Attributes\Validate;

class ListMenu extends Component
{
    public $modalOpen, $legend, $title = 'Company';
    #[Validate('required', message: 'kolom wajib di isi!!!')]
    public $menu, $icon, $request_route, $status, $urutan;
    public $search_company, $menu_id;
    #[Validate('nullable')]
    public $route;
    public function resetFilds()
    {
        $this->reset('menu_id', 'urutan', 'menu', 'icon', 'route');
    }
    public function open_modal(Menu $id)
    {
        Flux::modal('menu')->show();
        if ($id->id) {
            $this->menu_id = $id->id;
            $this->menu = $id->menu;
            $this->request_route = $id->request_route;
            $this->route = $id->route;
            $this->icon = $id->icon;
            $this->status = $id->status;
            $this->urutan = $id->urutan;
            $this->legend = 'Edit Menu';
        } else {
            $this->legend = 'Input Menu';
             $this->resetFilds();
        }
    }
    public function close_modal()
    {
        Flux::modal('menu')->close();
        $this->resetFilds();
    }
    public function store()
    {
        $this->validate();
        Menu::updateOrCreate(['id' => $this->menu_id], [
            'menu' => $this->menu,
            'request_route' => $this->request_route,
            'route' => $this->route,
            'icon' => $this->icon,
            'status' => $this->status,
            'urutan' => $this->urutan
        ]);
        if ($this->menu_id) {
            $text = 'Data berhasil di edit!!!';
        } else {
            $text = 'Data berhasil di input!!!';
        }
        $this->dispatch('menu-update');
        $this->dispatch(
            'alert',
            [
                'text' => $text,
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
            ]
        );
    }
    public function delete($id)
    {
        $deleteFile = Menu::whereId($id);
        $deleteFile->delete();
        $this->dispatch(
            'alert',
            [
                'text' => "Data berhasil di hapus!!!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #ff3333, #ff6666)",
            ]
        );
    }
    public function render()
    {
        return view('livewire.administration.menu.list-menu', [
            'Menus' => Menu::urutan()->paginate(20)
        ]);
    }
     public function paginationView()
    {
        return 'vendor.livewire.tailwind';
    }
}
