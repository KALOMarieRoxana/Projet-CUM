@extends('layouts.admin')

@section('title', 'Gestion des Administrateurs')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestion des Administrateurs</h2>
        <a href="{{ route('super-admin.admins.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Ajouter un administrateur
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Date de création</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td>{{ $admin->id }}</td>
                                <td>{{ $admin->name }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>
                                    <span class="badge {{ $admin->role === 'super_admin' ? 'bg-danger' : 'bg-primary' }}">
                                        {{ ucfirst($admin->role) }}
                                    </span>
                                </td>
                                <td>{{ $admin->created_at ? $admin->created_at->format('d/m/Y') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Aucun administrateur trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection