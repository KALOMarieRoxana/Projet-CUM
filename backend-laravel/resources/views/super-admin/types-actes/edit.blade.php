@extends('layouts.admin')

@section('title', 'Modifier un type d\'acte')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('super-admin.types-actes.index') }}" class="btn btn-sm btn-light mb-2">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <h4 class="mb-0">Modifier : {{ $type->nom }}</h4>
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

<div class="content-card p-4 mb-4" style="max-width: 800px;">
    <form method="POST" action="{{ route('super-admin.types-actes.update', $type->id) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Code</label>
                <input type="text" class="form-control" value="{{ $type->type_acte }}" disabled>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Nom *</label>
                <input type="text" name="nom" class="form-control"
                       value="{{ old('nom', $type->nom) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Sigle</label>
                <input type="text" name="sigle" class="form-control"
                       value="{{ old('sigle', $type->sigle) }}" maxlength="10">
            </div>

            <div class="col-12"><hr><h6 class="fw-bold">💰 Tarifs</h6></div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Standard MG *</label>
                <input type="number" name="montantStandardMG" class="form-control"
                       value="{{ old('montantStandardMG', $type->montantStandardMG) }}" min="0" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Express MG *</label>
                <input type="number" name="montantExpressMG" class="form-control"
                       value="{{ old('montantExpressMG', $type->montantExpressMG) }}" min="0" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Standard FR *</label>
                <input type="number" name="montantStandardFR" class="form-control"
                       value="{{ old('montantStandardFR', $type->montantStandardFR) }}" min="0" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Express FR *</label>
                <input type="number" name="montantExpressFR" class="form-control"
                       value="{{ old('montantExpressFR', $type->montantExpressFR) }}" min="0" required>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <a href="{{ route('super-admin.types-actes.index') }}" class="btn btn-light">Annuler</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Enregistrer
            </button>
        </div>
    </form>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- SUPPLÉMENTS                                                 --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="content-card p-4" style="max-width: 800px;">
    <h5 class="fw-bold mb-3">📎 Suppléments / Sous-types</h5>

    @if($type->supplements->count() > 0)
        <table class="table table-sm">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Prix Standard MG</th>
                    <th>Prix Express MG</th>
                    <th>Prix Standard FR</th>
                    <th>Prix Express FR</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($type->supplements as $supp)
                    <tr>
                        <td>{{ $supp->nom }}</td>
                        <td>{{ number_format($supp->prix_standard_mg ?? 0, 0, ',', ' ') }} Ar</td>
                        <td>{{ number_format($supp->prix_express_mg ?? 0, 0, ',', ' ') }} Ar</td>
                        <td>{{ number_format($supp->prix_standard_fr ?? 0, 0, ',', ' ') }} Ar</td>
                        <td>{{ number_format($supp->prix_express_fr ?? 0, 0, ',', ' ') }} Ar</td>
                        <td>
                            <form action="{{ route('super-admin.types-actes.supplements.destroy', $supp->id) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer ce supplément ?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="text-muted text-center py-3">Aucun supplément</div>
    @endif

    <hr class="my-4">

    <h6 class="fw-bold mb-3">➕ Ajouter un supplément</h6>
    <form method="POST" action="{{ route('super-admin.types-actes.supplements.store', $type->id) }}">
        @csrf
        <div class="row g-2">
            <div class="col-md-6">
                <input type="text" name="nom" class="form-control" placeholder="Nom (ex: Bulletin de naissance)" required>
            </div>
            <div class="col-md-6">
                <input type="text" name="description" class="form-control" placeholder="Description (optionnel)">
            </div>
            <div class="col-md-3">
                <input type="number" name="prix_standard_mg" class="form-control" placeholder="Std MG (Ar)" min="0" value="0" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="prix_express_mg" class="form-control" placeholder="Exp MG (Ar)" min="0" value="0" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="prix_standard_fr" class="form-control" placeholder="Std FR (Ar)" min="0" value="0" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="prix_express_fr" class="form-control" placeholder="Exp FR (Ar)" min="0" value="0" required>
            </div>
        </div>
        <button type="submit" class="btn btn-success mt-3">
            <i class="bi bi-plus-lg"></i> Ajouter le supplément
        </button>
    </form>
</div>

@endsection