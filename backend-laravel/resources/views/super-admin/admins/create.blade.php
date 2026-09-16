@extends('layouts.app')

@section('title', 'Ajouter un administrateur')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('super-admin.admins.index') }}" class="btn btn-sm btn-light mb-2">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <h4 class="fw-bold mb-1">Ajouter un administrateur</h4>
        <p class="text-muted small mb-0">Créez un nouveau compte administrateur.</p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="content-card" style="max-width: 700px;">
    <form method="POST" action="{{ route('super-admin.admins.store') }}">
        @csrf

        <div class="row g-3">

            {{-- Nom --}}
            <div class="col-md-6">
                <label for="name" class="form-label small fw-bold">Nom <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" required>
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Email --}}
            <div class="col-md-6">
                <label for="email" class="form-label small fw-bold">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required>
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Contact --}}
            <div class="col-md-6">
                <label for="contact" class="form-label small fw-bold">Contact</label>
                <input type="text" name="contact" id="contact"
                       class="form-control @error('contact') is-invalid @enderror"
                       value="{{ old('contact') }}">
                @error('contact') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Rôle --}}
            <div class="col-md-6">
                <label for="role" class="form-label small fw-bold">Rôle <span class="text-danger">*</span></label>
                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>
                        🛡 Admin
                    </option>
                    <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>
                        🛡Super Admin
                    </option>
                </select>
                @error('role') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Mot de passe --}}
            <div class="col-md-6">
                <label for="password" class="form-label small fw-bold">Mot de passe <span class="text-danger">*</span></label>
                <input type="password" name="password" id="password"
                       class="form-control @error('password') is-invalid @enderror"
                       required minlength="6">
                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Confirmation --}}
            <div class="col-md-6">
                <label for="password_confirmation" class="form-label small fw-bold">Confirmer <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control" required minlength="6">
            </div>

        </div>

        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <a href="{{ route('super-admin.admins.index') }}" class="btn btn-light">Annuler</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Créer l'administrateur
            </button>
        </div>
    </form>
</div>

@endsection