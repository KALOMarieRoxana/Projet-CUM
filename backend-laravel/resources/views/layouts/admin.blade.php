<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        body {
            background-color: #f9fafb;
            font-family: 'Segoe UI', sans-serif;
        }
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #fff;
            border-right: 1px solid #eee;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px 0;
        }
        .sidebar .brand {
            padding: 0 24px 20px;
            font-weight: 700;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar .brand .logo-box {
            width: 34px; height: 34px;
            background: #4f46e5;
            border-radius: 8px;
        }
        .sidebar .menu-label {
            font-size: 0.75rem;
            color: #9ca3af;
            padding: 10px 24px 6px;
            text-transform: uppercase;
        }
        .sidebar .nav-link {
            color: #4b5563;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.92rem;
            border-radius: 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #f3f4f6;
            color: #4f46e5;
        }
        .main-content {
            margin-left: 260px;
        }
        .topbar {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 14px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .topbar .search-box {
            background: #f9fafb;
            border-radius: 8px;
            padding: 8px 16px;
            width: 400px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #9ca3af;
        }
        .stat-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #eee;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .stat-card p {
            margin: 0;
            color: #9ca3af;
            font-size: 0.85rem;
        }
        .content-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #eee;
        }
        .badge-status-attente { background: #fff7ed; color: #ea580c; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; }
        .badge-status-acceptee { background: #ecfdf5; color: #059669; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; }
        .badge-status-refusee { background: #fef2f2; color: #dc2626; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; }

        .filter-btn.active {
            color: #fff !important;
        }
        .btn-outline-secondary.active { background: #6c757d; }
        .btn-outline-warning.active { background: #ea580c; border-color: #ea580c; }
        .btn-outline-success.active { background: #059669; border-color: #059669; }
        .btn-outline-danger.active { background: #dc2626; border-color: #dc2626; }
    </style>
    @stack('styles')
</head>
<body>

    <div class="sidebar">
        <div class="brand">
            <div class="logo-box"></div>
            AdminPanel
        </div>

        <div class="menu-label">Menu</div>
        <a href="{{ Auth::user()->isSuperAdmin() ? route('super-admin.dashboard') : route('admin.dashboard') }}"
            class="nav-link {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid"></i> Tableau de bord
        </a>
     

        @if (Auth::user()->isAdmin())
            <a href="{{ route('admin.demandes') }}" class="nav-link {{ request()->routeIs('admin.demandes*') ? 'active' : '' }}">
                <i class="bi bi-inbox"></i> Demandes
            </a>
        @endif

        @if (Auth::user()->isSuperAdmin())
            <a href="{{ route('super-admin.demandes') }}" class="nav-link {{ request()->routeIs('super-admin.demandes*') ? 'active' : '' }}">
                <i class="bi bi-inbox"></i> Toutes les demandes
            </a>
            <a href="{{ route('super-admin.admins') }}" class="nav-link {{ request()->routeIs('super-admin.admins*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Administrateurs
            </a>
        @endif

        <div class="menu-label">Compte</div>
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-link">
            <i class="bi bi-box-arrow-right"></i> Déconnexion
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <span>Rechercher...</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-bell fs-5 text-secondary"></i>
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-secondary rounded-circle" style="width:36px;height:36px;"></div>
                        <span class="fw-semibold">{{ Auth::user()->name }}</span>
                        <i class="bi bi-chevron-down text-muted" style="font-size: 0.7rem;"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#modalMonCompte">
                                <i class="bi bi-person-circle"></i> Mon compte
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#modalMotDePasse">
                                <i class="bi bi-key"></i> Changer mot de passe
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-top').submit();">
                                <i class="bi bi-box-arrow-right"></i> Déconnexion
                            </a>
                            <form id="logout-form-top" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="p-4">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        {{-- Modal : Mon compte --}}
    <div class="modal fade" id="modalMonCompte" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('profil.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-person-circle me-2"></i>Mon compte</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', Auth::user()->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', Auth::user()->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact</label>
                            <input type="text" name="contact" class="form-control @error('contact') is-invalid @enderror" value="{{ old('contact', Auth::user()->contact) }}" required>
                            @error('contact') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Rôle</label>
                            <input type="text" class="form-control" value="{{ Auth::user()->role }}" disabled>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal : Changer mot de passe --}}
    <div class="modal fade" id="modalMotDePasse" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('profil.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-key me-2"></i>Changer mot de passe</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Mot de passe actuel</label>
                            <input type="password" name="mot_de_passe_actuel" class="form-control @error('mot_de_passe_actuel') is-invalid @enderror" required>
                            @error('mot_de_passe_actuel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nouveau mot de passe</label>
                            <input type="password" name="mot_de_passe" class="form-control @error('mot_de_passe') is-invalid @enderror" required>
                            @error('mot_de_passe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" name="mot_de_passe_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Rouvrir automatiquement la modale concernée en cas d'erreur de validation --}}
    @if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('ouvrir_modal') === 'password' || $errors->has('mot_de_passe_actuel') || $errors->has('mot_de_passe'))
                new bootstrap.Modal(document.getElementById('modalMotDePasse')).show();
            @elseif ($errors->has('name') || $errors->has('email') || $errors->has('contact'))
                new bootstrap.Modal(document.getElementById('modalMonCompte')).show();
            @endif
        });
    </script>
    @endif

    {{-- Message de succès global --}}
    @if (session('success'))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
        <div class="toast show align-items-center text-white bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">{{ session('success') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif
    @stack('scripts')
</body>
</html>