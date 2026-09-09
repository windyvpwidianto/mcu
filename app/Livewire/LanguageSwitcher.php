<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
class LanguageSwitcher extends Component
{
    public $lang;

    public function mount()
    {
       $this->lang = session('locale', app()->getLocale());
    }

    public function updatedLang($value)
    {
        session(['locale' => $value]);
    app()->setLocale($value);
    return redirect()->to(url()->previous());
    }
    public function render()
    {
        return view('livewire.language-switcher');
    }
}
