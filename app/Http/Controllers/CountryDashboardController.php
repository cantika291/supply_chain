<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CountryDashboardController extends Controller
{
    public function index(): View
    {
        return view('countries.index');
    }
}