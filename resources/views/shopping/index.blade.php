@extends('layouts.app')

@section('title', 'Lista de Compras')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-weight: 900; font-size: 1.8rem; letter-spacing: -1px;">Compras 🛒</h1>
        <p style="font-size: 0.8rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Organiza tus listas y ahorra</p>
    </div>
    <a href="{{ route('shopping.history') }}" class="stat-card" style="padding: 0.8rem 1.2rem; text-decoration: none; display: flex; align-items: center; gap: 8px; border: 1px solid var(--primary-glow);">
        <i class="fas fa-chart-line" style="color: var(--primary);"></i>
        <span style="font-size: 0.7rem; font-weight: 900; color: var(--primary);">TENDENCIAS</span>
    </a>
</div>

<div class="stat-card" style="margin-bottom: 2rem; border-left: 5px solid var(--primary);">
    <h3 style="font-weight: 900; font-size: 0.9rem; margin-bottom: 1rem;">NUEVA LISTA</h3>
    <form action="{{ route('shopping.store') }}" method="POST" style="display: flex; gap: 10px;">
        @csrf
        <input type="text" name="name" placeholder="Ej: Supermercado Mensual" required 
               style="flex: 1; padding: 0.8rem; border-radius: 12px; border: 1px solid #e2e8f0; font-family: inherit; font-weight: 600;">
        <button type="submit" class="btn-primary" style="width: auto; padding: 0 1.5rem; border-radius: 12px;">CREAR</button>
    </form>
</div>

<div style="display: grid; gap: 15px;">
    @forelse($lists as $list)
        <a href="{{ route('shopping.show', $list->id) }}" class="stat-card" style="display: flex; justify-content: space-between; align-items: center; text-decoration: none; color: inherit;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="width: 50px; height: 50px; background: var(--primary-glow); border-radius: 15px; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.2rem;">
                    <i class="fas fa-shopping-basket"></i>
                </div>
                <div>
                    <h4 style="font-weight: 900; font-size: 1rem;">{{ $list->name }}</h4>
                    <p style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800;">
                        {{ $list->pending_count }} ÍTEMS PENDIENTES
                    </p>
                </div>
            </div>
            <i class="fas fa-chevron-right" style="color: var(--text-muted); font-size: 0.8rem;"></i>
        </a>
    @empty
        <div style="text-align: center; padding: 3rem 1rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">🛒</div>
            <p style="color: var(--text-muted); font-weight: 800; font-size: 0.8rem;">AÚN NO TIENES LISTAS DE COMPRAS</p>
        </div>
    @endforelse
</div>
@endsection
