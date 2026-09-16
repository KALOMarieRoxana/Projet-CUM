<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — État Civil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(79,70,229,0.35) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: absolute;
            bottom: -20%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(59,130,246,0.25) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        /* ═══════════ CARTE RECTANGLE ═══════════ */
        .auth-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(148, 163, 184, 0.15);
            border-radius: 16px;
            padding: 32px 36px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .auth-header img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .auth-header .etat-civil {
            font-size: 18px;
            font-weight: 700;
            color: #F1F5F9;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .auth-header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #FFFFFF;
            margin-bottom: 4px;
        }

        .auth-header p {
            font-size: 13px;
            color: #94A3B8;
            margin: 0;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.prefix {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 15px;
            pointer-events: none;
        }

        .input-wrapper input {
            width: 100%;
            padding: 12px 42px 12px 42px;
            border-radius: 10px;
            border: 1px solid rgba(148, 163, 184, 0.25);
            background: rgba(30, 41, 59, 0.6);
            color: #F1F5F9;
            font-size: 14px;
            outline: none;
            transition: border 0.2s, background 0.2s;
        }

        .input-wrapper input::placeholder {
            color: #64748B;
        }

        .input-wrapper input:focus {
            border-color: #4F46E5;
            background: rgba(30, 41, 59, 0.9);
        }

        .input-wrapper .toggle-eye {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            cursor: pointer;
            font-size: 15px;
            background: none;
            border: none;
            padding: 0;
        }

        .options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            font-size: 13px;
        }

        .options-row label {
            color: #CBD5E1;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .options-row input[type="checkbox"] {
            accent-color: #4F46E5;
        }

        .options-row a {
            color: #60A5FA;
            text-decoration: none;
        }

        .options-row a:hover {
            text-decoration: underline;
        }

        .btn-primary-custom {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: none;
            background: #2563EB;
            color: #FFF;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-primary-custom:hover {
            background: #1D4ED8;
        }

        .btn-primary-custom:active {
            transform: scale(0.99);
        }

        .auth-footer {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: #94A3B8;
        }

        .auth-footer a {
            color: #60A5FA;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .alert-error {
            background: rgba(220, 38, 38, 0.15);
            border: 1px solid rgba(220, 38, 38, 0.4);
            color: #FCA5A5;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>

<div class="auth-card">

    <div class="auth-header">
        <img src="{{ asset('images/logo.png') }}" alt="État Civil">
        <div class="etat-civil">ÉTAT CIVIL</div>
        <h1>Connexion</h1>
        <p>Connectez-vous à votre compte</p>
    </div>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if (session('status'))
        <div class="alert-error" style="background: rgba(16,185,129,0.15); border-color: rgba(16,185,129,0.4); color: #6EE7B7;">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <div class="input-wrapper">
                <i class="bi bi-envelope prefix"></i>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email ou téléphone" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <div class="input-wrapper">
                <i class="bi bi-lock prefix"></i>
                <input type="password" name="password" id="password" placeholder="Mot de passe" required>
                <button type="button" class="toggle-eye" onclick="togglePassword('password', this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="options-row">
            <label>
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                Se souvenir de moi
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
            @endif
        </div>

        <button type="submit" class="btn-primary-custom">
            Se connecter
        </button>
    </form>

    <div class="auth-footer">
        Pas encore de compte ?
        <a href="{{ route('register') }}">S'inscrire</a>
    </div>

</div>

<script>
    function togglePassword(id, btn) {
        const input = document.getElementById(id);
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