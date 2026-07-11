@extends('layouts.app')
@section('title', 'Kelola Pelabuhan')
@section('content')
<div class="row g-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">Kelola Dataset Pelabuhan</h4>
            <p class="text-muted mb-0">{{ $ports->total() }} pelabuhan terdaftar</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    {{-- Form Tambah Pelabuhan --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Pelabuhan Baru</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.ports.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nama Pelabuhan</label>
                            <input type="text" name="name" class="form-control" required
                                placeholder="Contoh: Port of Rotterdam">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Negara</label>
                            <select name="country_id" class="form-select">
                                <option value="">-- Pilih Negara --</option>
                                @foreach ($countries as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="0.000001" name="latitude" class="form-control"
                                required placeholder="51.9225">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="0.000001" name="longitude" class="form-control"
                                required placeholder="4.4792">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipe Pelabuhan</label>
                            <select name="harbor_type" class="form-select">
                                <option value="">-- Opsional --</option>
                                <option>Coastal Natural</option>
                                <option>River Natural</option>
                                <option>Coastal Artificial</option>
                                <option>River Artificial</option>
                                <option>Lake or Canal</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-geo-alt me-2"></i>Tambah Pelabuhan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Tabel Pelabuhan --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Pelabuhan</th>
                            <th>Negara</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Tipe</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ports as $port)
                            <tr>
                                <td>{{ $port->name }}</td>
                                <td class="text-muted small">{{ $port->country?->name ?? '—' }}</td>
                                <td class="text-muted small">{{ $port->latitude }}</td>
                                <td class="text-muted small">{{ $port->longitude }}</td>
                                <td class="text-muted small">{{ $port->harbor_type ?? '—' }}</td>
                                <td class="text-center">
                                    <form method="POST"
                                        action="{{ route('admin.ports.destroy', $port) }}"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm py-0 px-2"
                                            onclick="return confirm('Hapus pelabuhan {{ $port->name }}?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($ports->hasPages())
                <div class="card-footer bg-white border-0">{{ $ports->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection