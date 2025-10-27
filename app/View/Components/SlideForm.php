<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SlideForm extends Component
{

    public $buttonText;
    public $buttonIcon;
    public $title;
    /**
     * Create a new component instance.
     */
    public function __construct($buttonText = '',  $buttonIcon = '',   $title = 'Form')
    {
        $this->buttonText = $buttonText;
        $this->buttonIcon = $buttonIcon;
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.slide-form');
    }
}
