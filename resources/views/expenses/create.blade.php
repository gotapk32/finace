@extends('layouts.app')

@section('title', 'Nuevo Gasto')

@section('content')
<div id="step-1">
    <h2 style="font-weight: 900; margin-bottom: 2rem; text-align: center;">¿Qué tipo de gasto es?</h2>
    
    <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
        <button onclick="selectType('shared')" class="type-card">
            <div class="icon" style="background: var(--primary-glow); color: var(--primary);"><i class="fas fa-users"></i></div>
            <div class="info">
                <h3>Gasto Compartido</h3>
                <p>Se divide 50/50 con tu pareja automáticamente.</p>
            </div>
            <i class="fas fa-chevron-right"></i>
        </button>

        <button onclick="selectType('personal')" class="type-card">
            <div class="icon" style="background: #fdf2f2; color: var(--secondary);"><i class="fas fa-user-lock"></i></div>
            <div class="info">
                <h3>Gasto Personal</h3>
                <p>Solo tú lo ves. No afecta el balance compartido.</p>
            </div>
            <i class="fas fa-chevron-right"></i>
        </button>

        <button onclick="selectType('debt')" class="type-card">
            <div class="icon" style="background: #f0fdf4; color: var(--accent);"><i class="fas fa-hand-holding-usd"></i></div>
            <div class="info">
                <h3>Préstamo o Deuda</h3>
                <p>Alguien debe dinero. Tú a ellos o ellos a ti.</p>
            </div>
            <i class="fas fa-chevron-right"></i>
        </button>

        <button onclick="selectType('recurring')" class="type-card" style="border-color: #e0e7ff;">
            <div class="icon" style="background: #eef2ff; color: #4338ca;"><i class="fas fa-redo"></i></div>
            <div class="info">
                <h3>Gasto Recurrente</h3>
                <p>Suscripciones, renta o pagos fijos mensuales.</p>
            </div>
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<div id="step-2" style="display: none;">
    <button onclick="goBack()" class="btn-back" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px; background: none; border: none; color: var(--text-muted); font-weight: 800; font-size: 0.75rem; cursor: pointer; transition: 0.3s; padding: 0;">
        <div style="width: 32px; height: 32px; border-radius: 50%; background: white; box-shadow: var(--shadow); display: flex; align-items: center; justify-content: center; color: var(--primary);">
            <i class="fas fa-arrow-left"></i>
        </div>
        VOLVER
    </button>
    <div class="stat-card">
        <h3 id="form-title" style="font-weight: 900; margin-bottom: 1.5rem;">Detalles del Gasto</h3>
        
        <form id="expense-form" enctype="multipart/form-data">
            <input type="hidden" name="is_personal" id="is_personal" value="0">
            <input type="hidden" name="type" id="main_type" value="gasto">
            <input type="hidden" name="is_recurring" id="is_recurring" value="0">
            <input type="hidden" name="id" id="edit_id" value="">

            <div id="debt-direction-area" style="display: none; margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 800; font-size: 0.65rem; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase;">Sentido de la Deuda</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <label class="toggle-btn">
                        <input type="radio" name="debt_direction" value="to_me" checked>
                        <span>ME DEBEN</span>
                    </label>
                    <label class="toggle-btn">
                        <input type="radio" name="debt_direction" value="to_them">
                        <span>YO DEBO</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Concepto</label>
                <input type="text" name="name" id="f-name" placeholder="Ej. Cena Sushi, Netflix..." required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="form-group">
                    <label>Monto</label>
                    <input type="number" name="amount" id="f-amount" step="0.01" required>
                </div>
                <div class="form-group" id="date-group">
                    <label>Fecha</label>
                    <input type="date" name="date" id="f-date">
                </div>
                <div class="form-group" id="day-group" style="display:none;">
                    <label>Día de Cobro</label>
                    <input type="number" name="due_day" id="f-day" min="1" max="31" placeholder="1-31">
                </div>
            </div>

            <div class="form-group">
                <label>Categoría</label>
                <select name="category_id" id="category-selector" required></select>
            </div>

            <div class="form-group" id="payer-group">
                <label>¿Quién pagó?</label>
                <select name="payer" id="payer-select"></select>
            </div>

            <div class="form-group">
                <label>Método de Pago</label>
                <select name="payment_method_id" class="method-selector"></select>
            </div>

            <div class="form-group" id="trip-group">
                <label>Vincular a un Viaje (Opcional)</label>
                <select name="trip_id" id="trip-selector">
                    <option value="">Ninguno</option>
                </select>
            </div>

            <div class="form-group" id="photo-group">
                <label>Foto del Recibo (Opcional)</label>
                <div class="file-input-wrapper" id="preview-container">
                    <div id="preview-placeholder">
                        <i class="fas fa-camera"></i>
                        <span>SUBIR FOTO</span>
                    </div>
                    <img id="image-preview" style="display:none; width:100%; height:150px; object-fit:cover; border-radius:12px;">
                    <input type="file" name="image" accept="image/*" onchange="previewImage(event)">
                </div>
                <button type="button" id="remove-img" onclick="clearImage()" style="display:none; background:none; border:none; color:var(--secondary); font-size:0.6rem; font-weight:900; margin-top:5px; cursor:pointer;">QUITAR FOTO</button>
            </div>

            <button type="submit" id="btn-submit" class="btn-primary" style="margin-top: 1rem; display: flex; align-items: center; justify-content: center; gap: 10px;">
                <span id="btn-text">GUARDAR REGISTRO</span>
                <i id="btn-loader" class="fas fa-spinner fa-spin" style="display:none;"></i>
            </button>
        </form>
    </div>
