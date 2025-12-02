<?php

namespace App\View\Components;

use Illuminate\View\Component;

class GwCard extends Component
{
    public $gw;

    /**
     * @param array $gw Single GW performance dataset
     */
    public function __construct($gw)
    {
        $this->gw = $gw;
    }

    public function render()
    {
        return view('components.gw-card');
    }
}
