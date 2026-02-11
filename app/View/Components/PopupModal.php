<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PopupModal extends Component
{
    public string $buttonText;

    public string $buttonIcon;

    public string $title;

    public function __construct(
        string $buttonText = '',
        string $buttonIcon = '',
        string $title = 'Modal'
    ) {
        $this->buttonText = $buttonText;
        $this->buttonIcon = $buttonIcon;
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.popup-modal');
    }
}
