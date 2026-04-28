@extends('layouts.app')

@section('title', 'Historial')

@section('content')
<div class="stat-card" style="margin-bottom: 1.5rem; padding: 1.5rem;">
    <h3 style="font-weight: 900; margin-bottom: 1.5rem;">Historial 🔍</h3>
    
    <!-- BALANCE DE DEUDAS RAPIDO -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 1.5rem; background: #f8fafc; padding: 10px; border-radius: 15px;">
        <div style="text-align: center;">
            <p style="font-size: 0.5rem; font-weight: 800; color: var(--text-muted); margin-bottom: 2px;">ME DEBEN</p>
            <h4 id="history-me-deben" style="font-weight: 900; color: var(--accent); font-size: 0.9rem;">$0</h4>
        </div>
        <div style="text-align: center; border-left: 1px solid #e2e8f0;">
            <p style="font-size: 0.5rem; font-weight: 800; color: var(--text-muted); margin-bottom: 2px;">YO DEBO</p>
            <h4 id="history-yo-debo" style="font-weight: 900; color: var(--secondary); font-size: 0.9rem;">$0</h4>
        </div>
    </div>
    
    <!-- BUSCADOR -->
    <div class="form-group" style="margin-bottom: 1rem;">
        <input type="text" id="filter-search" placeholder="Buscar por concepto..." oninput="window.fetchExpenses()" style="padding: 14px; border-radius: 14px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: 700; width: 100%; font-size: 0.8rem;">
    </div>

    <div style="display: flex; gap: 5px; margin-bottom: 1.5rem;">
        <button onclick="setDateRange('week')" style="flex:1; border:none; background:#f1f5f9; padding:8px; border-radius:10px; font-size:0.6rem; font-weight:800; cursor:pointer;">ESTA SEMANA</button>
        <button onclick="setDateRange('month')" style="flex:1; border:none; background:#f1f5f9; padding:8px; border-radius:10px; font-size:0.6rem; font-weight:800; cursor:pointer;">ESTE MES</button>
        <button onclick="setDateRange('year')" style="flex:1; border:none; background:#f1f5f9; padding:8px; border-radius:10px; font-size:0.6rem; font-weight:800; cursor:pointer;">ESTE AÑO</button>
    </div>

    <!-- FECHAS -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 1rem;">
        <div class="form-group">
            <label style="font-size: 0.55rem; font-weight: 800; color: var(--text-muted);">DESDE</label>
            <input type="date" id="filter-start" onchange="window.fetchExpenses()" style="font-size: 0.7rem; padding: 10px; border-radius: 10px;">
        </div>
        <div class="form-group">
            <label style="font-size: 0.55rem; font-weight: 800; color: var(--text-muted);">HASTA</label>
            <input type="date" id="filter-end" onchange="window.fetchExpenses()" style="font-size: 0.7rem; padding: 10px; border-radius: 10px;">
        </div>
    </div>

    <!-- CATEGORÍA Y PAGADOR -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
        <select id="filter-type" onchange="window.fetchExpenses()" style="padding: 10px; font-size: 0.7rem; border-radius: 10px; background: #f1f5f9; border: none; font-weight: 700;">
            <option value="all">TODOS</option>
            <option value="shared">Compartidos</option>
            <option value="personal">Privados</option>
            <option value="deuda">Deudas</option>
        </select>
        <select id="filter-payer" onchange="window.fetchExpenses()" style="padding: 10px; font-size: 0.7rem; border-radius: 10px; background: #f1f5f9; border: none; font-weight: 700;">
            <option value="all">PAGADOR</option>
        </select>
    </div>

    <div class="form-group" style="margin-top: 10px;">
        <select id="filter-method" onchange="window.fetchExpenses()" style="width:100%; padding: 10px; font-size: 0.7rem; border-radius: 10px; background: #f1f5f9; border: none; font-weight: 700;">
            <option value="all">TODOS LOS MÉTODOS</option>
            <option value="0">Efectivo / Ninguno</option>
        </select>
    </div>
</div>

<div id="main-list"></div>
@endsection

@push('scripts')
<script>
    function setDateRange(range) {
        const startInput = document.getElementById('filter-start');
        const endInput = document.getElementById('filter-end');
        const today = new Date();
        let start = new Date();
        
        if (range === 'week') {
            const day = today.getDay();
            const diff = today.getDate() - day + (day === 0 ? -6 : 1);
            start = new Date(today.setDate(diff));
        } else if (range === 'month') {
            start = new Date(today.getFullYear(), today.getMonth(), 1);
        } else if (range === 'year') {
            start = new Date(today.getFullYear(), 0, 1);
        }
        
        const formatDate = (d) => {
            const offset = d.getTimezoneOffset();
            return new Date(d.getTime() - (offset * 60 * 1000)).toISOString().split('T')[0];
        };

        startInput.value = formatDate(start);
        endInput.value = formatDate(new Date());
        window.fetchExpenses();
    }

    document.addEventListener('DOMContentLoaded', async () => {
        const urlParams = new URLSearchParams(window.location.search);
        
        await window.fetchMethods(); // Cargar métodos en el select si es necesario
        
        // Poblar el select de métodos en el filtro
        const res = await fetch('/api/payment-methods');
        const methods = await res.json();
        const methodSelect = document.getElementById('filter-method');
        methods.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = m.name;
            methodSelect.appendChild(opt);
        });

        const searchParam = urlParams.get('search');
        if (searchParam) {
            const searchInput = document.getElementById('filter-search');
            if (searchInput) searchInput.value = searchParam;
        }

        const methodParam = urlParams.get('method_id');
        if (methodParam) {
            document.getElementById('filter-method').value = methodParam;
        }

        const rangeParam = urlParams.get('range');
        if (rangeParam) setDateRange(rangeParam);

        window.fetchSummary();
        window.fetchExpenses();
    });
</script>
@endpush
