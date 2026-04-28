@extends('layouts.app')

@section('title', 'Cartera')

@section('content')
<div class="tabs" style="display: flex; gap: 10px; margin-bottom: 1.5rem; overflow-x: auto; padding-bottom: 5px;">
    <button onclick="showTab('tab-cards')" class="tab-btn active" id="btn-tab-cards">TARJETAS</button>
    <button onclick="showTab('tab-recurring')" class="tab-btn" id="btn-tab-recurring">MENSUALES</button>
    <button onclick="showTab('tab-partner')" class="tab-btn" id="btn-tab-partner">PAREJA</button>
    <button onclick="showTab('tab-categories')" class="tab-btn" id="btn-tab-categories">CATEGORÍAS</button>
    <button onclick="showTab('tab-settings')" class="tab-btn" id="btn-tab-settings">AJUSTES</button>
</div>

<!-- TAB: TARJETAS -->
<div id="tab-cards" class="tab-content">
    <div class="stat-card" style="margin-bottom: 1.5rem;">
        <h3 style="font-weight: 900; margin-bottom: 1rem;">Mis Tarjetas 💳</h3>
        <form id="card-form">
            <div class="form-group">
                <label>Nombre de la Tarjeta</label>
                <input type="text" name="name" placeholder="Ej. Visa Débito Michelle" required>
            </div>
            <div class="form-group">
                <label>Tipo</label>
                <select name="type" onchange="toggleCardDetails(this.value)">
                    <option value="debito">Débito</option>
                    <option value="credito">Crédito</option>
                    <option value="vales">Vales / Otros</option>
                </select>
            </div>
            <div id="credit-details" style="display:none; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Día de Corte</label>
                    <input type="number" name="cut_day" placeholder="Ej. 15" min="1" max="31">
                </div>
                <div class="form-group">
                    <label>Día de Pago</label>
                    <input type="number" name="payment_day" placeholder="Ej. 5" min="1" max="31">
                </div>
            </div>
            <button type="submit" class="btn-primary">AÑADIR TARJETA</button>
        </form>
    </div>
    <div id="methods-list" style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
        <!-- JS Loaded -->
    </div>
</div>

<!-- TAB: GASTOS RECURRENTES -->
<div id="tab-recurring" class="tab-content" style="display:none;">
    <div class="stat-card" style="margin-bottom: 1.5rem; border-left: 5px solid var(--primary);">
        <h3 style="font-weight: 900; margin-bottom: 1rem;">Nuevo Mensual 🔄</h3>
        <form id="recurring-form-wallet">
            <input type="hidden" name="id" id="rec-id">
            <div class="form-group">
                <label>Concepto</label>
                <input type="text" name="name" id="rec-name" placeholder="Ej. Netflix, Renta..." required>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                <div class="form-group">
                    <label>Monto</label>
                    <input type="number" name="amount" id="rec-amount" required>
                </div>
                <div class="form-group">
                    <label>Día de Cobro</label>
                    <input type="number" name="due_day" id="rec-day" min="1" max="31" required>
                </div>
            </div>
            <div class="form-group">
                <label>Categoría</label>
                <select name="category_id" class="category-selector-all" required></select>
            </div>
            <button type="submit" class="btn-primary">GUARDAR RECURRENTE</button>
            <button type="button" id="cancel-rec-edit" style="display:none; width:100%; margin-top:10px; border:none; background:transparent; font-weight:800; color:var(--secondary);" onclick="cancelRecEdit()">CANCELAR EDICIÓN</button>
        </form>
    </div>
    <div id="monthly-list">
        <!-- JS Loaded -->
    </div>
</div>

<!-- TAB: PAREJA -->
<div id="tab-partner" class="tab-content" style="display:none;">
    <div class="stat-card" style="text-align: center; padding: 2rem;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">💍</div>
        @if(Auth::user()->partner)
            <h3 style="font-weight: 900;">Vinculado con {{ Auth::user()->partner->name }}</h3>
            <p style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 2rem;">Compartiendo gastos al 50/50</p>
            <form action="{{ route('unlink-partner') }}" method="POST">
                @csrf
                <button class="btn-primary" style="background: var(--secondary);">DESVINCULAR CUENTA</button>
            </form>
        @else
            <h3 style="font-weight: 900;">Vincular Pareja</h3>
            <p style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 2rem;">Ingresa el correo de tu pareja para compartir gastos.</p>
            <form action="{{ route('link-partner') }}" method="POST">
                @csrf
                <div class="form-group">
                    <input type="email" name="partner_email" placeholder="correo@ejemplo.com" required>
                </div>
                <button class="btn-primary">ENVIAR INVITACIÓN</button>
            </form>

            <div id="pending-invitations-list" style="margin-top: 2rem; text-align: left;">
                <!-- Cargado por JS -->
            </div>
        @endif
    </div>
