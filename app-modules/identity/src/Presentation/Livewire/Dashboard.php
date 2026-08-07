<?php

namespace Modules\Identity\Presentation\Livewire;

use App\Models\User;
use Livewire\Component;
use Modules\Identity\Application\Queries\DashboardMetrics;

class Dashboard extends Component
{
    protected DashboardMetrics $metrics;

    public function boot(DashboardMetrics $metrics): void
    {
        $this->metrics = $metrics;
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();

        return view('identity::dashboard', [
            'metrics' => $this->metrics->for($user),
        ])->title(__('app.dashboard'));
    }
}
