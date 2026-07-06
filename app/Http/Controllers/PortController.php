<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PortController extends Controller
{
    public function index(): View
    {
        return view('ports.index');
    }
}