<div class="stat-card">
    <h3 style="margin-bottom: 1.5rem; font-weight: 900;">Mi Perfil</h3>
    
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; padding: 1rem; background: #f1f5f9; border-radius: 15px;">
        <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: 900;">
            {{ substr(Auth::user()->name, 0, 1) }}
        </div>
        <div>
            <h4 style="font-weight: 900;">{{ Auth::user()->name }}</h4>
            <p style="font-size: 0.7rem; color: var(--text-muted);">{{ Auth::user()->email }}</p>
        </div>
    </div>

    <h4 style="font-size: 0.7rem; font-weight: 900; color: var(--text-muted); margin-bottom: 1.2rem; text-transform: uppercase;">Configuración de Ingresos</h4>
    <form action="{{ route('update-salary') }}" method="POST" style="margin-bottom: 2rem; background: #fafafa; padding: 1.5rem; border-radius: 20px; border: 1px solid #f1f5f9;">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Mi Sueldo</label>
                <input type="number" name="salary" value="{{ Auth::user()->salary }}" placeholder="Ej. 15000" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Frecuencia</label>
                <select name="salary_period">
                    <option value="semanal" {{ Auth::user()->salary_period == 'semanal' ? 'selected' : '' }}>Semanal</option>
                    <option value="quincenal" {{ Auth::user()->salary_period == 'quincenal' ? 'selected' : '' }}>Quincenal</option>
                    <option value="mensual" {{ Auth::user()->salary_period == 'mensual' ? 'selected' : '' }}>Mensual</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn-primary" style="padding: 0.8rem; background: var(--accent);">GUARDAR INGRESOS</button>
    </form>

    <h4 style="font-size: 0.7rem; font-weight: 900; color: var(--text-muted); margin-bottom: 1.2rem; text-transform: uppercase;">Compartir Gastos</h4>
    
    @if(Auth::user()->partner_id)
        <div class="stat-card" style="background: var(--primary-glow); border: 1px solid var(--primary); margin-bottom: 1.5rem; position: relative;">
            <p style="font-size: 0.6rem; color: var(--primary); font-weight: 800;">VINCULADO CON</p>
            <h4 style="font-weight: 900;">{{ Auth::user()->partner->name }}</h4>
            <p style="font-size: 0.7rem; margin-top: 5px; color: var(--text-muted);">Ahora comparten gastos y presupuestos.</p>
            
            <form action="{{ route('unlink-partner') }}" method="POST" onsubmit="return confirm('¿Seguro que quieres desvincular la cuenta? Se dejarán de compartir gastos.')" style="margin-top: 15px;">
                @csrf
                <button type="submit" class="btn-text" style="color: var(--secondary); font-weight: 800; font-size: 0.65rem; border: 1px solid var(--secondary); padding: 5px 10px; border-radius: 8px;">DESVINCULAR PAREJA</button>
            </form>
        </div>
    @else
        <form action="{{ route('link-partner') }}" method="POST" style="margin-bottom: 2rem;">
            @csrf
            <div class="form-group">
                <label>Vincular con pareja (Email)</label>
                <input type="email" name="partner_email" placeholder="email@ejemplo.com" required>
                <p style="font-size: 0.6rem; color: var(--text-muted); margin-top: 5px;">Tu pareja ya debe estar registrada en la app.</p>
            </div>
            <button type="submit" class="btn-primary" style="padding: 0.8rem;">VINCULAR AHORA</button>
        </form>
    @endif

    <h4 style="font-size: 0.7rem; font-weight: 900; color: var(--text-muted); margin-bottom: 1.2rem; text-transform: uppercase;">App y Notificaciones</h4>
    
    <div id="install-pwa" class="action-card" onclick="window.installApp()" style="display: none; align-items: center; justify-content: space-between; padding: 1rem; cursor: pointer; background: var(--primary-glow); border: 1px solid var(--primary); margin-bottom: 10px;">
        <span style="font-weight: 800; color: var(--primary);">Instalar App en el Celular</span>
        <i class="fas fa-mobile-alt" style="font-size: 1.2rem; color: var(--primary);"></i>
    </div>

    <div class="action-card" onclick="window.requestNotifications()" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; cursor: pointer; margin-bottom: 10px;">
        <span style="font-weight: 800;">Activar Notificaciones Push</span>
        <i class="fas fa-bell" style="font-size: 1.2rem;"></i>
    </div>

    <div class="action-card" onclick="window.toggleDarkMode()" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; cursor: pointer;">
        <span style="font-weight: 800;">Cambiar Modo (Oscuro/Claro)</span>
        <i class="fas fa-adjust" style="font-size: 1.2rem;"></i>
    </div>
</div>
