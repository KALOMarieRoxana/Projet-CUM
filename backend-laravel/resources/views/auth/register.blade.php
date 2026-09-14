<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - CUM État Civil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Réutiliser les mêmes styles que login */
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
            min-height: 620px;
        }
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
        .brand-text h1 { font-size: 20px; font-weight: 700; margin: 0; }
        .brand-text p { font-size: 13px; opacity: 0.9; margin: 0; }
        .tagline { font-size: 22px; font-weight: 600; line-height: 1.4; margin-bottom: 32px; max-width: 340px; }
        .features { display: flex; flex-direction: column; gap: 14px; }
        .feature-item { display: flex; align-items: center; gap: 12px; font-size: 14px; opacity: 0.95; }
        .feature-icon {
            width: 34px; height: 34px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }
        .right-side {
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
            max-height: 620px;
        }
        .tabs {
            display: flex;
            background: #F3F4F6;
            border-radius: 10px;
            padding: 4px;
            margin-bottom: 24px;
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
        .form-title { font-size: 22px; font-weight: 700; color: #111827; margin-bottom: 6px; }
        .form-subtitle { font-size: 13px; color: #6B7280; margin-bottom: 20px; }
        .form-group { margin-bottom: 14px; }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        .input-wrapper { position: relative; }
        .input-wrapper .icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 16px;
        }
        .input-wrapper input, .input-wrapper select {
            width: 100%;
            padding: 11px 14px 11px 42px;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            font-size: 14px;
            color: #111827;
            outline: none;
            transition: border 0.2s;
            background: #FFF;
        }
        .input-wrapper input:focus, .input-wrapper select:focus {
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
            margin-top: 10px;
        }
        .btn-submit:hover {
            background: #1D4ED8;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37,99,235,0.3);
        }
        .login-link {
            text-align: center;
            font-size: 13px;
            color: #6B7280;
            margin-top: 16px;
        }
        .login-link a { color: #2563EB; font-weight: 600; text-decoration: none; }
        .alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; }
        .alert-danger { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        @media (max-width: 768px) {
            .login-container { grid-template-columns: 1fr; }
            .left-side { display: none; }
        }
    </style>
</head>
<body>

<div class="login-container">
    
    {{-- Partie droite : Formulaire d'inscription --}}
    <div class="right-side">

        <div class="tabs">
            <a href="{{ route('login') }}" class="tab">Connexion</a>
            <a href="{{ route('register') }}" class="tab active">Inscription</a>
        </div>

        <h2 class="form-title">Créer un compte</h2>
        <p class="form-subtitle">Rejoignez le portail citoyen</p>

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div><i class="bi bi-exclamation-circle"></i> {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="grid-2">
                <div class="form-group">
                    <label>Nom</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person icon"></i>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="KALO" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Prénom</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person icon"></i>
                        <input type="text" name="prenom" value="{{ old('prenom') }}" placeholder="Marie" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Adresse email</label>
                <div class="input-wrapper">
                    <i class="bi bi-envelope icon"></i>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="votre.email@exemple.com" required>
                </div>
            </div>

            <div class="form-group">
                <label>Contact</label>
                <div class="input-wrapper">
                    <i class="bi bi-telephone icon"></i>
                    <input type="text" name="contact" value="{{ old('contact') }}" placeholder="0376191729" required>
                </div>
            </div>

            <div class="form-group">
                <label>Rôle</label>
                <div class="input-wrapper">
                    <i class="bi bi-shield icon"></i>
                    <select name="role" required>
                        <option value="">— Sélectionner un rôle —</option>
                        <option value="citoyen" {{ old('role') == 'citoyen' ? 'selected' : '' }}>Citoyen</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>
            </div>

            <div class="grid-2">
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

                <div class="form-group">
                    <label>Confirmer</label>
                    <div class="input-wrapper">
                        <i class="bi bi-lock-fill icon"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="••••••••" required>
                        <button type="button" class="toggle-pwd" onclick="togglePassword('password_confirmation', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-person-plus"></i> Créer mon compte
            </button>
        </form>

        <div class="login-link">
            Déjà inscrit ?
            <a href="{{ route('login') }}">Se connecter</a>
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