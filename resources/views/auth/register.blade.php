<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro - GASTOS</title>
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
            max-width: 450px;
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
                <p>Crea tu cuenta de pareja</p>
            </div>

            <form action="{{ route('register') }}" method="POST">
                @csrf
                @if(isset($token))
                    <input type="hidden" name="token" value="{{ $token }}">
                @endif

                <div class="form-group">
                    <label for="name">Nombre</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                    @error('name') <p class="error-msg">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ $email ?? old('email') }}" {{ isset($email) ? 'readonly' : '' }} required>
                    @error('email') <p class="error-msg">{{ $message }}</p> @enderror
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirmar</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
                @error('password') <p class="error-msg">{{ $message }}</p> @enderror

                <button type="submit" class="btn-primary">Registrarse</button>
            </form>

            <div class="footer-link">
                ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
            </div>
        </div>
    </div>
</body>
</html>
