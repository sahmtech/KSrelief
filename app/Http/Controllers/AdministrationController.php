<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AdministrationController extends Controller
{
    public function settings(): View
    {
        return view('pages.administration.settings');
    }
}
