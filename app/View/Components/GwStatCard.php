<?php

namespace App\View\Components;

use Illuminate\View\Component;

class GwStatCard extends Component
{
    public $title;
    public $color;

    public function __construct($title = 'TITLE', $color = 'green')
    {
        $this->title = $title;
        $this->color = $color;
    }

    public function render()
    {
        return view('components.gw-stat-card');
    }
}
