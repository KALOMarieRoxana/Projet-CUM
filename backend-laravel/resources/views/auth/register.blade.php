<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — État Civil</title>
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
            max-width: 520px;              /* ⬅️ Plus large pour l'inscription */
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
            margin-bottom: 22px;
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

        /* ✅ Grille 2 colonnes pour compacter */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .form-grid .full {
            grid-column: 1 / -1;
        }

        .form-group {
            margin-bottom: 0;
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

        .input-wrapper input,
        .input-wrapper select {
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

        .input-wrapper input:focus,
        .input-wrapper select:focus {
            border-color: #4F46E5;
            background: rgba(30, 41, 59, 0.9);
        }

        .input-wrapper select {
            appearance: none;
            padding-right: 42px;
        }

        .input-wrapper select option {
            background: #1E293B;
            color: #F1F5F9;
        }

        .input-wrapper .chevron {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            pointer-events: none;
            font-size: 13px;
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
            margin-top: 10px;
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

        /* ✅ Responsive : 1 colonne sur mobile */
        @media (max-width: 500px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="auth-card">

    <div class="auth-header">
        <img src="{{ asset('images/logo.png') }}" alt="État Civil">
        <div class="etat-civil">ÉTAT CIVIL</div>
        <h1>Inscription</h1>
        <p>Créez votre compte</p>
    </div>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Ligne 1 : Nom + Prénom --}}
        <div class="form-grid">
            <div class="form-group">
                <div class="input-wrapper">
                    <i class="bi bi-person prefix"></i>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Nom" required>
                </div>
            </div>
            <div class="form-group">
                <div class="input-wrapper">
                    <i class="bi bi-person prefix"></i>
                    <input type="text" name="prenom" value="{{ old('prenom') }}" placeholder="Prénom" required>
                </div>
            </div>
        </div>

        {{-- Ligne 2 : Email + Téléphone --}}
        <div class="form-grid">
            <div class="form-group">
                <div class="input-wrapper">
                    <i class="bi bi-envelope prefix"></i>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
                </div>
            </div>
            <div class="form-group">
                <div class="input-wrapper">
                    <i class="bi bi-telephone prefix"></i>
                    <input type="text" name="contact" value="{{ old('contact') }}" placeholder="Téléphone">
                </div>
            </div>
        </div>

        {{-- Ligne 3 : Mot de passe + Confirmation --}}
        <div class="form-grid">
            <div class="form-group">
                <div class="input-wrapper">
                    <i class="bi bi-lock prefix"></i>
                    <input type="password" name="password" id="password" placeholder="Mot de passe" required>
                    <button type="button" class="toggle-eye" onclick="togglePassword('password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="form-group">
                <div class="input-wrapper">
                    <i class="bi bi-lock prefix"></i>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirmer" required>
                    <button type="button" class="toggle-eye" onclick="togglePassword('password_confirmation', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Ligne 4 : Rôle --}}
        <div class="form-group" style="margin-bottom: 10px;">
            <div class="input-wrapper">
                <i class="bi bi-shield prefix"></i>
                <select name="role" required>
                    <option value="">Choisir votre rôle</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>🛡 Admin</option>
                    <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>👑 Super Admin</option>
                </select>
                <i class="bi bi-chevron-down chevron"></i>
            </div>
        </div>

        <button type="submit" class="btn-primary-custom">
            S'inscrire
        </button>
    </form>

    <div class="auth-footer">
        Déjà un compte ?
        <a href="{{ route('login') }}">Se connecter</a>
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