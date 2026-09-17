@extends('layouts.admin')

@section('title', 'Nouveau type d\'acte')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('super-admin.types-actes.index') }}" class="btn btn-sm btn-light mb-2">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <h4 class="mb-0">Nouveau type d'acte</h4>
        <small class="text-muted">Créez un nouveau type d'acte d'état civil</small>
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

<div class="content-card p-4" style="max-width: 800px;">
    <form method="POST" action="{{ route('super-admin.types-actes.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Code (unique) *</label>
                <input type="text" name="type_acte" class="form-control"
                       value="{{ old('type_acte') }}"
                       placeholder="Ex: certificat_vie" required>
                <small class="text-muted">Utilisé en interne, en minuscules et sans espaces.</small>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Nom *</label>
                <input type="text" name="nom" class="form-control"
                       value="{{ old('nom') }}"
                       placeholder="Ex: Certificat de vie" required>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Sigle</label>
                <input type="text" name="sigle" class="form-control"
                       value="{{ old('sigle') }}"
                       placeholder="Ex: CV" maxlength="10">
            </div>

            <div class="col-12"><hr><h6 class="fw-bold"> Tarifs</h6></div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Standard MG (Ar) *</label>
                <input type="number" name="montantStandardMG" class="form-control"
                       value="{{ old('montantStandardMG', 0) }}" min="0" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Express MG (Ar) *</label>
                <input type="number" name="montantExpressMG" class="form-control"
                       value="{{ old('montantExpressMG', 0) }}" min="0" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Standard FR (Ar) *</label>
                <input type="number" name="montantStandardFR" class="form-control"
                       value="{{ old('montantStandardFR', 0) }}" min="0" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Express FR (Ar) *</label>
                <input type="number" name="montantExpressFR" class="form-control"
                       value="{{ old('montantExpressFR', 0) }}" min="0" required>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <a href="{{ route('super-admin.types-actes.index') }}" class="btn btn-light">Annuler</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Créer
            </button>
        </div>
    </form>
</div>

@endsection