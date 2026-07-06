<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class WeatherController extends Controller
{
    public function index(): View
    {
        return view('weather.index');
    }
}