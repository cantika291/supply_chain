<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class RiskScoreController extends Controller
{
    public function index(): View
    {
        return view('risk-scoring.index');
    }
}