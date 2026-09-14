<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion - CUM État Civil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F3F4F6;
            padding: 20px;
        }
        .login-container {
            width: 100%;
            max-width: 1000px;
            background: #FFFFFF;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 580px;
        }

        /* ===== Partie gauche : Image de fond ===== */
        .left-side {
            position: relative;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.85), rgba(29, 78, 216, 0.85)),
                        url('{{ asset('images/login-bg.jpg') }}') center/cover no-repeat;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #FFFFFF;
        }
        .left-side::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.75) 0%, rgba(29, 78, 216, 0.9) 100%);
        }
        .left-content { position: relative; z-index: 1; }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }
        .brand-logo {
            width: 56px;
            height: 56px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            border: 2px solid rgba(255,255,255,0.3);
        }
        .brand-text h1 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .brand-text p {
            font-size: 13px;
            opacity: 0.9;
            margin: 0;
        }

        .tagline {
            font-size: 22px;
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 32px;
            max-width: 340px;
        }

        .features {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            opacity: 0.95;
        }
        .feature-icon {
            width: 34px;
            height: 34px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }

        /* ===== Partie droite : Formulaire ===== */
        .right-side {
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .tabs {
            display: flex;
            background: #F3F4F6;
            border-radius: 10px;
            padding: 4px;
            margin-bottom: 28px;
        }
        .tab {
            flex: 1;
            padding: 10px;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            color: #6B7280;
            text-decoration: none;
        }
        .tab.active {
            background: #2563EB;
            color: #FFFFFF;
            box-shadow: 0 2px 6px rgba(37,99,235,0.3);
        }

        .form-title {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
        }
        .form-subtitle {
            font-size: 13px;
            color: #6B7280;
            margin-bottom: 28px;
        }

        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper .icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 16px;
        }
        .input-wrapper input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            font-size: 14px;
            color: #111827;
            outline: none;
            transition: border 0.2s;
        }
        .input-wrapper input:focus {
            border-color: #2563EB;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .input-wrapper .toggle-pwd {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9CA3AF;
            cursor: pointer;
            font-size: 16px;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .form-check {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .form-check input { accent-color: #2563EB; }
        .form-check label { color: #6B7280; cursor: pointer; margin: 0; }
        .forgot-link {
            color: #2563EB;
            text-decoration: none;
            font-weight: 500;
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: #2563EB;
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-submit:hover {
            background: #1D4ED8;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37,99,235,0.3);
        }

        .register-link {
            text-align: center;
            font-size: 13px;
            color: #6B7280;
            margin-top: 20px;
        }
        .register-link a {
            color: #2563EB;
            font-weight: 600;
            text-decoration: none;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .alert-danger {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FCA5A5;
        }

        @media (max-width: 768px) {
            .login-container { grid-template-columns: 1fr; }
            .left-side { display: none; }
        }
    </style>
</head>
<body>

<div class="login-container">

    {{-- ===== Partie droite : Formulaire ===== --}}
    <div class="right-side">

        {{-- Onglets Connexion / Inscription --}}
        <div class="tabs">
            <a href="{{ route('login') }}" class="tab active">Connexion</a>
            <a href="{{ route('register') }}" class="tab">Inscription</a>
        </div>

        <h2 class="form-title">Connectez-vous</h2>
        <p class="form-subtitle">Accédez à votre espace personnel</p>

        {{-- Messages d'erreur --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div><i class="bi bi-exclamation-circle"></i> {{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Formulaire de connexion --}}
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label>Adresse email</label>
                <div class="input-wrapper">
                    <i class="bi bi-envelope icon"></i>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="votre.email@exemple.com" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label>Mot de passe</label>
                <div class="input-wrapper">
                    <i class="bi bi-lock icon"></i>
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                    <button type="button" class="toggle-pwd" onclick="togglePassword('password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <div class="form-check">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Se souvenir de moi</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">Mot de passe oublié ?</a>
                @endif
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-box-arrow-in-right"></i> Se connecter
            </button>
        </form>

        <div class="register-link">
            Pas encore de compte ?
            <a href="{{ route('register') }}">Créer un compte</a>
        </div>
    </div>
</div>

<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }
</script>

</body>
</html>