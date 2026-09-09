<?php
namespace App\Livewire\Administration\Menu;

use App\Models\ExtraSubMenu as XsubMenu;
use App\Models\SubMenu;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ExtraSubMenu extends Component
{
    public $legend;
    #[Validate('required', message: 'kolom wajib di isi!!!')]
    public $menu, $icon, $status, $submenu_id, $route, $urutan,$request_route;
    public $search_company, $extra_submenu_id;
    public function resetFilds()
    {
        $this->reset('extra_submenu_id','submenu_id', 'menu', 'icon', 'route',  'urutan','request_route');
    }
    public function open_modal()
    {
        Flux::modal('extra-sub-menu')->show();
        $this->legend = 'Input Extra Sub Menu';
    }
    public function open_modal_edit(XsubMenu $id)
    {
        $this->extra_submenu_id       = $id->id;
        if ($id->id) {
            $this->submenu_id = $id->sub_menu_id;
            $this->menu             = $id->menu;
            $this->route            = $id->route;
            $this->request_route            = $id->request_route;
            $this->icon             = $id->icon;
            $this->status           = $id->status;
            $this->urutan           = $id->urutan;
        }
        Flux::modal('extra-sub-menu-edit')->show();
        $this->legend = 'Edit Extra Sub Menu';
    }
    public function close_modal()
    {
        if ($this->extra_submenu_id) {
            Flux::modal('extra-sub-menu-edit')->close();
        } else {
            Flux::modal('extra-sub-menu')->close();
        }
        $this->resetFilds();
    }
    public function store()
    {
        $this->validate();
        XsubMenu::updateOrCreate(['id' => $this->extra_submenu_id], [
            'sub_menu_id' => $this->submenu_id,
            'menu'    => $this->menu,
            'route'   => $this->route,
            'request_route'   => $this->request_route,
            'icon'    => $this->icon,
            'status'  => $this->status,
            'urutan'  => $this->urutan,
        ]);
        if ($this->extra_submenu_id) {
            $text = 'Data berhasil di edit!!!';
        } else {
            $text = 'Data berhasil di input!!!';
        }
        $this->dispatch('xsubmenu-update');
        $this->dispatch(
            'alert',
            [
                'text'            => $text,
                'duration'        => 5000,
                'destination'     => '/contact',
                'newWindow'       => true,
                'close'           => true,
                'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
            ]
        );
    }
    public function delete($id)
    {
        $deleteFile = XsubMenu::whereId($id);
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
        return view('livewire.administration.menu.extra-sub-menu',[
            'ExtraSubMenu' => XsubMenu::with('SubMenu')->urutan()->paginate(20),
            'SubMenu' => SubMenu::get(),
        ]);
    }
     public function paginationView()
    {
        return 'vendor.livewire.tailwind';
    }
}