</div>

<style>
    .type-card {
        background: var(--card-bg); border: 2px solid transparent; border-radius: 20px; padding: 1.2rem;
        display: flex; align-items: center; gap: 1.2rem; width: 100%; cursor: pointer; text-align: left;
        transition: 0.2s; box-shadow: var(--shadow);
    }
    .type-card:active { transform: scale(0.98); }
    .type-card .icon { width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .type-card .info { flex: 1; }
    .type-card h3 { font-size: 0.95rem; font-weight: 900; margin-bottom: 2px; }
    .type-card p { font-size: 0.7rem; color: var(--text-muted); font-weight: 600; }
    .type-card i.fa-chevron-right { color: #cbd5e1; font-size: 0.8rem; }

    .toggle-btn input { display: none; }
    .toggle-btn span { 
        display: block; padding: 12px; text-align: center; background: #f8fafc; border-radius: 12px;
        font-size: 0.65rem; font-weight: 900; color: var(--text-muted); border: 2px solid transparent; cursor: pointer;
    }
    .toggle-btn input:checked + span { background: white; border-color: var(--primary); color: var(--primary); box-shadow: 0 5px 10px rgba(99,102,241,0.1); }

    .file-input-wrapper {
        position: relative; background: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 16px;
        padding: 2rem; text-align: center; color: var(--text-muted); font-weight: 800; font-size: 0.7rem;
    }
    .file-input-wrapper input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .file-input-wrapper i { font-size: 1.5rem; display: block; margin-bottom: 8px; color: var(--primary); }
</style>

@endsection

@push('scripts')
<script>
    function selectType(type) {
        const step1 = document.getElementById('step-1');
        const step2 = document.getElementById('step-2');
        const isPersonal = document.getElementById('is_personal');
        const mainType = document.getElementById('main_type');
        const isRecurring = document.getElementById('is_recurring');
        const debtArea = document.getElementById('debt-direction-area');
        const dateGroup = document.getElementById('date-group');
        const dayGroup = document.getElementById('day-group');
        const payerGroup = document.getElementById('payer-group');
        const title = document.getElementById('form-title');

        // Reset
        isPersonal.value = "0";
        mainType.value = "gasto";
        isRecurring.value = "0";
        debtArea.style.display = 'none';
        dateGroup.style.display = 'block';
        dayGroup.style.display = 'none';
        payerGroup.style.display = 'block';

        if (type === 'shared') {
            title.innerText = 'Gasto Compartido 👥';
        } else if (type === 'personal') {
            isPersonal.value = "1";
            payerGroup.style.display = 'none'; // <-- Hiding for personal
            title.innerText = 'Gasto Personal 🔒';
        } else if (type === 'debt') {
            mainType.value = "deuda";
            debtArea.style.display = 'block';
            title.innerText = 'Préstamo o Deuda 💸';
        } else if (type === 'recurring') {
            isRecurring.value = "1";
            dateGroup.style.display = 'none';
            dayGroup.style.display = 'block';
            payerGroup.style.display = 'none';
            title.innerText = 'Gasto Recurrente 🔄';
        }

        step1.style.display = 'none';
        step2.style.display = 'block';
    }

    function goBack() {
        document.getElementById('step-1').style.display = 'block';
        document.getElementById('step-2').style.display = 'none';
    }

    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('image-preview');
            const placeholder = document.getElementById('preview-placeholder');
            const removeBtn = document.getElementById('remove-img');
            output.src = reader.result;
            output.style.display = 'block';
            placeholder.style.display = 'none';
            removeBtn.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    function clearImage() {
        const input = document.querySelector('input[name="image"]');
        const output = document.getElementById('image-preview');
        const placeholder = document.getElementById('preview-placeholder');
        const removeBtn = document.getElementById('remove-img');
        input.value = "";
        output.style.display = 'none';
        placeholder.style.display = 'block';
        removeBtn.style.display = 'none';
    }

    // Editar si viene ID en la URL
    document.addEventListener('DOMContentLoaded', async () => {
        const params = new URLSearchParams(window.location.search);
        const editId = params.get('id');
        
        await window.fetchCategories();
        await window.fetchMethods();
        await window.fetchSummary(); // Para los pagadores

        // Cargar viajes activos (disponible tanto para nuevo como para editar)
        try {
            const tripsRes = await fetch('/api/trips');
            const trips = await tripsRes.json();
            const tripSelector = document.getElementById('trip-selector');
            const activeTrips = trips.filter(t => t.is_active);
            activeTrips.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.name;
                tripSelector.appendChild(opt);
            });

            // Si viene trip_id por URL, seleccionarlo
            const tripParam = params.get('trip_id');
            if (tripParam) {
                tripSelector.value = tripParam;
            }
        } catch (e) {
            console.error("Error cargando viajes:", e);
        }

        if (editId) {
            // Lógica de carga para edición
            const res = await fetch('/api/expenses');
            const expenses = await res.json();
            const exp = expenses.find(e => e.id == editId);
            if (exp) {
                document.getElementById('edit_id').value = exp.id;
                document.getElementById('f-name').value = exp.name;
                document.getElementById('f-amount').value = exp.amount;
                document.getElementById('f-date').value = exp.date;
                document.getElementById('category-selector').value = exp.category_id;
                document.getElementById('payer-select').value = exp.payer;
                
                const tripIdFromExp = exp.trip_id;
                if (tripIdFromExp) document.getElementById('trip-selector').value = tripIdFromExp;

                selectType(exp.is_recurring ? 'recurring' : (exp.type === 'deuda' ? 'debt' : (exp.is_personal ? 'personal' : 'shared')));
            }
        } else {
            // Nuevo registro
            const today = new Date();
            const offset = today.getTimezoneOffset();
            const localDate = new Date(today.getTime() - (offset * 60 * 1000)).toISOString().split('T')[0];
            document.getElementById('f-date').value = localDate;
            document.getElementById('f-date').setAttribute('max', localDate);

            // Manejo de modo inicial
            const mode = params.get('mode');
            if (mode) selectType(mode);
        }
    });

    document.getElementById('expense-form').onsubmit = async (e) => {
        e.preventDefault();
        
        const btn = document.getElementById('btn-submit');
        const text = document.getElementById('btn-text');
        const loader = document.getElementById('btn-loader');

        // Estado de carga
        btn.disabled = true;
        text.innerText = "GUARDANDO...";
        loader.style.display = "block";

        const fData = new FormData(e.target);
        const id = fData.get('id');
        const method = id ? 'PUT' : 'POST';
        const url = id ? `/api/expenses/${id}` : '/api/expenses';

        if (id) {
            fData.append('_method', 'PUT');
        }

        try {
            const res = await fetch(url, {
                method: 'POST',
                body: fData
            });

            if (res.ok) {
                window.showToast('Gasto guardado con éxito');
                location.href = "/gastos";
            } else {
                window.showToast('Error al guardar', 'error');
                btn.disabled = false;
                text.innerText = "GUARDAR REGISTRO";
                loader.style.display = "none";
            }
        } catch (err) {
            window.showToast('Error de conexión', 'error');
            btn.disabled = false;
            text.innerText = "GUARDAR REGISTRO";
            loader.style.display = "none";
        }
    };
</script>
@endpush
