@extends('layouts.app')

@section('title', 'Gestion des Administrateurs')

@section('content')

{{-- ═══════════ HEADER ═══════════ --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Gestion des Administrateurs</h4>
        <p class="text-muted small mb-0">Ajoutez, modifiez ou supprimez des comptes administrateurs.</p>
    </div>

    {{-- ✅ Bouton qui ouvre la modale --}}
    <button type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#modalAjoutAdmin">
        <i class="bi bi-plus-lg me-1"></i> Ajouter un administrateur
    </button>
</div>

{{-- ═══════════ ALERTES ═══════════ --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ═══════════ TABLEAU ═══════════ --}}
<div class="content-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Nom</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Rôle</th>
                    <th>Date d'ajout</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $admin->name }}</td>
                        <td>{{ $admin->email }}</td>
                        <td>{{ $admin->contact ?? '—' }}</td>

                        <td>
                            @if(($admin->role ?? '') === 'super_admin')
                                <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded-pill">
                                    <i class="bi bi-shield-fill-check"></i> Super Admin
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded-pill">
                                    <i class="bi bi-shield"></i> Admin
                                </span>
                            @endif
                        </td>

                        <td>{{ $admin->created_at ? $admin->created_at->format('d/m/Y') : '—' }}</td>

                        <td class="text-end pe-3">
                            <div class="d-flex gap-2 justify-content-end">

                                <a href="{{ route('super-admin.admins.edit', $admin->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Modifier
                                </a>

                                @if($admin->id !== auth()->id())
                                    <form method="POST"
                                          action="{{ route('super-admin.admins.destroy', $admin->id) }}"
                                          onsubmit="return confirm('Voulez-vous vraiment supprimer cet administrateur ?');"
                                          style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small fst-italic">(Vous)</span>
                                @endif

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2"></i>
                            Aucun administrateur enregistré.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- ✅ DONUT — RÉPARTITION PAR RÔLE (Super Admin uniquement)   --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if(auth()->check() && auth()->user()->role === 'super_admin')
    @php
        $nbSuperAdmins = $admins->where('role', 'super_admin')->count();
        $nbAdmins      = $admins->where('role', 'admin')->count();
        $totalAdmins   = $admins->count();

        $pourcentage = fn($n) => $totalAdmins > 0 ? round(($n / $totalAdmins) * 100) : 0;
    @endphp

    <div class="content-card mt-4 p-4">
        <h5 class="fw-bold mb-1">Répartition par rôle</h5>
        <p class="text-muted small mb-4">Distribution des comptes administrateurs</p>

        <div class="d-flex align-items-center gap-5 flex-wrap">

            {{-- 🍩 Donut SVG --}}
            <div style="position: relative; width: 200px; height: 200px; flex-shrink: 0;">
                <svg width="200" height="200" viewBox="0 0 200 200">
                    {{-- Cercle de fond --}}
                    <circle cx="100" cy="100" r="70" fill="none"
                            stroke="#F3F4F6" stroke-width="30" />

                    @php
                        $rayon = 70;
                        $circ = 2 * M_PI * $rayon;

                        $segments = [
                            ['count' => $nbSuperAdmins, 'color' => '#4F46E5'],   // Indigo (Super Admin)
                            ['count' => $nbAdmins,      'color' => '#F59E0B'],   // Orange (Admin)
                        ];

                        $offset = 0;
                    @endphp

                    @foreach($segments as $seg)
                        @php
                            $ratio = $totalAdmins > 0 ? $seg['count'] / $totalAdmins : 0;
                            $dash  = $ratio * $circ;
                            $gap   = $circ - $dash;
                        @endphp
                        @if($seg['count'] > 0)
                            <circle cx="100" cy="100" r="{{ $rayon }}"
                                    fill="none"
                                    stroke="{{ $seg['color'] }}"
                                    stroke-width="30"
                                    stroke-dasharray="{{ $dash }} {{ $gap }}"
                                    stroke-dashoffset="{{ -$offset }}"
                                    transform="rotate(-90 100 100)" />
                            @php $offset += $dash; @endphp
                        @endif
                    @endforeach
                </svg>

                {{-- Centre --}}
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                    <div style="font-size: 32px; font-weight: 800; color: #111827; line-height: 1;">
                        {{ $totalAdmins }}
                    </div>
                    <div style="font-size: 11px; color: #6B7280; margin-top: 2px;">
                        Comptes
                    </div>
                </div>
            </div>

            {{-- 📋 Légende --}}
            <div style="flex: 1; min-width: 250px;">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #4F46E5;"></span>
                        <span class="fw-semibold">Super Admin</span>
                        <span class="text-muted small">({{ $nbSuperAdmins }})</span>
                    </div>
                    <span class="fw-bold">{{ $pourcentage($nbSuperAdmins) }} %</span>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #F59E0B;"></span>
                        <span class="fw-semibold">Admin</span>
                        <span class="text-muted small">({{ $nbAdmins }})</span>
                    </div>
                    <span class="fw-bold">{{ $pourcentage($nbAdmins) }} %</span>
                </div>
            </div>

        </div>
    </div>
@endif

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- ✅ MODALE AJOUT ADMINISTRATEUR                              --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalAjoutAdmin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 44px; height: 44px; background: #e0e7ff; color: #4f46e5;">
                        <i class="bi bi-person-plus-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Ajouter un administrateur</h5>
                        <small class="text-muted">Créez un nouveau compte administrateur</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            {{-- Body --}}
            <form method="POST" action="{{ route('super-admin.admins.store') }}" id="formAjoutAdmin">
                @csrf

                <div class="modal-body">

                    {{-- Erreurs --}}
                    @if($errors->any())
                        <div class="alert alert-danger small">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">

                        {{-- Nom --}}
                        <div class="col-md-6">
                            <label for="modal_name" class="form-label small fw-bold">
                                Nom <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" id="modal_name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6">
                            <label for="modal_email" class="form-label small fw-bold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" id="modal_email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required>
                            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Contact --}}
                        <div class="col-md-6">
                            <label for="modal_contact" class="form-label small fw-bold">Contact</label>
                            <input type="text" name="contact" id="modal_contact"
                                   class="form-control"
                                   value="{{ old('contact') }}"
                                   placeholder="Ex: 0341234567">
                        </div>

                        {{-- Rôle --}}
                        <div class="col-md-6">
                            <label for="modal_role" class="form-label small fw-bold">
                                Rôle <span class="text-danger">*</span>
                            </label>
                            <select name="role" id="modal_role" class="form-select" required>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>
                                    🛡 Admin
                                </option>
                                <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>
                                    👑 Super Admin
                                </option>
                            </select>
                        </div>

                        {{-- Mot de passe --}}
                        <div class="col-md-6">
                            <label for="modal_password" class="form-label small fw-bold">
                                Mot de passe <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password" id="modal_password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required minlength="6">
                            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Confirmation --}}
                        <div class="col-md-6">
                            <label for="modal_password_confirmation" class="form-label small fw-bold">
                                Confirmer <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password_confirmation" id="modal_password_confirmation"
                                   class="form-control" required minlength="6">
                        </div>

                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Créer l'administrateur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection