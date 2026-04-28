<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - GASTOS</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .auth-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .auth-card {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .auth-header p {
            color: var(--text-muted);
        }
        .error-msg {
            color: var(--primary);
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
        .footer-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        .footer-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="background-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <div class="auth-container">
        <div class="card glass auth-card">
            <div class="auth-header">
                <h1>GASTOS<span>.</span></h1>
                <p>Inicia sesión para continuar</p>
            </div>

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email') <p class="error-msg">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                    @error('password') <p class="error-msg">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-primary">Entrar</button>
            </form>

            <div class="footer-link">
                ¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a>
            </div>
        </div>
    </div>
</body>
</html>
