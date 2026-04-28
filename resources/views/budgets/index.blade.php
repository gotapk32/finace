@extends('layouts.app')

@section('title', 'Presupuestos')

@section('content')
<!-- RESUMEN GENERAL -->
<div class="stat-card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; padding: 1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 1rem;">
        <h3 style="font-weight: 900; margin:0;">Control Mensual 🎯</h3>
        <button onclick="openBudgetModal()" class="btn-text" style="background:rgba(255,255,255,0.2); color:white; padding:8px 15px; border-radius:12px; font-size:0.6rem; font-weight:800;">+ NUEVO LÍMITE</button>
    </div>
    
    <div style="margin-top: 1rem;">
        <div style="display:flex; justify-content:space-between; margin-bottom: 0.5rem;">
            <span style="font-size: 0.7rem; font-weight: 800;">TOTAL GASTADO</span>
            <span id="total-spent-label" style="font-weight: 900;">$0</span>
        </div>
        <div style="height: 12px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow:hidden;">
            <div id="total-budget-bar" style="height:100%; width: 0%; background: var(--primary); transition: width 1s;"></div>
        </div>
        <div style="display:flex; justify-content:space-between; margin-top: 0.5rem; font-size: 0.65rem; opacity: 0.8; font-weight: 800;">
            <span id="total-perc-label">0% DEL LÍMITE</span>
            <span id="total-limit-label">LÍMITE: $0</span>
        </div>
    </div>
</div>

<!-- LISTADO DETALLADO -->
<div id="budgets-detailed-list">
    <div class="skeleton" style="height: 120px; margin-bottom: 1rem;"></div>
    <div class="skeleton" style="height: 120px; margin-bottom: 1rem;"></div>
</div>

