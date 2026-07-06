<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ComparisonController extends Controller
{
    public function index(): View
    {
        return view('comparison.index');
    }
}