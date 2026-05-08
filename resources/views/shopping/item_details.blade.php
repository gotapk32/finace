@extends('layouts.app')

@section('title', $item->name)

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('shopping.history') }}" style="text-decoration: none; color: var(--text-muted); font-size: 0.7rem; font-weight: 800; display: flex; align-items: center; gap: 5px; margin-bottom: 10px;">
        <i class="fas fa-arrow-left"></i> VOLVER A TENDENCIAS
    </a>
    <h1 style="font-weight: 900; font-size: 1.8rem; letter-spacing: -1px;">{{ $item->name }}</h1>
    <p style="font-size: 0.8rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Análisis de precios histórico</p>
</div>

<div class="stat-card" style="margin-bottom: 2rem; padding: 1.5rem;">
    <canvas id="priceChart" style="width: 100%; height: 250px;"></canvas>
</div>

<div style="display: grid; gap: 10px;">
    <h3 style="font-size: 0.65rem; font-weight: 900; color: var(--text-muted); margin-bottom: 5px;">HISTORIAL DE REGISTROS</h3>
    @foreach($item->priceHistories as $history)
        <div class="stat-card" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
            <div>
                <p style="font-size: 0.75rem; font-weight: 900; color: var(--text-main);">${{ number_format((float)$history->price, 2) }}</p>
                <p style="font-size: 0.6rem; color: var(--text-muted); font-weight: 800;">{{ $history->recorded_at->format('d M, Y - H:i') }}</p>
            </div>
            @if(!$loop->last)
                @php
                    $nextPrice = (float)$item->priceHistories[$loop->index + 1]->price;
                    $currentPrice = (float)$history->price;
                    $diff = $currentPrice - $nextPrice;
                    $perc = ($nextPrice > 0) ? ($diff / $nextPrice) * 100 : 0;
                @endphp
                <div style="text-align: right;">
                    @if($diff > 0)
                        <span style="color: #f43f5e; font-size: 0.7rem; font-weight: 900;">+{{ number_format($perc, 1) }}% <i class="fas fa-arrow-up"></i></span>
                    @elseif($diff < 0)
                        <span style="color: #10b981; font-size: 0.7rem; font-weight: 900;">{{ number_format($perc, 1) }}% <i class="fas fa-arrow-down"></i></span>
                    @else
                        <span style="color: var(--text-muted); font-size: 0.7rem; font-weight: 900;">--</span>
                    @endif
                </div>
            @endif
        </div>
    @endforeach
</div>

@push('scripts')
<script>
    const ctx = document.getElementById('priceChart').getContext('2d');
    const data = @json($item->priceHistories->reverse()->values());
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(h => new Date(h.recorded_at).toLocaleDateString()),
            datasets: [{
                label: 'Precio ($)',
                data: data.map(h => h.price),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#6366f1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    grid: { display: false }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
</script>
@endpush
@endsection