<!-- MODAL DE GESTIÓN -->
<div id="budget-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center; padding:20px;">
    <div class="stat-card" style="width:100%; max-width:400px; padding:2rem;">
        <h3 id="modal-title" style="font-weight:900; margin-bottom:1.5rem;">Nuevo Límite</h3>
        <form id="budget-form" onsubmit="window.saveBudgetLocal(event)">
            <input type="hidden" name="id" id="budget-id">
            <div class="form-group">
                <label>Categoría</label>
                <select name="category_id" id="category-selector-budget" required></select>
            </div>
            <div class="form-group">
                <label>Monto Límite Mensual</label>
                <input type="number" name="amount" id="budget-amount" placeholder="Ej. 2000" required>
            </div>
            <button type="submit" class="btn-primary">GUARDAR LÍMITE</button>
            <button type="button" onclick="closeBudgetModal()" style="width:100%; margin-top:10px; border:none; background:transparent; font-weight:800; color:var(--text-muted); cursor:pointer; padding:10px;">CANCELAR</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openBudgetModal(id = '', catId = '', amount = '') {
        document.getElementById('budget-id').value = id;
        document.getElementById('budget-amount').value = amount;
        if(catId) document.getElementById('category-selector-budget').value = catId;
        
        document.getElementById('modal-title').innerText = id ? 'Editar Límite' : 'Nuevo Límite';
        document.getElementById('budget-modal').style.display = 'flex';
    }

    function closeBudgetModal() {
        document.getElementById('budget-modal').style.display = 'none';
        document.getElementById('budget-form').reset();
    }

    window.saveBudgetLocal = async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.target));
        const res = await fetch('/api/budgets', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if(res.ok) {
            closeBudgetModal();
            window.fetchBudgetsDetailed();
            if(window.fetchSummary) window.fetchSummary();
        }
    };

    window.deleteBudgetLocal = async (id) => {
        if(!confirm('¿Seguro que quieres eliminar este presupuesto?')) return;
        const res = await fetch(`/api/budgets/${id}`, { method: 'DELETE' });
        if(res.ok) {
            window.fetchBudgetsDetailed();
            if(window.fetchSummary) window.fetchSummary();
        }
    };

    window.fetchBudgetsDetailed = async () => {
        try {
            const res = await fetch('/api/budgets/report');
            const data = await res.json();
            const list = document.getElementById('budgets-detailed-list');
            list.innerHTML = '';

            let totalLimit = 0;
            let totalSpent = 0;

            if (data.length === 0) {
                list.innerHTML = '<div class="stat-card" style="text-align:center; padding:3rem;"><p style="color:var(--text-muted); font-weight:700;">No tienes límites configurados.</p></div>';
                return;
            }

            data.forEach(b => {
                totalLimit += b.limit;
                totalSpent += b.spent;

                const color = b.percentage > 100 ? 'var(--secondary)' : (b.percentage > 80 ? '#f59e0b' : 'var(--accent)');
                const statusMsg = b.percentage > 100 ? '🔥 EXCEDIDO' : (b.percentage > 80 ? '⚠️ LÍMITE CRÍTICO' : '✅ CONTROLADO');
                
                const card = document.createElement('div');
                card.className = 'stat-card';
                card.style.marginBottom = '1rem';
                card.style.padding = '1.5rem';
                card.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:1rem;">
                        <div style="display:flex; gap:12px; align-items:center;">
                            <div style="font-size:2rem;">${b.category_icon || '💰'}</div>
                            <div>
                                <h4 style="font-weight:900; margin:0;">${b.category_name}</h4>
                                <span style="font-size:0.6rem; font-weight:800; color:${color}">${statusMsg}</span>
                            </div>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button onclick="openBudgetModal('${b.id}', '${b.category_id}', '${b.limit}')" style="background:#f1f5f9; border:none; width:35px; height:35px; border-radius:10px; color:var(--primary); cursor:pointer;"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteBudgetLocal(${b.id})" style="background:#fff1f2; border:none; width:35px; height:35px; border-radius:10px; color:var(--secondary); cursor:pointer;"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:end; margin-bottom:8px;">
                        <span style="font-weight:900; font-size:1.2rem;">${window.formatCurrency(b.spent)}</span>
                        <span style="font-size:0.7rem; color:var(--text-muted); font-weight:800;">META: ${window.formatCurrency(b.limit)}</span>
                    </div>

                    <div style="height:10px; background:#f1f5f9; border-radius:10px; overflow:hidden; margin-bottom:10px;">
                        <div style="height:100%; width:${Math.min(100, b.percentage)}%; background:${color}; border-radius:10px; transition: width 1s;"></div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.65rem; font-weight:800; color:var(--text-muted);">
                        <span>USADO: ${b.percentage}%</span>
                        <span style="color:${b.limit - b.spent < 0 ? 'var(--secondary)' : 'var(--accent)'}">${b.limit - b.spent < 0 ? 'SOBREGIRO: ' : 'DISPONIBLE: '} ${window.formatCurrency(Math.abs(b.limit - b.spent))}</span>
                    </div>
                    
                    <button onclick="location.href='/gastos?search=${b.category_name}'" style="width:100%; margin-top:15px; background:transparent; border:1px solid #e2e8f0; padding:8px; border-radius:10px; font-size:0.6rem; font-weight:800; color:var(--text-muted); cursor:pointer;">
                        VER MOVIMIENTOS DE ${b.category_name.toUpperCase()}
                    </button>
                `;
                list.appendChild(card);
            });

            const totalPerc = totalLimit > 0 ? Math.round((totalSpent / totalLimit) * 100) : 0;
            document.getElementById('total-spent-label').textContent = window.formatCurrency(totalSpent);
            document.getElementById('total-limit-label').textContent = 'LÍMITE TOTAL: ' + window.formatCurrency(totalLimit);
            document.getElementById('total-budget-bar').style.width = Math.min(100, totalPerc) + '%';
            document.getElementById('total-perc-label').textContent = totalPerc + '% DEL LÍMITE TOTAL';

        } catch (err) { console.error('Detailed budgets error:', err); }
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.fetchBudgetsDetailed();
        
        // Cargar selector de categorías
        fetch('/api/categories').then(r => r.json()).then(cats => {
            const sel = document.getElementById('category-selector-budget');
            let opts = '<option value="">Selecciona categoría...</option>';
            cats.forEach(c => opts += `<option value="${c.id}">${c.icon || '💰'} ${c.name}</option>`);
            sel.innerHTML = opts;
        });
    });
</script>
@endpush
