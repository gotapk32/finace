@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div id="reminders-area"></div>

<!-- PRESUPUESTO GENERAL DEL MES -->
<div id="general-budget-card" class="stat-card" style="margin-bottom: 2rem; padding: 1.5rem; display:none;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <div>
            <h4 style="font-weight: 900; font-size: 0.9rem; margin:0;">Presupuesto General</h4>
            <p style="font-size: 0.6rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Control de Gastos del Mes</p>
        </div>
        <div id="budget-percentage" style="font-weight: 900; font-size: 1.2rem; color: var(--primary);">0%</div>
    </div>
    
    <div style="height: 12px; background: #f1f5f9; border-radius: 10px; overflow: hidden; margin-bottom: 0.8rem;">
        <div id="budget-progress-bar" style="height: 100%; width: 0%; background: var(--primary); transition: width 1s ease-out;"></div>
    </div>

    <div style="display:flex; justify-content:space-between; font-weight: 800; font-size: 0.7rem;">
        <span id="budget-spent" style="color: var(--text-main);">$0</span>
        <span id="budget-limit" style="color: var(--text-muted);">Límite: $0</span>
    </div>
</div>

<div class="summary-section" style="margin-bottom: 2rem;">
    <!-- TARJETAS DE HOY Y MES -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
        <div class="stat-card" style="padding: 1.5rem; border-top: 4px solid var(--primary);">
            <p style="font-size: 0.6rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Gastado Hoy</p>
            <h2 id="total-day" style="font-size: 1.8rem; font-weight: 900; margin: 5px 0; color: var(--primary);">$0</h2>
        </div>
        <div class="stat-card" style="padding: 1.5rem; border-top: 4px solid #0f172a;">
            <p style="font-size: 0.6rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Total del Mes</p>
            <h2 id="total-month" style="font-size: 1.8rem; font-weight: 900; margin: 5px 0; color: var(--text-main);">$0</h2>
        </div>
    </div>

    <!-- DESGLOSE TIPO GASTO -->
    <div class="stat-card" style="margin-bottom: 1.5rem; padding: 1.5rem;">
        <h4 style="font-size: 0.7rem; font-weight: 900; color: var(--text-muted); margin-bottom: 1.2rem; text-transform: uppercase;">Detalle de Gastos</h4>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 800; font-size: 0.85rem;"><i class="fas fa-users" style="color: var(--primary); margin-right: 8px;"></i> COMPARTIDO</span>
                <span id="total-shared" style="font-weight: 900; color: var(--text-main);">$0</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 800; font-size: 0.85rem;"><i class="fas fa-user-lock" style="color: var(--secondary); margin-right: 8px;"></i> PRIVADO</span>
                <span id="total-personal" style="font-weight: 900; color: var(--text-main);">$0</span>
            </div>
        </div>
    </div>

    <!-- BALANCE DE DEUDAS -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
        <div class="stat-card" style="border-left: 4px solid var(--accent); padding: 1.2rem;">
            <p style="font-size: 0.55rem; font-weight: 800; color: var(--text-muted);">ME DEBEN ⬇️</p>
            <h4 id="total-me-deben" style="font-weight: 900; color: var(--accent); margin: 5px 0;">$0</h4>
        </div>
        <div class="stat-card" style="border-left: 4px solid var(--secondary); padding: 1.2rem;">
            <p style="font-size: 0.55rem; font-weight: 800; color: var(--text-muted);">YO DEBO ⬆️</p>
            <h4 id="total-yo-debo" style="font-weight: 900; color: var(--secondary); margin: 5px 0;">$0</h4>
        </div>
    </div>

    <!-- LIQUIDACIÓN DE CUENTAS (Michelle vs Omer) -->
    <div class="stat-card" style="margin-bottom: 1.5rem; background: #fafafa; border: 1px dashed #cbd5e1; padding: 1.5rem; text-align: center;">
        <h4 style="font-size: 0.6rem; font-weight: 900; color: var(--text-muted); margin-bottom: 1rem; text-transform: uppercase;">Liquidación Michelle vs Omer</h4>
        <div id="settlement-msg" style="font-weight: 800; font-size: 0.9rem; color: var(--text-main);">Calculando balance...</div>
        <div id="settlement-balance" style="font-size: 1.5rem; font-weight: 900; margin: 10px 0; color: var(--primary);">$0</div>
        <p style="font-size: 0.6rem; color: var(--text-muted); font-weight: 700;">Basado en gastos compartidos del mes</p>
    </div>

    <!-- BOTONES DE ACCIÓN RÁPIDA -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
        <div class="action-card" onclick="location.href='{{ route('expenses.create') }}?mode=shared'" style="padding: 1.2rem;">
            <i class="fas fa-users" style="font-size: 1.2rem; color: var(--primary);"></i>
            <span style="font-weight: 900; font-size: 0.6rem;">COMPARTIDO</span>
        </div>
        <div class="action-card" onclick="location.href='{{ route('expenses.create') }}?mode=personal'" style="padding: 1.2rem;">
            <i class="fas fa-user-lock" style="font-size: 1.2rem; color: var(--secondary);"></i>
            <span style="font-weight: 900; font-size: 0.6rem;">PERSONAL</span>
        </div>
    </div>

    <!-- PRESUPUESTOS -->
    <div class="stat-card" style="margin-bottom: 1.5rem; padding: 1.5rem;">
        <h4 style="font-size: 0.7rem; font-weight: 900; color: var(--text-muted); margin-bottom: 1.2rem; text-transform: uppercase;">Límites de Gastos</h4>
        <div id="budget-area">
            <!-- Cargado vía JS -->
        </div>
    </div>

    <!-- GRÁFICAS -->
    <div class="chart-container" style="background: white; border-radius: 24px; padding: 1.5rem; box-shadow: var(--shadow); margin-bottom: 1.5rem;">
        <h4 style="font-weight: 900; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 1.5rem; text-transform: uppercase;">REPARTO POR CATEGORÍA</h4>
        <div style="height: 250px;"><canvas id="categoryChart"></canvas></div>
    </div>

    <div class="chart-container" style="background: white; border-radius: 24px; padding: 1.5rem; box-shadow: var(--shadow); margin-bottom: 1.5rem;">
        <h4 style="font-weight: 900; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 1.5rem; text-transform: uppercase;">TENDENCIA DE GASTOS</h4>
        <div style="height: 250px;"><canvas id="trendChart"></canvas></div>
    </div>

    <div class="chart-container" style="background: white; border-radius: 24px; padding: 1.5rem; box-shadow: var(--shadow);">
        <h4 style="font-weight: 900; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 1.5rem; text-transform: uppercase;">REPARTO POR PERSONA</h4>
        <div style="height: 250px;"><canvas id="payerChart"></canvas></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.fetchSummary();
    });
</script>
@endpush
