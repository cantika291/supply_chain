<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Port;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortController extends Controller
{
    public function index(): View
    {
        $totalPorts     = Port::count();
        $totalCountries = Port::whereNotNull('country_id')->distinct('country_id')->count();

        $harborTypes = Port::selectRaw('harbor_type, COUNT(*) as total')
            ->whereNotNull('harbor_type')
            ->groupBy('harbor_type')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // Semua negara yang punya pelabuhan (untuk dropdown search)
        $countries = Country::has('ports')
            ->withCount('ports')
            ->orderBy('name')
            ->get();

        // Top 10 untuk ditampilkan di card
        $topCountries = $countries->sortByDesc('ports_count')->take(10);

        // Sample 500 pelabuhan untuk peta
        $mapPorts = Port::with('country:id,name,cca3,flag_url')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->inRandomOrder()
            ->take(500)
            ->get(['id', 'name', 'latitude', 'longitude', 'harbor_type', 'country_id', 'port_code']);

        $mapData = $mapPorts->map(fn($p) => [
            'name'        => $p->name,
            'lat'         => (float) $p->latitude,
            'lng'         => (float) $p->longitude,
            'harbor_type' => $p->harbor_type ?? 'Unknown',
            'port_code'   => $p->port_code ?? '—',
            'country'     => $p->country?->name ?? 'Unknown',
            'flag'        => $p->country?->flag_url ?? null,
        ]);

        return view('ports.index', compact(
            'totalPorts', 'totalCountries', 'harborTypes',
            'countries', 'topCountries', 'mapData'
        ));
    }

    public function search(Request $request): JsonResponse
    {
        $query   = $request->query('q', '');
        $country = $request->query('country', '');

        $ports = Port::with('country:id,name,cca3,flag_url')
            ->when($query, fn($q) => $q->where('name', 'like', "%{$query}%"))
            ->when($country, fn($q) => $q->whereHas('country',
                fn($c) => $c->where('cca3', $country)))
            ->take(200)
            ->get(['id', 'name', 'latitude', 'longitude', 'harbor_type', 'port_code', 'country_id']);

        return response()->json($ports->map(fn($p) => [
            'name'        => $p->name,
            'lat'         => (float) $p->latitude,
            'lng'         => (float) $p->longitude,
            'harbor_type' => $p->harbor_type ?? 'Unknown',
            'port_code'   => $p->port_code ?? '—',
            'country'     => $p->country?->name ?? 'Unknown',
            'flag'        => $p->country?->flag_url ?? null,
        ]));
    }
}