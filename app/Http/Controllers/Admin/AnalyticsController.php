<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsQueryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AnalyticsController extends Controller
{
    protected AnalyticsQueryService $analyticsService;

    public function __construct(AnalyticsQueryService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request): View|RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || !$admin->is_active || $admin->role !== 'admin') {
            abort(403, 'Akses tidak diizinkan.');
        }

        $from = $request->query('from');
        $to = $request->query('to');

        try {
            $analytics = $this->analyticsService->query($from, $to);
        } catch (ValidationException $e) {
            return redirect()->route('admin.analytics.index')
                ->withErrors($e->errors())
                ->withInput();
        }

        return view('admin.analytics.index', compact('analytics'));
    }
}
