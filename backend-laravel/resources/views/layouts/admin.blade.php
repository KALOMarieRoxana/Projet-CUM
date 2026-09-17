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
            width: 40px;
            height: 40px;
            object-fit: contain;
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
            position: relative;
            z-index: 100;
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

        .filter-btn.active { color: #fff !important; }
        .btn-outline-secondary.active { background: #6c757d; }
        .btn-outline-warning.active { background: #ea580c; border-color: #ea580c; }
        .btn-outline-success.active { background: #059669; border-color: #059669; }
        .btn-outline-danger.active { background: #dc2626; border-color: #dc2626; }

        .avatar-initiales {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            color: #FFF;
            flex-shrink: 0;
        }

        /* ═══════════════════════════════════════════════════════════ */
    /* STAT CHART CARDS (Able Pro Style)                           */
    /* ═══════════════════════════════════════════════════════════ */
    .stat-chart-card {
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 14px;
        padding: 20px 22px 12px 22px;
        transition: all 0.2s;
        overflow: hidden;
        height: 100%;
    }
    .stat-chart-card:hover {
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        transform: translateY(-2px);
    }
    .stat-chart-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }
    .stat-chart-label {
        font-size: 13px;
        color: #6B7280;
        font-weight: 500;
        margin-top: 4px;
    }
    .stat-chart-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .stat-chart-value {
        font-size: 28px;
        font-weight: 800;
        color: #111827;
        line-height: 1.1;
        margin-bottom: 6px;
        letter-spacing: -0.5px;
    }
    .stat-chart-evolution {
        font-size: 12.5px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        margin-bottom: 8px;
    }
    .stat-chart-graph {
        margin: 0 -22px -12px -22px;
        padding: 0 12px;
        opacity: 0.95;
    }

        /* ✅ NOTIFICATIONS */
        .notification-wrapper {
            position: relative;
        }
        .notification-btn {
            background: none;
            border: none;
            padding: 6px;
            cursor: pointer;
            color: #6b7280;
            position: relative;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .notification-btn:hover {
            background: #f3f4f6;
            color: #4f46e5;
        }
        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: #dc2626;
            color: #FFF;
            border-radius: 50%;
            min-width: 18px;
            height: 18px;
            font-size: 10px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.4);
        }
        .notification-panel {
            display: none;
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 380px;
            max-height: 500px;
            overflow-y: auto;
            background: #FFF;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            z-index: 1000;
        }
        .notification-panel-header {
            padding: 14px 18px;
            border-bottom: 1px solid #E5E7EB;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: #FFF;
            z-index: 1;
        }
        .notification-panel-header strong {
            font-size: 14px;
        }
        .notification-panel-header button {
            background: none;
            border: none;
            color: #4F46E5;
            font-size: 12px;
            cursor: pointer;
            font-weight: 500;
        }
        .notification-panel-header button:hover {
            text-decoration: underline;
        }
        .notification-item {
            padding: 14px 18px;
            border-bottom: 1px solid #F3F4F6;
            cursor: pointer;
            transition: background 0.15s;
        }
        .notification-item:hover {
            background: #F9FAFB;
        }
        .notification-item.unread {
            background: #F0F9FF;
        }
        .notification-item.unread:hover {
            background: #E0F2FE;
        }
        .notification-item-content {
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }
        .notification-item-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #EEF2FF;
            color: #4F46E5;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .notification-item-body {
            flex: 1;
            min-width: 0;
        }
        .notification-item-title {
            font-size: 13px;
            color: #111827;
            margin-bottom: 4px;
        }
        .notification-item.unread .notification-item-title {
            font-weight: 600;
        }
        .notification-item-message {
            font-size: 12px;
            color: #6B7280;
            line-height: 1.4;
        }
        .notification-item-date {
            font-size: 11px;
            color: #9CA3AF;
            margin-top: 6px;
        }
        .notification-empty {
            padding: 40px 20px;
            text-align: center;
            color: #9CA3AF;
            font-size: 13px;
        }
        .notification-empty i {
            font-size: 32px;
            display: block;
            margin-bottom: 10px;
        }
    </style>
    @stack('styles')
</head>
<body>

@php
    $user = Auth::user();
    $nom = trim($user->name ?? 'U');
    $mots = preg_split('/\s+/', $nom);
    $initiales = count($mots) >= 2
        ? strtoupper(mb_substr($mots[0], 0, 1) . mb_substr($mots[1], 0, 1))
        : strtoupper(mb_substr($nom, 0, 2));

    $couleurAvatar = $user->role === 'super_admin'
        ? 'linear-gradient(135deg, #4F46E5, #8B5CF6)'
        : 'linear-gradient(135deg, #F59E0B, #F97316)';
@endphp

    {{-- ═══════════ SIDEBAR ═══════════ --}}
    <div class="sidebar">
        <div class="brand">
            <img src="{{ asset('images/logo.png') }}"
                 alt="Logo"
                 class="logo-box">
            AdminPanel
        </div>

        <div class="menu-label">Menu</div>

        <a href="{{ Auth::user()->isSuperAdmin() ? route('super-admin.dashboard') : route('admin.dashboard') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard') || request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid"></i> Tableau de bord
        </a>

        @if (Auth::user()->isSuperAdmin())
            <a href="{{ route('super-admin.demandes') }}"
               class="nav-link {{ request()->routeIs('super-admin.demandes*') ? 'active' : '' }}">
                <i class="bi bi-inbox"></i> Demandes
            </a>
        @else
            <a href="{{ route('admin.demandes') }}"
               class="nav-link {{ request()->routeIs('admin.demandes*') ? 'active' : '' }}">
                <i class="bi bi-inbox"></i> Demandes
            </a>
        @endif

        {{-- Archives --}}
        @if (Auth::user()->isSuperAdmin())
            <a href="{{ route('super-admin.archives') }}"
                class="nav-link {{ request()->routeIs('super-admin.archives*') ? 'active' : '' }}">
                <i class="bi bi-archive"></i> Archives
            </a>
        @else
            <a href="{{ route('admin.archives') }}"
                class="nav-link {{ request()->routeIs('admin.archives*') ? 'active' : '' }}">
                <i class="bi bi-archive"></i> Archives
            </a>
        @endif

        @if (Auth::user()->isSuperAdmin())
            <a href="{{ route('super-admin.statistiques') }}"
               class="nav-link {{ request()->routeIs('super-admin.statistiques*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill"></i> Statistiques
            </a>
        @else
            <a href="{{ route('admin.statistiques') }}"
               class="nav-link {{ request()->routeIs('admin.statistiques*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill"></i> Statistiques
            </a>
        @endif

        @if (Auth::user()->isSuperAdmin())
            <a href="{{ route('super-admin.paiements.index') }}"
               class="nav-link {{ request()->routeIs('super-admin.paiements.index') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> Paiements
            </a>
        @else
            <a href="{{ route('admin.paiements.index') }}"
               class="nav-link {{ request()->routeIs('admin.paiements.index') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> Paiements
            </a>
        @endif

        @if (Auth::user()->isSuperAdmin())
            <a href="{{ route('super-admin.paiements.liste') }}"
               class="nav-link {{ request()->routeIs('super-admin.paiements.liste*') ? 'active' : '' }}">
                <i class="bi bi-list-ul"></i> Liste des Paiements
            </a>
        @else
            <a href="{{ route('admin.paiements.liste') }}"
               class="nav-link {{ request()->routeIs('admin.paiements.liste*') ? 'active' : '' }}">
                <i class="bi bi-list-ul"></i> Liste des Paiements
            </a>
        @endif

        @if (Auth::user()->isSuperAdmin())
            <div class="menu-label">Administration</div>
            <a href="{{ route('super-admin.admins.index') }}"
               class="nav-link {{ request()->routeIs('super-admin.admins*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Gestion Administrateurs
            </a>

             <a href="{{ route('super-admin.citoyens.index') }}"
                class="nav-link {{ request()->routeIs('super-admin.citoyens*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Citoyens
            </a>

            <a href="{{ route('super-admin.types-actes.index') }}"
                class="nav-link {{ request()->routeIs('super-admin.types-actes*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Types d'actes
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

    {{-- ═══════════ MAIN CONTENT ═══════════ --}}
    <div class="main-content">
        <div class="topbar">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <span>Rechercher...</span>
            </div>

            <div class="d-flex align-items-center gap-3">

                {{-- ✅ CLOCHE NOTIFICATIONS --}}
                <div class="notification-wrapper" id="notification-wrapper">
                    <button type="button" class="notification-btn" id="btn-notifications">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="notification-badge" id="badge-notifications" style="display: none;">0</span>
                    </button>

                    <div class="notification-panel" id="panel-notifications">
                        <div class="notification-panel-header">
                            <strong>Notifications</strong>
                            <button type="button" id="btn-tout-lire">Tout marquer comme lu</button>
                        </div>
                        <div id="liste-notifications">
                            <div class="notification-empty">
                                <i class="bi bi-bell-slash"></i>
                                Chargement...
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PROFIL --}}
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-initiales" style="background:{{ $couleurAvatar }};">
                            {{ $initiales }}
                        </div>
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

    {{-- ═══════════ MODAL : Mon compte ═══════════ --}}
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

    {{-- ═══════════ MODAL : Changer mot de passe ═══════════ --}}
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

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SCRIPT NOTIFICATIONS                                        --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        const isSuperAdmin = {{ Auth::user()->isSuperAdmin() ? 'true' : 'false' }};
        const baseUrl = isSuperAdmin ? '/super-admin' : '/admin';

        console.log('🔔 Notifications actives — baseUrl:', baseUrl);

        const btnNotif = document.getElementById('btn-notifications');
        const panelNotif = document.getElementById('panel-notifications');
        const badgeNotif = document.getElementById('badge-notifications');
        const listeNotif = document.getElementById('liste-notifications');
        const btnToutLire = document.getElementById('btn-tout-lire');

        if (!btnNotif) return;

        /* ═══════════════════════════════════════════════════════
           1. COMPTEUR
        ═══════════════════════════════════════════════════════ */
        async function chargerCompteur() {
            try {
                const res = await fetch(`${baseUrl}/notifications/compteur`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                const data = await res.json();

                if (data.count > 0) {
                    badgeNotif.textContent = data.count > 99 ? '99+' : data.count;
                    badgeNotif.style.display = 'flex';
                } else {
                    badgeNotif.style.display = 'none';
                }
            } catch (err) {
                console.warn('Erreur compteur:', err);
            }
        }

        /* ═══════════════════════════════════════════════════════
           2. LISTE
        ═══════════════════════════════════════════════════════ */
        async function chargerListe() {
            try {
                const res = await fetch(`${baseUrl}/notifications`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                const data = await res.json();

                if (!data.notifications || data.notifications.length === 0) {
                    listeNotif.innerHTML = `
                        <div class="notification-empty">
                            <i class="bi bi-bell-slash"></i>
                            Aucune notification
                        </div>`;
                    return;
                }

                listeNotif.innerHTML = data.notifications.map(n => `
                    <div class="notification-item ${n.lue ? '' : 'unread'}"
                         onclick="marquerLue(${n.id})">
                        <div class="notification-item-content">
                            <div class="notification-item-icon">
                                <i class="bi bi-bell-fill"></i>
                            </div>
                            <div class="notification-item-body">
                                <div class="notification-item-title">${n.titre}</div>
                                <div class="notification-item-message">${n.message}</div>
                                <div class="notification-item-date">
                                    ${new Date(n.created_at).toLocaleString('fr-FR')}
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');

            } catch (err) {
                console.error('Erreur liste:', err);
                listeNotif.innerHTML = `
                    <div class="notification-empty" style="color: #dc2626;">
                        <i class="bi bi-exclamation-circle"></i>
                        Erreur de chargement
                    </div>`;
            }
        }

        /* ═══════════════════════════════════════════════════════
           3. MARQUER LUE
        ═══════════════════════════════════════════════════════ */
        window.marquerLue = async function(id) {
            try {
                await fetch(`${baseUrl}/notifications/${id}/marquer-lue`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    credentials: 'same-origin',
                });
                chargerCompteur();
                chargerListe();
            } catch (err) {
                console.warn('Erreur marquer lue:', err);
            }
        };

        /* ═══════════════════════════════════════════════════════
           4. TOUT MARQUER LU
        ═══════════════════════════════════════════════════════ */
        btnToutLire.addEventListener('click', async function (e) {
            e.stopPropagation();
            try {
                await fetch(`${baseUrl}/notifications/marquer-toutes-lues`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    credentials: 'same-origin',
                });
                chargerCompteur();
                chargerListe();
            } catch (err) {
                console.warn('Erreur tout lire:', err);
            }
        });

        /* ═══════════════════════════════════════════════════════
           5. OUVRIR / FERMER
        ═══════════════════════════════════════════════════════ */
        btnNotif.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = panelNotif.style.display === 'block';

            if (isOpen) {
                panelNotif.style.display = 'none';
            } else {
                panelNotif.style.display = 'block';
                chargerListe();
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#notification-wrapper')) {
                panelNotif.style.display = 'none';
            }
        });

        /* ═══════════════════════════════════════════════════════
           6. LANCEMENT
        ═══════════════════════════════════════════════════════ */
        chargerCompteur();
        setInterval(chargerCompteur, 15000);   // toutes les 15s

    });
    </script>

    @stack('scripts')
</body>
</html>