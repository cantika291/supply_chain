<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Port;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiPortController extends Controller
{
    /**
     * GET /api/ports
     * GET /api/v1/ports
     *
     * Query params:
     *   - country: filter berdasarkan cca3 negara
     *   - search: cari nama pelabuhan
     *   - per_page: default 50
     */
    public function index(Request $request): JsonResponse
    {
        $ports = Port::with('country:id,name,cca3')
            ->when($request->search,
                fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->country,
                fn($q) => $q->whereHas('country',
                    fn($c) => $c->where('cca3', strtoupper($request->country))))
            ->paginate((int) $request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'meta' => [
                'total'        => $ports->total(),
                'current_page' => $ports->currentPage(),
                'last_page'    => $ports->lastPage(),
            ],
            'data' => $ports->map(fn($p) => [
                'name'        => $p->name,
                'port_code'   => $p->port_code,
                'country'     => $p->country?->name,
                'cca3'        => $p->country?->cca3,
                'latitude'    => (float) $p->latitude,
                'longitude'   => (float) $p->longitude,
                'harbor_type' => $p->harbor_type,
            ]),
        ]);
    }

    /**
     * GET /api/v1/ports/search?q=rotterdam
     */
    public function search(Request $request): JsonResponse
    {
        $q = $request->query('q', '');

        if (strlen($q) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Query minimal 2 karakter.',
            ], 422);
        }

        $ports = Port::with('country:id,name,cca3')
            ->where('name', 'like', "%{$q}%")
            ->take(20)
            ->get();

        return response()->json([
            'success' => true,
            'count'   => $ports->count(),
            'data'    => $ports->map(fn($p) => [
                'name'        => $p->name,
                'port_code'   => $p->port_code,
                'country'     => $p->country?->name,
                'cca3'        => $p->country?->cca3,
                'latitude'    => (float) $p->latitude,
                'longitude'   => (float) $p->longitude,
                'harbor_type' => $p->harbor_type,
            ]),
        ]);
    }
}