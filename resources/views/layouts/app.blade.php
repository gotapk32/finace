<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>M&O Finance - @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="{{ asset('images/logo_mo.png') }}">
    <meta name="theme-color" content="#6366f1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.15);
            --secondary: #f43f5e;
            --accent: #10b981;
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        body.dark-mode {
            --bg-main: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg-main); color: var(--text-main); line-height: 1.5; padding-bottom: 90px; transition: background 0.3s; }
        
        .header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; position: sticky; top: 0; background: var(--bg-main); z-index: 100; }
        .logo { font-weight: 900; font-size: 1.4rem; color: var(--primary); letter-spacing: -1px; }

        .container { padding: 0 1.2rem; max-width: 600px; margin: 0 auto; }

        .stat-card { background: var(--card-bg); border-radius: 24px; padding: 1.2rem; box-shadow: var(--shadow); transition: transform 0.2s; }
        .stat-card:active { transform: scale(0.98); }

        .btn-primary { 
            background: var(--primary); color: white; border: none; padding: 1.2rem; border-radius: 18px; 
            font-weight: 900; font-size: 0.9rem; width: 100%; cursor: pointer; box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
            display: flex; align-items: center; justify-content: center;
        }

        .bottom-nav { 
            position: fixed; bottom: 0; left: 0; right: 0; background: var(--card-bg); 
            display: flex; justify-content: space-around; align-items: center; padding: 0.8rem 0.5rem;
            border-top: 1px solid rgba(0,0,0,0.05); box-shadow: 0 -5px 20px rgba(0,0,0,0.03); z-index: 1000;
        }
        .nav-item { display: flex; flex-direction: column; align-items: center; color: var(--text-muted); text-decoration: none; flex: 1; transition: 0.2s; }
        .nav-item i { font-size: 1.2rem; margin-bottom: 4px; }
        .nav-item span { font-size: 0.55rem; font-weight: 800; text-transform: uppercase; }
        .nav-item.active { color: var(--primary); }

        .nav-center {
            width: 55px; height: 55px; background: var(--primary); color: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.4); transform: translateY(-15px);
        }

        .skeleton { background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%); background-size: 200% 100%; animation: loading 1.5s infinite; border-radius: 10px; }
        @keyframes loading { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

        /* Form styling */
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; font-weight: 800; font-size: 0.65rem; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; }
        .form-group input, .form-group select { 
            width: 100%; padding: 1rem; border-radius: 16px; border: 1px solid #e2e8f0; 
            background: #f8fafc; font-family: inherit; font-weight: 600; font-size: 0.9rem;
        }
        /* Install Banner */
        #install-banner {
            background: linear-gradient(135deg, var(--primary), #4f46e5);
            color: white; padding: 12px 20px; display: none;
            position: sticky; top: 0; z-index: 2000;
            justify-content: space-between; align-items: center;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }
        #install-banner p { font-size: 0.75rem; font-weight: 800; margin: 0; }
        #install-banner button { 
            background: white; color: var(--primary); border: none; padding: 6px 15px; 
            border-radius: 10px; font-weight: 900; font-size: 0.65rem; cursor: pointer;
        }
    </style>