</div>

<!-- TAB: CATEGORÍAS -->
<div id="tab-categories" class="tab-content" style="display:none;">
    <div class="stat-card" style="margin-bottom: 1.5rem;">
        <h3 style="font-weight: 900; margin-bottom: 1rem;">Categorías 🏷️</h3>
        <form id="cat-form">
            <input type="hidden" name="id" id="cat-id">
            <div style="display:grid; grid-template-columns: 80px 1fr; gap:10px;">
                <div class="form-group">
                    <label>Icono</label>
                    <input type="text" name="icon" id="cat-icon" placeholder="🍔" required maxlength="2" style="text-align:center; font-size:1.5rem; cursor:pointer;" readonly onclick="document.getElementById('emoji-picker').style.display='grid'">
                </div>
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="name" id="cat-name" placeholder="Ej. Restaurantes" required>
                </div>
            </div>
            
            <div id="emoji-picker" style="display:none; grid-template-columns: repeat(8, 1fr); gap: 5px; margin-bottom: 1rem; padding: 10px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                @foreach(['💰','🍔','🛒','🚗','🏠','💡','💊','🎮','👗','✈️','🏫','🐶','❤️','🛠️','📱','🧼'] as $emoji)
                    <span onclick="selectEmoji('{{ $emoji }}')" style="font-size: 1.2rem; cursor: pointer; text-align: center; padding: 5px; border-radius: 8px; transition: 0.2s;" onmouseover="this.style.background='white'" onmouseout="this.style.background='transparent'">{{ $emoji }}</span>
                @endforeach
            </div>
            <button type="submit" class="btn-primary">GUARDAR CATEGORÍA</button>
        </form>
    </div>
    <div id="categories-list">
        <!-- JS Loaded -->
    </div>
</div>

<!-- TAB: AJUSTES -->
<div id="tab-settings" class="tab-content" style="display:none;">
    @include('settings_content')
</div>

<style>
    .tab-btn { 
        padding: 0.6rem 1.2rem; border: none; background: var(--card-bg); border-radius: 12px; 
        font-weight: 800; font-size: 0.6rem; color: var(--text-muted); cursor: pointer; transition: 0.3s;
        white-space: nowrap; border: 1px solid transparent;
    }
    .tab-btn.active { background: var(--primary); color: white; border-color: var(--primary); box-shadow: 0 5px 15px rgba(99,102,241,0.3); }
    .card-mockup { background: var(--primary); color: white; padding: 1.5rem; border-radius: 20px; position: relative; overflow: hidden; }
    .card-mockup h4 { font-weight: 900; }
    .card-mockup .type { font-size: 0.5rem; text-transform: uppercase; opacity: 0.8; font-weight: 900; }
</style>

@endsection

@push('scripts')
<script>
    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(t => {
            t.style.display = 'none';
            t.classList.remove('active');
        });
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        
        const selectedTab = document.getElementById(tabId);
        if (selectedTab) {
            selectedTab.style.display = 'block';
            selectedTab.classList.add('active');
        }
        
        const selectedBtn = document.getElementById('btn-' + tabId);
        if (selectedBtn) {
            selectedBtn.classList.add('active');
        }
    }

    function toggleCardDetails(type) {
        document.getElementById('credit-details').style.display = (type === 'credito') ? 'grid' : 'none';
    }

    function selectEmoji(emoji) {
        document.getElementById('cat-icon').value = emoji;
        document.getElementById('emoji-picker').style.display = 'none';
    }

    // Manejo de Recurrentes en Wallet
    document.getElementById('recurring-form-wallet').onsubmit = async (e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.target));
        data.is_recurring = true;
        const res = await fetch(data.id ? `/api/expenses/${data.id}` : '/api/expenses', {
            method: data.id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if(res.ok) {
            e.target.reset();
            cancelRecEdit();
            window.fetchMonthly();
            window.showToast('Mensual guardado correctamente');
        }
    };

    window.editMonthly = (jsonStr) => {
        const exp = JSON.parse(decodeURIComponent(jsonStr));
        document.getElementById('rec-id').value = exp.id;
        document.getElementById('rec-name').value = exp.name;
        document.getElementById('rec-amount').value = exp.amount;
        document.getElementById('rec-day').value = exp.due_day;
        document.getElementById('cancel-rec-edit').style.display = 'block';
        showTab('tab-recurring');
    };

    function cancelRecEdit() {
        document.getElementById('rec-id').value = '';
        document.getElementById('recurring-form-wallet').reset();
        document.getElementById('cancel-rec-edit').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', () => {
        showTab('tab-cards');
        window.fetchMethods();
        window.fetchCategories();
        window.fetchMonthly();
        window.fetchSummary(); // <-- Esta línea es la que faltaba para cargar invitaciones
    });
</script>
@endpush
