@extends('layouts.app')

@section('title', $list->name)

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('shopping.index') }}" style="text-decoration: none; color: var(--text-muted); font-size: 0.7rem; font-weight: 800; display: flex; align-items: center; gap: 5px; margin-bottom: 10px;">
        <i class="fas fa-arrow-left"></i> VOLVER A LISTAS
    </a>
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h1 style="font-weight: 900; font-size: 1.8rem; letter-spacing: -1px;">{{ $list->name }}</h1>
            <p style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">
                {{ $list->items->where('is_bought', true)->count() }} / {{ $list->items->count() }} COMPLETADOS
            </p>
        </div>
        <form action="{{ route('shopping.deleteList', $list->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta lista?')">
            @csrf @method('DELETE')
            <button style="border: none; background: #fff1f2; color: #f43f5e; padding: 10px; border-radius: 10px; cursor: pointer;">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    </div>
</div>

<div class="stat-card" style="margin-bottom: 2rem; border-left: 5px solid var(--accent);">
    <h3 style="font-weight: 900; font-size: 0.8rem; margin-bottom: 1rem; color: var(--accent);">AGREGAR ÍTEM</h3>
    <form action="{{ route('shopping.addItem', $list->id) }}" method="POST" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px;">
        @csrf
        <input type="text" name="name" placeholder="Producto (ej: Leche)" required list="existing-items"
               style="padding: 0.8rem; border-radius: 12px; border: 1px solid #e2e8f0; font-family: inherit; font-weight: 600;">
        <datalist id="existing-items">
            @foreach($existingItems as $existing)
                <option value="{{ $existing->name }}">
            @endforeach
        </datalist>
        <input type="number" name="quantity" value="1" step="0.1" required 
               style="padding: 0.8rem; border-radius: 12px; border: 1px solid #e2e8f0; font-family: inherit; font-weight: 600;">
        <button type="submit" class="btn-primary" style="width: 50px; height: 50px; border-radius: 12px; padding: 0;">
            <i class="fas fa-plus"></i>
        </button>
    </form>
</div>

@if($list->items->where('is_bought', true)->count() > 0 && $list->status == 'active')
    <div class="stat-card" style="margin-bottom: 2rem; background: var(--primary); color: white; padding: 1.5rem; text-align: center;">
        <p style="font-size: 0.6rem; font-weight: 800; text-transform: uppercase; margin-bottom: 10px; opacity: 0.8;">Lista en curso</p>
        <h3 style="font-weight: 900; font-size: 1.2rem; margin-bottom: 15px;">
            Total: ${{ number_format($list->items->where('is_bought', true)->sum(fn($i) => (float)$i->price), 2) }}
        </h3>
        <form action="{{ route('shopping.convertToExpense', $list->id) }}" method="POST">
            @csrf
            <button class="btn-primary" style="background: white; color: var(--primary); font-size: 0.75rem;">
                FINALIZAR Y REGISTRAR GASTO ⚡
            </button>
        </form>
        <p style="font-size: 0.55rem; margin-top: 10px; opacity: 0.7;">Esto sumará el total a tu control de gastos del mes automáticamente.</p>
    </div>
@endif

<div style="display: grid; gap: 12px;">
    @php
        $pending = $list->items->where('is_bought', false);
        $bought = $list->items->where('is_bought', true);
    @endphp

    @if($pending->count() > 0)
        <h3 style="font-size: 0.65rem; font-weight: 900; color: var(--text-muted); margin-top: 10px;">PENDIENTES</h3>
        @foreach($pending as $item)
            <div class="stat-card" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
                <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                    <div style="width: 24px; height: 24px; border: 2px solid var(--primary); border-radius: 6px; cursor: pointer;" 
                         onclick="openBuyModal({{ $item->id }}, '{{ $item->item->name }}', {{ $item->quantity }})"></div>
                    <div>
                        <h4 style="font-weight: 800; font-size: 0.95rem;">{{ $item->item->name }}</h4>
                        <p style="font-size: 0.65rem; color: var(--text-muted); font-weight: 800;">CANTIDAD: {{ (float)$item->quantity }}</p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    @if($item->item->current_price)
                        <span style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted);">Est. ${{ number_format((float)$item->item->current_price, 2) }}</span>
                    @endif
                    <form action="{{ route('shopping.deleteItem', $item->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button style="border: none; background: transparent; color: #cbd5e1; cursor: pointer;"><i class="fas fa-times"></i></button>
                    </form>
                </div>
            </div>
        @endforeach
    @endif

    @if($bought->count() > 0)
        <h3 style="font-size: 0.65rem; font-weight: 900; color: var(--text-muted); margin-top: 20px;">COMPRADOS</h3>
        @foreach($bought as $item)
            <div class="stat-card" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; opacity: 0.7; background: #f8fafc;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 24px; height: 24px; background: var(--accent); color: white; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <h4 style="font-weight: 800; font-size: 0.95rem; text-decoration: line-through;">{{ $item->item->name }}</h4>
                        <p style="font-size: 0.65rem; color: var(--text-muted); font-weight: 800;">${{ number_format((float)$item->price, 2) }} ({{ (float)$item->quantity }} ud)</p>
                    </div>
                </div>
                <a href="{{ route('shopping.item_details', $item->item->id) }}" style="color: var(--primary); font-size: 0.7rem;"><i class="fas fa-history"></i></a>
            </div>
        @endforeach
    @endif
</div>

<!-- Modal para marcar como comprado -->
<div id="buy-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:2000; align-items:center; justify-content:center; backdrop-filter: blur(5px); padding: 20px;">
    <div class="stat-card" style="width:100%; max-width:400px; padding:2rem;">
        <h2 id="modal-item-name" style="font-weight:900; font-size:1.4rem; margin-bottom: 5px;">Producto</h2>
        <p style="font-size:0.75rem; color:var(--text-muted); font-weight:800; margin-bottom: 1.5rem;">INDICA EL PRECIO PAGADO</p>
        
        <div class="form-group">
            <label>PRECIO UNITARIO O TOTAL</label>
            <input type="number" id="modal-price" step="0.01" placeholder="0.00" 
                   style="width: 100%; padding: 1rem; border-radius: 16px; border: 1px solid #e2e8f0; font-size: 1.5rem; font-weight: 900; text-align: center;">
        </div>

        <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
            <button onclick="closeBuyModal()" class="btn-primary" style="background: #f1f5f9; color: var(--text-main); flex: 1; box-shadow: none;">CANCELAR</button>
            <button onclick="confirmBuy()" class="btn-primary" style="background: var(--accent); flex: 2;">GUARDAR</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentItemId = null;

    function openBuyModal(id, name, qty) {
        currentItemId = id;
        document.getElementById('modal-item-name').innerText = name;
        document.getElementById('modal-price').value = '';
        document.getElementById('buy-modal').style.display = 'flex';
        document.getElementById('modal-price').focus();
    }

    function closeBuyModal() {
        document.getElementById('buy-modal').style.display = 'none';
    }

    function confirmBuy() {
        const price = document.getElementById('modal-price').value;
        if (!price || price <= 0) {
            alert('Por favor ingresa un precio válido');
            return;
        }

        fetch(`/compras/items/${currentItemId}/buy`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ price: price })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
</script>
@endpush
@endsection