</head>
<body>
    <div id="install-banner">
        <p><i class="fas fa-mobile-alt"></i> Instala la App para una mejor experiencia</p>
        <button id="install-btn">INSTALAR</button>
    </div>
    <header class="header">
        <div style="display:flex; align-items:center; gap:10px;">
            <img src="{{ asset('images/logo_mo.png') }}" alt="M&O" style="width: 35px; height: 35px; border-radius: 8px;">
            <div class="logo">M&O<span style="color:var(--secondary)">.</span></div>
        </div>
        <div style="display:flex; align-items:center; gap:15px;">
            <div style="position:relative; cursor:pointer;" onclick="window.toggleNotifications()">
                <i class="fas fa-bell" style="font-size:1.2rem; color:var(--text-main);"></i>
                <span id="notif-badge" style="display:none; position:absolute; top:-5px; right:-5px; width:10px; height:10px; background:var(--secondary); border-radius:50%; border:2px solid var(--bg-main);"></span>
            </div>
            <i class="fas fa-moon" onclick="window.toggleDarkMode()" style="cursor:pointer;"></i>
            <div style="text-align:right">
                <p style="font-size:0.7rem; font-weight:800; color:var(--text-muted)">
                    @if(Auth::user()->is_admin)
                        <a href="{{ route('admin.index') }}" style="color:var(--primary); text-decoration:none; margin-right:10px;">ADMIN ⚡</a>
                    @endif
                    Hola, {{ Auth::user()->name }}
                </p>
                <form action="{{ route('logout') }}" method="POST">@csrf<button style="border:none; background:transparent; color:var(--secondary); font-weight:900; font-size:0.6rem; cursor:pointer;">CERRAR SESIÓN</button></form>
            </div>
        </div>
    </header>

    <main class="container">
        @if ($errors->any())
            <div style="background:#fff1f2; color:#f43f5e; padding:1rem; border-radius:15px; margin-bottom:1.5rem; font-size:0.75rem; font-weight:800;">
                @foreach ($errors->all() as $error)
                    <p>⚠️ {{ $error }}</p>
                @endforeach
            </div>
        @endif
        @if (session('status'))
            <div style="background:#f0fdf4; color:#10b981; padding:1rem; border-radius:15px; margin-bottom:1.5rem; font-size:0.75rem; font-weight:800;">
                ✅ {{ session('status') }}
            </div>
        @endif
        @yield('content')
    </main>

    <!-- MODAL DETALLE DE GASTO -->
    <div id="detail-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:2000; align-items:flex-end; justify-content:center; backdrop-filter: blur(5px);">
        <div class="stat-card" style="width:100%; max-width:500px; border-radius:30px 30px 0 0; padding:2rem; animation: slideUp 0.3s ease-out; position:relative;">
            <div style="width:50px; height:5px; background:#e2e8f0; border-radius:10px; margin:0 auto 1.5rem;"></div>
            
            <div style="text-align:center; margin-bottom:2rem;">
                <div id="detail-icon" style="font-size:3rem; margin-bottom:10px;">💰</div>
                <h2 id="detail-name" style="font-weight:900; font-size:1.4rem;">Nombre del Gasto</h2>
                <p id="detail-date" style="font-size:0.75rem; color:var(--text-muted); font-weight:800; text-transform:uppercase;">25 ABR • 10:30 AM</p>
            </div>

            <div style="background:#f8fafc; border-radius:20px; padding:1.5rem; margin-bottom:1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; border-bottom:1px solid #e2e8f0; padding-bottom:10px;">
                    <span style="font-size:0.7rem; font-weight:800; color:var(--text-muted);">MONTO</span>
                    <span id="detail-amount" style="font-weight:900; font-size:1.5rem; color:var(--text-main);">$0</span>
                </div>
                <div id="detail-perc-salary" style="display:none; text-align:right; font-size:0.6rem; color:var(--text-muted); font-weight:800; margin-top:-8px; margin-bottom:10px;"></div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                    <div>
                        <p style="font-size:0.55rem; font-weight:900; color:var(--text-muted);">PAGADOR</p>
                        <p id="detail-payer" style="font-weight:800; font-size:0.85rem;">Michelle</p>
                    </div>
                    <div>
                        <p style="font-size:0.55rem; font-weight:900; color:var(--text-muted);">MÉTODO</p>
                        <p id="detail-method" style="font-weight:800; font-size:0.85rem;">Efectivo</p>
                    </div>
                    <div>
                        <p style="font-size:0.55rem; font-weight:900; color:var(--text-muted);">CATEGORÍA</p>
                        <p id="detail-category" style="font-weight:800; font-size:0.85rem;">Supermercado</p>
                    </div>
                    <div>
                        <p style="font-size:0.55rem; font-weight:900; color:var(--text-muted);">TIPO</p>
                        <p id="detail-type" style="font-weight:800; font-size:0.85rem;">Compartido</p>
                    </div>
                </div>
            </div>

            <div id="detail-image-area" style="display:none; margin-bottom:1.5rem;">
                <p style="font-size:0.55rem; font-weight:900; color:var(--text-muted); margin-bottom:8px;">RECIBO</p>
                <img id="detail-img" src="" style="width:100%; border-radius:15px; max-height:200px; object-fit:cover; cursor:pointer;" onclick="window.viewReceipt(this.src)">
            </div>

            <div id="detail-settle-area" style="display:none; background:#f0fdf4; border:1px dashed var(--accent); border-radius:20px; padding:1.5rem; margin-bottom:1.5rem;">
                <p style="font-size:0.6rem; font-weight:900; color:var(--accent); margin-bottom:10px;">SALDAR DEUDA</p>
                <form id="settle-form">
                    <div class="form-group">
                        <label>Comprobante de Pago (Opcional)</label>
                        <input type="file" id="settle-proof" accept="image/*">
                    </div>
                    <button type="button" id="confirm-settle-btn" class="btn-primary" style="background:var(--accent);">CONFIRMAR PAGO</button>
                    <button type="button" onclick="hideSettleForm()" style="width:100%; margin-top:10px; border:none; background:transparent; font-size:0.6rem; font-weight:800; color:var(--text-muted);">CANCELAR</button>
                </form>
            </div>

            <div style="display:flex; gap:10px;">
                <button id="detail-settle-btn" class="btn-primary" style="display:none; background:var(--accent); flex:1; box-shadow:none;">SALDAR</button>
                <button id="detail-edit-btn" class="btn-primary" style="background:#f1f5f9; color:var(--primary); flex:1; box-shadow:none;">EDITAR</button>
                <button id="detail-delete-btn" class="btn-primary" style="background:#fff1f2; color:var(--secondary); flex:1; box-shadow:none;">BORRAR</button>
            </div>
            <button onclick="closeDetailModal()" style="width:100%; margin-top:15px; border:none; background:transparent; font-weight:800; color:var(--text-muted); cursor:pointer; font-size:0.7rem;">CERRAR</button>
        </div>
    </div>

    <style>
        @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
    </style>

    <div class="bottom-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i>
            <span>Inicio</span>
        </a>
        <a href="{{ route('expenses.index') }}" class="nav-item {{ request()->routeIs('expenses.index') ? 'active' : '' }}">
            <i class="fas fa-search-dollar"></i>
            <span>Gastos</span>
        </a>
        
        <a href="{{ route('expenses.create') }}" class="nav-center">
            <i class="fas fa-plus"></i>
        </a>

        <a href="{{ route('budgets.index') }}" class="nav-item {{ request()->routeIs('budgets.index') ? 'active' : '' }}">
            <i class="fas fa-bullseye"></i>
            <span>Límites</span>
        </a>
        <a href="{{ route('wallet') }}" class="nav-item {{ request()->routeIs('wallet') ? 'active' : '' }}">
            <i class="fas fa-cog"></i>
            <span>Configuración</span>
        </a>
        @if(Auth::user()->is_admin)
            <a href="{{ route('admin.index') }}" class="nav-item {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                <i class="fas fa-user-shield"></i>
                <span>Admin</span>
            </a>
        @endif
    </div>

    <!-- PANEL DE NOTIFICACIONES -->
    <div id="notif-panel" style="display:none; position:fixed; inset:0; z-index:3000;">
        <div style="position:absolute; inset:0; background:rgba(0,0,0,0.3); backdrop-filter:blur(3px);" onclick="window.toggleNotifications()"></div>
        <div class="stat-card" style="position:absolute; right:10px; top:80px; width:calc(100% - 20px); max-width:350px; height:auto; max-height:80vh; overflow-y:auto; padding:1.5rem; animation: slideInNotif 0.3s ease-out; border-top: 5px solid var(--primary);">
            <h3 style="font-weight:900; margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center;">
                Notificaciones 🔔
                <span style="font-size:0.6rem; color:var(--text-muted); cursor:pointer;" onclick="window.toggleNotifications()">CERRAR</span>
            </h3>
            <div id="notif-list" style="display:flex; flex-direction:column; gap:10px;">
                <!-- Cargado vía JS -->
                <p style="text-align:center; color:var(--text-muted); font-size:0.7rem; padding:1rem;">No hay alertas pendientes.</p>
            </div>
        </div>
    </div>

    <style>
        @keyframes slideInNotif { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    @stack('scripts')
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
