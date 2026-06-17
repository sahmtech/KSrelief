<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ReportController extends Controller
{
    public function campaigns(): View
    {
        return view('pages.reports.campaigns');
    }

    public function patients(): View
    {
        return view('pages.reports.patients');
    }

    public function attendance(): View
    {
        return view('pages.reports.attendance');
    }
}
