<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Dashboard', [
            'title' => __('app.dashboard'),
            'summary' => __('app.dashboard_summary'),
        ]);
    }
}
