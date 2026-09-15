@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>Gestion des prix des actes</h2>

    @foreach($types as $type)
    <div class="card mb-3">
        <div class="card-header">
            <strong>{{ $type->nom }}</strong>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.types-actes.update', $type->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-3">
                        <label>Standard MG (Ar)</label>
                        <input type="number" name="montantStandardMG" value="{{ $type->montantStandardMG }}" class="form-control" step="100">
                    </div>
                    <div class="col-md-3">
                        <label>Express MG (Ar)</label>
                        <input type="number" name="montantExpressMG" value="{{ $type->montantExpressMG }}" class="form-control" step="100">
                    </div>
                    <div class="col-md-3">
                        <label>Standard FR (Ar)</label>
                        <input type="number" name="montantStandardFR" value="{{ $type->montantStandardFR }}" class="form-control" step="100">
                    </div>
                    <div class="col-md-3">
                        <label>Express FR (Ar)</label>
                        <input type="number" name="montantExpressFR" value="{{ $type->montantExpressFR }}" class="form-control" step="100">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">💾 Enregistrer</button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endsection