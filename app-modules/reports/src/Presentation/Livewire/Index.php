<?php

namespace Modules\Reports\Presentation\Livewire;

use Livewire\Component;
use Modules\Reports\Application\Queries\OperationalReport;

class Index extends Component
{
    protected OperationalReport $report;

    public function boot(OperationalReport $report): void
    {
        $this->report = $report;
    }

    public function render()
    {
        return view('reports::index', $this->report->get())
            ->title(__('reports::messages.reports'));
    }
}
