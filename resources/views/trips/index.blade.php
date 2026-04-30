@extends('layouts.app')

@section('title', 'Mis Viajes')

@section('content')
<div class="stat-card" style="margin-bottom: 1.5rem; padding: 1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h3 style="font-weight: 900; margin:0;">Mis Viajes ✈️</h3>
        <a href="{{ route('trips.create') }}" class="btn-primary" style="width:auto; padding: 10px 20px; font-size: 0.7rem; box-shadow:none;">NUEVO VIAJE</a>
    </div>

    <div style="display: flex; gap: 5px; margin-bottom: 0.5rem;">
        <button onclick="filterTrips('active')" id="btn-active" style="flex:1; border:none; background:var(--primary); color:white; padding:10px; border-radius:12px; font-size:0.65rem; font-weight:800; cursor:pointer;">ACTIVOS</button>
        <button onclick="filterTrips('past')" id="btn-past" style="flex:1; border:none; background:#f1f5f9; color:var(--text-muted); padding:10px; border-radius:12px; font-size:0.65rem; font-weight:800; cursor:pointer;">PASADOS</button>
    </div>
</div>

<div id="trips-list" style="display: flex; flex-direction: column; gap: 1.2rem;">
    <!-- Cargado vía JS -->
    <div class="skeleton" style="height:150px; border-radius:24px;"></div>
    <div class="skeleton" style="height:150px; border-radius:24px;"></div>
</div>

@endsection

@push('scripts')
<script>
    let currentFilter = 'active';

    async function fetchTrips() {
        const list = document.getElementById('trips-list');
        try {
            const res = await fetch('/api/trips');
            const trips = await res.json();
            
            const filtered = trips.filter(t => {
                if (currentFilter === 'active') return t.is_active;
                return !t.is_active;
            });

            if (filtered.length === 0) {
                list.innerHTML = `<div style="text-align:center; padding:3rem; color:var(--text-muted);">
                    <i class="fas fa-map-marked-alt" style="font-size:3rem; margin-bottom:1rem; opacity:0.3;"></i>
                    <p style="font-weight:800; font-size:0.8rem;">No hay viajes ${currentFilter === 'active' ? 'activos' : 'pasados'}.</p>
                </div>`;
                return;
            }

            list.innerHTML = filtered.map(t => `
                <div class="stat-card" onclick="location.href='/viajes/${t.id}'" style="padding:1.5rem; cursor:pointer; position:relative; overflow:hidden;">
                    ${t.is_personal ? '<span style="position:absolute; top:0; right:0; background:var(--secondary); color:white; font-size:0.5rem; font-weight:900; padding:5px 12px; border-radius:0 0 0 15px;">PRIVADO</span>' : ''}
                    <div style="display:flex; gap:15px; align-items:center; margin-bottom:12px;">
                        <div style="width:50px; height:50px; background:var(--primary-glow); color:var(--primary); border-radius:15px; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">
                            <i class="fas fa-plane-departure"></i>
                        </div>
                        <div>
                            <h4 style="font-weight:900; font-size:1.1rem; margin:0;">${t.name}</h4>
                            <p style="font-size:0.65rem; color:var(--text-muted); font-weight:800; text-transform:uppercase;">
                                <i class="fas fa-map-marker-alt"></i> ${t.destination}
                            </p>
                        </div>
                    </div>
                    
                    <div style="display:flex; justify-content:space-between; align-items:flex-end; border-top:1px solid #f1f5f9; pt:12px; margin-top:12px; padding-top:12px;">
                        <div>
                            <p style="font-size:0.55rem; font-weight:900; color:var(--text-muted);">FECHAS</p>
                            <p style="font-weight:800; font-size:0.75rem;">${new Date(t.start_date).toLocaleDateString()} ${t.end_date ? ' - ' + new Date(t.end_date).toLocaleDateString() : ''}</p>
                        </div>
                        <div style="text-align:right">
                            <p style="font-size:0.55rem; font-weight:900; color:var(--text-muted);">PRESUPUESTO</p>
                            <p style="font-weight:900; font-size:1.1rem; color:var(--primary);">$${parseFloat(t.budget || 0).toLocaleString()}</p>
                        </div>
                    </div>
                </div>
            `).join('');
        } catch (e) {
            list.innerHTML = '<p style="text-align:center; color:var(--secondary); font-weight:800;">Error al cargar viajes.</p>';
        }
    }

    function filterTrips(filter) {
        currentFilter = filter;
        document.getElementById('btn-active').style.background = filter === 'active' ? 'var(--primary)' : '#f1f5f9';
        document.getElementById('btn-active').style.color = filter === 'active' ? 'white' : 'var(--text-muted)';
        document.getElementById('btn-past').style.background = filter === 'past' ? 'var(--primary)' : '#f1f5f9';
        document.getElementById('btn-past').style.color = filter === 'past' ? 'white' : 'var(--text-muted)';
        fetchTrips();
    }

    document.addEventListener('DOMContentLoaded', fetchTrips);
</script>
@endpush
