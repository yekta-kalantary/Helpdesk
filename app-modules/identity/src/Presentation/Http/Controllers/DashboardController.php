<?php

namespace Modules\Identity\Presentation\Http\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Identity\Application\Queries\DashboardMetrics;

class DashboardController extends Controller
{
    public function __invoke(DashboardMetrics $metrics): View
    {
        /** @var User $user */
        $user = auth()->user();

        return view('identity::dashboard', [
            'metrics' => $metrics->for($user),
        ]);
    }
}
