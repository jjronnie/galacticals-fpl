<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Logo extends Component
{
    public $width;
    public $height;
    public $class;

    public function __construct($width = '', $height = '', $class = '')
    {
        $this->width = $width;
        $this->height = $height;
        $this->class = $class;
    }

    public function render()
    {
        return view('components.logo');
    }
}
