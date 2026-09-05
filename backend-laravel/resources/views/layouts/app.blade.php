<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AdminPanel')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background-color: #f8fafc; font-family: 'Inter', system-ui, sans-serif; color: #1e293b; }
        .sidebar { width: 260px; min-height: 100vh; background: #fff; border-right: 1px solid #e2e8f0; position: fixed; top: 0; left: 0; z-index: 100; }
        .sidebar .brand { padding: 24px; font-weight: 700; font-size: 1.25rem; display: flex; align-items: center; gap: 12px; color: #0f172a; }
        .sidebar .brand-icon { width: 36px; height: 36px; background: #4f46e5; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; }
        .sidebar .menu-label { font-size: 0.725rem; font-weight: 600; color: #94a3b8; padding: 16px 24px 8px; text-transform: uppercase; letter-spacing: 0.05em; }
        .sidebar .nav-link { color: #64748b; padding: 10px 24px; display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 0.9rem; transition: all 0.2s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #f1f5f9; color: #4f46e5; border-right: 3px solid #4f46e5; }
        .main-content { margin-left: 260px; }
        .topbar { background: #fff; border-bottom: 1px solid #e2e8f0; padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; }
        .stat-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; display: flex; align-items: center; gap: 16px; }
        .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .content-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 24px; }
        .table > :not(caption) > * > * { padding: 14px 16px; border-bottom-color: #f1f5f9; }
        
        /* Style pour les pages de login/register sans sidebar */
        .auth-page .sidebar { display: none; }
        .auth-page .main-content { margin-left: 0; }
        .auth-page .topbar { display: none; }
        .auth-page .p-4 { padding-top: 40px !important; }
    </style>
    @stack('styles')
</head>
<body class="@if(Route::currentRouteName() === 'login' || Route::currentRouteName() === 'register') auth-page @endif">

{{-- ===== SIDEBAR ===== --}}
<div class="sidebar">
    <div class="brand">
        <div class="brand-icon"><i class="bi bi-bar-chart-line-fill"></i></div>
        AdminPanel
    </div>

    <div class="menu-label">Menu</div>
    <a href="{{ route('dashboard') }}"
       class="nav-link {{ request()->routeIs('*dashboard*') ? 'active' : '' }}">
        <i class="bi bi-grid-1x2"></i> Tableau de bord
    </a>

    @auth
        {{-- Lien Demandes : URL différente selon le rôle --}}
        <a href="{{ Auth::user()->isSuperAdmin() ? route('super-admin.demandes') : route('admin.demandes') }}"
           class="nav-link {{ request()->routeIs('*demandes*') ? 'active' : '' }}">
            <i class="bi bi-inbox"></i> Demandes
        </a>

        {{-- Section Administration visible uniquement pour le super-admin --}}
        @if(Auth::user()->isSuperAdmin())
            <div class="menu-label">Administration</div>
            <a href="{{ route('super-admin.admins.index') }}"
               class="nav-link {{ request()->routeIs('*admins*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Gestion Administrateurs
            </a>
        @endif
    @endauth

    <div class="menu-label">Compte</div>
    <a href="#" class="nav-link text-danger"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <i class="bi bi-box-arrow-right"></i> Déconnexion
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</div>

{{-- ===== MAIN CONTENT ===== --}}
<div class="main-content">

    {{-- Topbar --}}
    <div class="topbar">
        <div class="search-box position-relative" style="width: 320px;">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            <input type="text" class="form-control bg-light border-0 ps-5" placeholder="Rechercher...">
        </div>

        <div class="d-flex align-items-center gap-3">
            @auth
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2" role="button" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4f46e5&color=fff"
                             class="rounded-circle" width="38" height="38" alt="Avatar">
                        <div class="d-none d-md-block text-start">
                            <span class="fw-semibold d-block lh-1 fs-6">{{ Auth::user()->name }}</span>
                            <small class="text-muted">{{ ucfirst(Auth::user()->role) }}</small>
                        </div>
                        <i class="bi bi-chevron-down text-muted ms-1"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                               href="#"
                               data-bs-toggle="modal"
                               data-bs-target="#modalMonCompte">
                                <i class="bi bi-person"></i> Mon compte
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                               href="#"
                               data-bs-toggle="modal"
                               data-bs-target="#modalMotDePasse">
                                <i class="bi bi-key"></i> Changer mot de passe
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 text-danger"
                               href="#"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right"></i> Se déconnecter
                            </a>
                        </li>
                    </ul>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Se connecter</a>
            @endauth
        </div>
    </div>

    {{-- Contenu des pages enfants --}}
    <div class="p-4">
        @yield('content')
    </div>

</div>

{{-- ===== MODALE : MON COMPTE ===== --}}
@auth
<div class="modal fade" id="modalMonCompte" tabindex="-1" aria-labelledby="modalMonCompteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('profil.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalMonCompteLabel">
                        <i class="bi bi-person-circle me-2"></i>Mon compte
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nom complet</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', Auth::user()->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', Auth::user()->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Contact</label>
                        <input type="text" name="contact" class="form-control @error('contact') is-invalid @enderror"
                               value="{{ old('contact', Auth::user()->contact) }}">
                        @error('contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-medium">Rôle</label>
                        <input type="text" class="form-control bg-light" value="{{ ucfirst(Auth::user()->role) }}" disabled>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== MODALE : CHANGER MOT DE PASSE ===== --}}
<div class="modal fade" id="modalMotDePasse" tabindex="-1" aria-labelledby="modalMotDePasseLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('profil.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalMotDePasseLabel">
                        <i class="bi bi-key me-2"></i>Changer mot de passe
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    @if($errors->hasBag('password'))
                        <div class="alert alert-danger">{{ $errors->getBag('password')->first() }}</div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-medium">Mot de passe actuel</label>
                        <input type="password" name="mot_de_passe_actuel" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nouveau mot de passe</label>
                        <input type="password" name="mot_de_passe" class="form-control" required minlength="8">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-medium">Confirmer le nouveau mot de passe</label>
                        <input type="password" name="mot_de_passe_confirmation" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Modifier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>