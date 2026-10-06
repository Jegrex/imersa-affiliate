<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\DashboardMetricsService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    protected DashboardMetricsService $metricsService;

    public function __construct(DashboardMetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }

    public function __invoke(): View
    {
        $metrics = $this->metricsService->getMetrics();

        return view('admin.dashboard', compact('metrics'));
    }
}
