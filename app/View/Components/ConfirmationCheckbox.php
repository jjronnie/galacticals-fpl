<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ConfirmationCheckbox extends Component
{

    public string $id;
    public string $name;
    public string $label;
    public string $color;



    /**
     * Create a new component instance.
     */
       public function __construct(
        string $id = 'confirm-details',
        string $name = 'confirm_details',
        string $label = 'I confirm that I have cross-checked and entered the correct details',
        string $color = 'green'
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->label = $label;
        $this->color = $color;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.confirmation-checkbox');
    }
}
