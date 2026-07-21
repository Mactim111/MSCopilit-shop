<?php
namespace App\View\Components;

use Illuminate\View\Component;

class VariantCard extends Component
{
    public $variant;
    public $isFavorite;

    public function __construct($variant, $isFavorite = false)
    {
        $this->variant = $variant;
        $this->isFavorite = $isFavorite;
    }

    public function render()
    {
        return view('components.variant-card');
    }
}

