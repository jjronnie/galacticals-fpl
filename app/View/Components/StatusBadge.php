<?php

namespace App\View\Components;

use Illuminate\View\Component;

class StatusBadge extends Component
{
    public string $status;
    public string $colorClasses;

    public function __construct(string $status)
    {
        $this->status = $status;
        $this->colorClasses = $this->getColorClasses($status);
    }

    private function getColorClasses(string $status): string
    {
        return match ($status) {
            'active' => 'bg-green-100 text-green-800',
            'enabled' => 'bg-green-100 text-green-800',

            'paid' => 'bg-green-100 text-green-800',
            'partial' => 'bg-yellow-100 text-yellow-800',

            'inactive' => 'bg-gray-100 text-gray-700',
            'pending' => 'bg-yellow-100 text-yellow-700',
            'expired' => 'bg-orange-100 text-orange-700',
            'on-leave' => 'bg-yellow-100 text-yellow-800',
            'suspended' => 'bg-orange-100 text-orange-800',
            'terminated' => 'bg-red-100 text-red-800',
            'dismissed' => 'bg-red-200 text-red-900',
            'resigned' => 'bg-gray-100 text-gray-800',
            'deserted' => 'bg-red-100 text-red-800',
            'off-duty' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function render()
    {
        return view('components.status-badge');
    }
}
