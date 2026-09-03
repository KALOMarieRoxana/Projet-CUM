@extends('layouts.app')

@section('title', 'Gestion des Administrateurs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Gestion des Administrateurs</h4>
        <p class="text-muted small">Ajoutez ou supprimez des comptes administrateurs.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAjoutAdmin">
        <i class="bi bi-plus-lg me-1"></i> Ajouter un administrateur
    </button>
</div>

<div class="content-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Date d'ajout</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins ?? [] as $admin)
                    <tr>
                        <td class="fw-semibold">{{ $admin->name }}</td>
                        <td>{{ $admin->email }}</td>
                        <td>{{ $admin->contact }}</td>
                        <td>{{ $admin->created_at->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <form action="{{ route('super-admin.admins.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('Supprimer cet administrateur ?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Aucun administrateur trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modale : Ajouter Admin -->
<div class="modal fade" id="modalAjoutAdmin" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('super-admin.admins.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Ajouter un administrateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom complet</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact</label>
                        <input type="text" name="contact" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer le compte</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection