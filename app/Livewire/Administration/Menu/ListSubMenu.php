<?php

namespace App\Livewire\Administration\Menu;

use Flux\Flux;
use App\Models\Menu;
use App\Models\SubMenu;
use Livewire\Component;
use Livewire\Attributes\Validate;

class ListSubMenu extends Component
{
    public $legend;
    #[Validate('required', message: 'kolom wajib di isi!!!')]
    public $menu, $icon, $status, $menu_id, $urutan;
    public $search_company, $submenu_id, $route, $request_route;
    public function resetFilds()
    {
        $this->reset('submenu_id', 'menu', 'icon', 'route', 'menu_id', 'urutan');
    }
    public function open_modal(SubMenu $id)
    {
        Flux::modal('sub-menu')->show();
        if ($id->id) {
            $this->submenu_id = $id->id;
            $this->menu_id = $id->menu_id;
            $this->menu = $id->menu;
            $this->route = $id->route;
            $this->request_route = $id->request_route;
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
        $this->resetFilds();
        Flux::modal('sub-menu')->close();
    }
    public function store()
    {
        $this->validate();
        SubMenu::updateOrCreate(['id' => $this->submenu_id], [
            'menu_id' => $this->menu_id,
            'menu' => $this->menu,
            'route' => $this->route,
            'request_route' => $this->request_route,
            'icon' => $this->icon,
            'status' => $this->status,
            'urutan' => $this->urutan
        ]);
        if ($this->menu_id) {
            $text = 'Data berhasil di edit!!!';
        } else {
            $text = 'Data berhasil di input!!!';
        }
        $this->dispatch('submenu-update');
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
        $deleteFile = SubMenu::whereId($id);
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
        return view('livewire.administration.menu.list-sub-menu', [
            'SubMenu' => SubMenu::urutan()->paginate(20),
            'Menus' => Menu::get(),
        ]);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
