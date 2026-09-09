<?php

namespace App\Livewire\Administration\Menu;

use App\Models\Menu;
use Livewire\Component;
use Livewire\Attributes\On;

class Navlist extends Component
{
    #[On('menu-update','submenu-update','xsubmenu-update')]

    public function render()
    {
        return view('livewire.administration.menu.navlist',[
            'Menus'=>Menu::with(['subMenus','subMenus.ExtraSubMenu'])->status()->urutan()->get()
        ]);
    }
}
