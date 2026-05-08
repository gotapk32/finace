@extends('layouts.app')

@section('title', 'Historial de Precios')

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('shopping.index') }}" style="text-decoration: none; color: var(--text-muted); font-size: 0.7rem; font-weight: 800; display: flex; align-items: center; gap: 5px; margin-bottom: 10px;">
        <i class="fas fa-arrow-left"></i> VOLVER A COMPRAS
    </a>
    <h1 style="font-weight: 900; font-size: 1.8rem; letter-spacing: -1px;">Tendencias de Precios 📈</h1>
    <p style="font-size: 0.8rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">¿Cuánto suben y bajan las cosas?</p>
</div>

<div style="display: grid; gap: 15px;">
    @forelse($items as $item)
        <a href="{{ route('shopping.item_details', $item->id) }}" class="stat-card" style="display: flex; justify-content: space-between; align-items: center; text-decoration: none; color: inherit;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="width: 45px; height: 45px; background: #f8fafc; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                    <i class="fas fa-tag"></i>
                </div>
                <div>
                    <h4 style="font-weight: 900; font-size: 1rem;">{{ $item->name }}</h4>
                    <p style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800;">
                        PRECIO ACTUAL: ${{ number_format((float)$item->current_price, 2) }}
                    </p>
                </div>
            </div>
            
            <div style="text-align: right;">
                @php
                    $change = (float)$item->percentage_change;
                @endphp
                @if($change > 0)
                    <div style="color: #f43f5e; font-weight: 900; font-size: 1rem;">
                        <i class="fas fa-arrow-up"></i> {{ number_format($change, 1) }}%
                    </div>
                @elseif($change < 0)
                    <div style="color: #10b981; font-weight: 900; font-size: 1rem;">
                        <i class="fas fa-arrow-down"></i> {{ number_format(abs($change), 1) }}%
                    </div>
                @else
                    <div style="color: var(--text-muted); font-weight: 900; font-size: 1rem;">
                        --
                    </div>
                @endif
                <p style="font-size: 0.55rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">VARIACIÓN</p>
            </div>
        </a>
    @empty
        <div style="text-align: center; padding: 3rem 1rem;">
            <p style="color: var(--text-muted); font-weight: 800; font-size: 0.8rem;">REGISTRA TUS COMPRAS PARA VER LAS TENDENCIAS</p>
        </div>
    @endforelse
</div>
@endsection
