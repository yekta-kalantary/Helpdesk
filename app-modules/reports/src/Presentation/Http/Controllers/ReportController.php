<?php

namespace Modules\Reports\Presentation\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Reports\Application\Queries\OperationalReport;

class ReportController extends Controller
{
    public function __invoke(OperationalReport $report): View
    {
        return view('reports::index', $report->get());
    }
}
