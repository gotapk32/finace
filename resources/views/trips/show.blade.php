@extends('layouts.app')

@section('title', 'Detalle del Viaje')

@section('content')
<div id="trip-header"></div>

<div class="stat-card" style="margin-bottom: 2rem; padding: 1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <div>
            <h4 style="font-weight: 900; font-size: 0.9rem; margin:0;">Presupuesto del Viaje</h4>
            <p style="font-size: 0.6rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Estado de Gastos</p>
        </div>
        <div id="trip-percentage" style="font-weight: 900; font-size: 1.2rem; color: var(--primary);">0%</div>
    </div>
    
    <div style="height: 12px; background: #f1f5f9; border-radius: 10px; overflow: hidden; margin-bottom: 0.8rem;">
        <div id="trip-progress-bar" style="height: 100%; width: 0%; background: var(--primary); transition: width 1s ease-out;"></div>
    </div>

    <div style="display:flex; justify-content:space-between; font-weight: 800; font-size: 0.7rem;">
        <span id="trip-spent" style="color: var(--text-main);">$0</span>
        <span id="trip-limit" style="color: var(--text-muted);">Límite: $0</span>
    </div>
</div>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
    <h3 style="font-weight: 900; font-size: 1rem;">Gastos del Viaje 💸</h3>
    <button onclick="location.href='{{ route('expenses.create') }}?trip_id={{ $id }}'" class="btn-primary" style="width:auto; padding:8px 15px; font-size:0.6rem; box-shadow:none;">+ GASTO</button>
</div>

<div id="trip-expenses-list" style="display:flex; flex-direction:column; gap:10px;">
    <!-- Cargado vía JS -->
</div>

<div style="margin-top: 2rem; display:flex; gap:10px;">
    <button onclick="toggleTripStatus()" id="status-btn" class="btn-primary" style="background:#f1f5f9; color:var(--primary); flex:1; box-shadow:none;">FINALIZAR VIAJE</button>
    <button onclick="deleteTrip()" class="btn-primary" style="background:#fff1f2; color:var(--secondary); flex:1; box-shadow:none;">ELIMINAR</button>
</div>
@endsection

@push('scripts')
<script>
    const tripId = {{ $id }};
    let isTripActive = true;

    async function fetchTripDetails() {
        try {
            const res = await fetch(`/api/trips/${tripId}/summary`);
            const data = await res.json();
            const trip = data.trip;
            isTripActive = trip.is_active;

            // Header
            document.getElementById('trip-header').innerHTML = `
                <div style="margin-bottom: 2rem;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:5px;">
                        <button onclick="location.href='{{ route('trips.index') }}'" style="border:none; background:transparent; font-size:1.2rem; color:var(--text-main); cursor:pointer;"><i class="fas fa-chevron-left"></i></button>
                        <h2 style="font-weight:900; font-size:1.5rem;">${trip.name}</h2>
                    </div>
                    <p style="font-size:0.75rem; color:var(--text-muted); font-weight:800; text-transform:uppercase; margin-left:35px;">
                        <i class="fas fa-map-marker-alt"></i> ${trip.destination} • ${new Date(trip.start_date).toLocaleDateString()}
                    </p>
                </div>
            `;

            // Budget
            const spent = data.total_spent;
            const limit = data.budget;
            const perc = limit > 0 ? Math.min((spent / limit) * 100, 100) : 0;
            
            document.getElementById('trip-spent').textContent = `$${spent.toLocaleString()}`;
            document.getElementById('trip-limit').textContent = `Límite: $${limit.toLocaleString()}`;
            document.getElementById('trip-percentage').textContent = `${Math.round(spent / (limit || 1) * 100)}%`;
            document.getElementById('trip-progress-bar').style.width = `${perc}%`;
            if (perc >= 90) document.getElementById('trip-progress-bar').style.background = 'var(--secondary)';

            // Status button
            const statusBtn = document.getElementById('status-btn');
            statusBtn.textContent = trip.is_active ? 'FINALIZAR VIAJE' : 'REACTIVAR VIAJE';
            statusBtn.style.color = trip.is_active ? 'var(--primary)' : 'var(--accent)';

            // Expenses
            const list = document.getElementById('trip-expenses-list');
            if (data.expenses.length === 0) {
                list.innerHTML = '<p style="text-align:center; padding:2rem; color:var(--text-muted); font-size:0.7rem; font-weight:800;">No hay gastos registrados en este viaje.</p>';
            } else {
                list.innerHTML = data.expenses.map(e => `
                    <div class="stat-card" style="padding:1rem; display:flex; justify-content:space-between; align-items:center;" onclick="window.showExpenseDetail(${e.id})">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:40px; height:40px; background:#f8fafc; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
                                ${e.category?.icon || '💰'}
                            </div>
                            <div>
                                <p style="font-weight:900; font-size:0.85rem;">${e.name}</p>
                                <p style="font-size:0.6rem; color:var(--text-muted); font-weight:800; text-transform:uppercase;">${new Date(e.date).toLocaleDateString()} • ${e.payer}</p>
                            </div>
                        </div>
                        <p style="font-weight:900; font-size:1rem; color:var(--text-main);">$${parseFloat(e.amount).toLocaleString()}</p>
                    </div>
                `).join('');
            }

        } catch (e) {
            console.error(e);
        }
    }

    async function toggleTripStatus() {
        if (!confirm(`¿Estás seguro de que quieres ${isTripActive ? 'finalizar' : 'reactivar'} este viaje?`)) return;
        
        try {
            await fetch(`/api/trips/${tripId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ is_active: !isTripActive })
            });
            fetchTripDetails();
        } catch (e) {
            alert('Error al actualizar estado');
        }
    }

    async function deleteTrip() {
        if (!confirm('¿Estás seguro de eliminar este viaje? Los gastos asociados NO se eliminarán, pero ya no estarán vinculados a este viaje.')) return;
        
        try {
            const res = await fetch(`/api/trips/${tripId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            if (res.ok) location.href = '{{ route("trips.index") }}';
        } catch (e) {
            alert('Error al eliminar');
        }
    }

    document.addEventListener('DOMContentLoaded', fetchTripDetails);
</script>
@endpush
