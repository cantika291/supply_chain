@extends('layouts.app')

@section('title', 'Favorite Monitoring')

@section('content')
<div class="row g-4">

    <div class="col-12">
        <h4 class="mb-1">Favorite Monitoring List</h4>
        <p class="text-muted mb-0">Negara-negara yang kamu pantau secara khusus</p>
    </div>

    @if (count($favorites) === 0)
        <div class="col-12">
            <div class="card border-0 shadow-sm text-center py-5">
                <i class="bi bi-star text-muted" style="font-size: 2.5rem;"></i>
                <p class="text-muted mt-3 mb-1">Belum ada negara favorit.</p>
                <small class="text-muted">Buka <a href="{{ route('countries.index') }}">Country Dashboard</a> lalu klik ikon bintang untuk menambahkan.</small>
            </div>
        </div>
    @else
        @foreach ($favorites as $fav)
            <div class="col-md-6 col-lg-4" id="favCard-{{ $fav['favorite_id'] }}">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                @if ($fav['flag_url'])
                                    <img src="{{ $fav['flag_url'] }}" style="height: 24px; border-radius: 3px;">
                                @endif
                                <h6 class="mb-0">{{ $fav['name'] }}</h6>
                            </div>
                            <button class="btn btn-sm btn-outline-danger btn-remove-fav"
                                data-id="{{ $fav['favorite_id'] }}" title="Hapus dari favorit">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <small class="text-muted d-block mb-3">{{ $fav['capital'] }} · {{ $fav['region'] }}</small>

                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="text-muted">GDP</td>
                                <td class="text-end fw-semibold">
                                    {{ $fav['gdp'] ? number_format($fav['gdp'] / 1_000_000_000, 1).' B USD' : 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Inflasi</td>
                                <td class="text-end">{{ $fav['inflation'] ? number_format($fav['inflation'], 2).'%' : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Risk Score</td>
                                <td class="text-end">
                                    @if ($fav['risk_score'])
                                        <span class="badge {{ $fav['risk_level'] === 'Low' ? 'bg-success' : ($fav['risk_level'] === 'Medium' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                            {{ $fav['risk_score'] }} ({{ $fav['risk_level'] }})
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Cuaca</td>
                                <td class="text-end">
                                    {{ $fav['temperature'] ? $fav['temperature'].'°C' : 'N/A' }}
                                    @if ($fav['storm_risk'])
                                        <span class="badge bg-light text-dark border">{{ strtoupper($fav['storm_risk']) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kurs</td>
                                <td class="text-end">
                                    {{ $fav['exchange_rate'] ? number_format($fav['exchange_rate'], 2).' '.$fav['currency_code'] : 'N/A' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <a href="{{ route('countries.index') }}?country={{ $fav['cca3'] }}" class="btn btn-sm btn-outline-primary w-100">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-remove-fav').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        if (!confirm('Hapus negara ini dari favorit?')) return;

        fetch(`/watchlist/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(() => {
            document.getElementById(`favCard-${id}`).remove();
        })
        .catch(err => console.error(err));
    });
});
</script>
@endpush