@extends('layouts.app')

@section('title', 'Mensuales')

@section('content')
<div class="stat-card">
    <h3 id="monthly-title" style="margin-bottom: 1.5rem; font-weight: 900;">Cargos Mensuales</h3>
    <form id="monthly-form" style="margin-bottom: 1.5rem;">
        <input type="hidden" name="id" id="monthly-id">
        <div class="form-group"><label>Concepto</label><input type="text" name="name" id="monthly-name" required></div>
        <div class="form-group"><label>Monto</label><input type="number" name="amount" id="monthly-amount" required></div>
        <div class="form-group"><label>Categoría</label><select name="category_id" id="monthly-category" class="category-selector-all" required></select></div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group"><label>Día del Mes</label><input type="number" name="due_day" id="monthly-day" min="1" max="31" required></div>
            <div class="form-group"><label>Método</label><select name="payment_method_id" id="monthly-method" class="method-selector"></select></div>
        </div>
        <input type="hidden" name="is_recurring" value="1">
        <input type="hidden" name="date" value="{{ date('Y-m-d') }}">
        <button type="submit" id="monthly-submit-btn" class="btn-primary" style="background: var(--accent);">GUARDAR RECURRENTE</button>
        <button type="button" id="monthly-cancel-edit" style="display:none; width:100%; margin-top:10px; border:none; background:transparent; font-weight:800; color:var(--text-muted); cursor:pointer;" onclick="cancelMonthlyEdit()">CANCELAR EDICIÓN</button>
    </form>
    <div id="monthly-list"></div>
</div>
@endsection

@push('scripts')
<script>
    window.editMonthly = (e) => {
        const item = JSON.parse(decodeURIComponent(e));
        document.getElementById('monthly-id').value = item.id;
        document.getElementById('monthly-name').value = item.name;
        document.getElementById('monthly-amount').value = item.amount;
        document.getElementById('monthly-category').value = item.category_id;
        document.getElementById('monthly-day').value = item.due_day;
        document.getElementById('monthly-method').value = item.payment_method_id || '';
        document.getElementById('monthly-title').innerText = 'EDITAR CARGO ✏️';
        document.getElementById('monthly-submit-btn').innerText = 'ACTUALIZAR CARGO';
        document.getElementById('monthly-cancel-edit').style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    window.cancelMonthlyEdit = () => {
        document.getElementById('monthly-form').reset();
        document.getElementById('monthly-id').value = '';
        document.getElementById('monthly-title').innerText = 'Cargos Mensuales';
        document.getElementById('monthly-submit-btn').innerText = 'GUARDAR RECURRENTE';
        document.getElementById('monthly-cancel-edit').style.display = 'none';
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.fetchCategories();
        window.fetchMethods();
        window.fetchMonthly();
    });
</script>
@endpush
