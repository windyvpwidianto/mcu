<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Session;

class ThemeSwitcher extends Component
{
    public $themes = ['sentry-interlock', 'goldmine', 'goldore-light', 'tokasafe', 'swimmingvoid', 'badzombie'];

    public function setTheme($theme)
    {
        if (in_array($theme, $this->themes)) {
            Session::put('theme', $theme);
            // Redirect dengan navigate: true agar transisi mulus di Livewire 3/4
            return $this->redirect(request()->header('Referer'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.theme-switcher');
    }
}
