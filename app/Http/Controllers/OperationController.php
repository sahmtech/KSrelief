<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class OperationController extends Controller
{
    public function attendance(): View
    {
        return view('pages.operations.attendance');
    }

    public function transportation(): View
    {
        return view('pages.operations.transportation');
    }

    public function activities(): View
    {
        return view('pages.operations.activities');
    }
}
