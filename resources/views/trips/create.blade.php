@extends('layouts.app')

@section('title', 'Nuevo Viaje')

@section('content')
<div class="stat-card" style="padding: 2rem;">
    <h3 style="font-weight: 900; margin-bottom: 2rem;">Nuevo Viaje 🌍</h3>

    <form id="trip-form">
        @csrf
        <div class="form-group">
            <label>Nombre del Viaje</label>
            <input type="text" name="name" placeholder="Ej. Vacaciones Japón" required>
        </div>

        <div class="form-group">
            <label>Destino</label>
            <input type="text" name="destination" placeholder="Ciudad, País" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Fecha Inicio</label>
                <input type="date" name="start_date" required>
            </div>
            <div class="form-group">
                <label>Fecha Fin (Opcional)</label>
                <input type="date" name="end_date">
            </div>
        </div>

        <div class="form-group">
            <label>Presupuesto Estimado</label>
            <input type="number" name="budget" step="0.01" placeholder="0.00">
        </div>

        <div class="form-group">
            <label>Notas / Descripción</label>
            <textarea name="description" style="width:100%; padding:1rem; border-radius:16px; border:1px solid #e2e8f0; background:#f8fafc; font-family:inherit; font-weight:600; min-height:100px;"></textarea>
        </div>

        <div class="form-group" style="display:flex; align-items:center; gap:10px; background:#f8fafc; padding:15px; border-radius:16px;">
            <input type="checkbox" name="is_personal" id="is_personal" style="width:20px; height:20px;">
            <label for="is_personal" style="margin:0;">¿Es un viaje privado?</label>
        </div>

        <button type="submit" class="btn-primary" style="margin-top: 1rem;">CREAR VIAJE</button>
        <button type="button" onclick="history.back()" style="width:100%; margin-top:15px; border:none; background:transparent; font-weight:800; color:var(--text-muted); cursor:pointer;">CANCELAR</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('trip-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> CREANDO...';

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        data.is_personal = formData.has('is_personal');

        try {
            const res = await fetch('/api/trips', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            if (res.ok) {
                location.href = '{{ route("trips.index") }}';
            } else {
                const err = await res.json();
                alert('Error: ' + Object.values(err.errors).flat().join('\n'));
                btn.disabled = false;
                btn.innerHTML = 'CREAR VIAJE';
            }
        } catch (e) {
            alert('Error de conexión');
            btn.disabled = false;
            btn.innerHTML = 'CREAR VIAJE';
        }
    });
</script>
@endpush
