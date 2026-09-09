<?php

namespace App\View\Components\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Label extends Component
{
     public string $label;
    public bool $required;
    /**
     * Create a new component instance.
     */
     public function __construct(string $label, bool $required = false)
    {
        $this->label = $label;
        $this->required = $required;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.form.label');
    }
}
