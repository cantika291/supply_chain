<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class WatchlistController extends Controller
{
    public function index(): View
    {
        return view('watchlist.index');
    }
}