@props(['icon' => 'bi-hourglass-split', 'stage' => ''])

<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi {{ $icon }}" style="font-size: 3rem; color: #2c8a5e;"></i>
        <h4 class="mt-3">{{ $slot }}</h4>
        <p class="text-muted mb-0">Fitur ini akan dibangun penuh di <strong>{{ $stage }}</strong>.</p>
    </div>
</div>