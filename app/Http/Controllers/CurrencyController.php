<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CurrencyController extends Controller
{
    public function index(): View
    {
        return view('currency.index');
    }
}