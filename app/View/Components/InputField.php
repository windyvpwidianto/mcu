<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InputField extends Component
{
   public string $name;
    public ?string $wireModel;
    public string $label;
    public string $placeholder;
    public bool $required;
    public string $type;
    public string $id;

    public function __construct(
        string $name,
        string $label = '',
        string $placeholder = '',
        string $type = 'text',
        bool $required = false,
        ?string $wireModel = null,
        ?string $id = null
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->type = $type;
        $this->required = $required;
        $this->wireModel = $wireModel;
        $this->id = $id ?? $name;
    }
    
    public function render(): View|Closure|string
    {
        return view('components.input-field');
    }
}
