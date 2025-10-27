<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class PageTitle extends Component
{
    public ?string $title;
    public ?string $subtitle;

    /**
     * Create a new component instance.
     */
    public function __construct(?string $title = null, ?string $subtitle = null)
    {
        // If no custom title is provided, fall back to route name
        $this->title = $title ?? ucfirst(Str::before(Route::currentRouteName() ?? '', '.'));
        $this->subtitle = $subtitle;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.page-title');
    }
}
