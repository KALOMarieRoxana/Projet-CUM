<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DemandePdfController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

Auth::routes();

// =============================                                                                                                                                                                      
// ROUTE DASHBOARD (Redirection dynamique)
// =============================
Route::middleware(['auth'])->get('/dashboard', function () {
    $user = auth()->user();
    
    if (auth()->user()->isSuperAdmin()) {
        return redirect()->route('super-admin.dashboard');
    }
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
})->name('dashboard');

// =============================
// ACCUEIL
// =============================
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    $user = auth()->user();

    if (auth()->user()->isSuperAdmin()) {
        return redirect()->route('super-admin.dashboard');
    }

    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login');
});

// =============================
// PROFILE & MOT DE PASSE (Pour la modale)
// =============================
Route::middleware(['auth'])->group(function () {
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profil.password.update');

    Route::get('/demandes/{id}/pdf', [DemandePdfController::class, 'telecharger'])->name('demandes.pdf');
    Route::get('/demandes/verifier/{reference}', [DemandePdfController::class, 'verifier'])->name('demandes.verifier');
});

// =============================
// ROUTES ADMIN
// =============================
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [
            DashboardController::class,
            'adminIndex'
        ])->name('dashboard');
        // AJOUTEZ CETTE ROUTE POUR LA MISE À JOUR DES PRIX :
        Route::put('/services/prices', [
            DashboardController::class, 
            'updatePrices'
        ])->name('services.update-prices');

        Route::get('/demandes', [
            DemandeController::class,
            'index'
        ])->name('demandes');

        Route::get('/demandes/{id}', [
            DemandeController::class,
            'show'
        ])->name('demandes.show');

        Route::put('/demandes/{id}', [
            DemandeController::class,
            'update'
        ])->name('demandes.update');
        // ✅ NOUVELLES ROUTES
        Route::post('/demandes/{id}/traiter', [DemandeController::class, 'traiter'])->name('demandes.traiter');
        Route::post('/demandes/items/{id}/traiter', [DemandeController::class, 'traiterItem'])->name('demandes.traiter-item');
        Route::get('/demandes/details/{type}/{id}', [DemandeController::class, 'detailsActe'])->name('demandes.details');

    });

// =============================
// ROUTES SUPER ADMIN
// =============================
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {

        Route::get('/dashboard', [
            DashboardController::class,
            'superAdminIndex'
        ])->name('dashboard');

        Route::get('/demandes', [
            DemandeController::class,
            'index'
        ])->name('demandes');

        Route::get('/demandes/{id}', [
            DemandeController::class,
            'show'
        ])->name('demandes.show');

        Route::put('/demandes/{id}', [
            DemandeController::class,
            'update'
        ])->name('demandes.update');

        // ✅ NOUVELLES ROUTES
        Route::post('/demandes/{id}/traiter', [DemandeController::class, 'traiter'])->name('demandes.traiter');
        Route::post('/demandes/items/{id}/traiter', [DemandeController::class, 'traiterItem'])->name('demandes.traiter-item');
        Route::post('/demandes/{id}/archiver', [DemandeController::class, 'archiver'])->name('demandes.archiver');
        Route::get('/demandes/export', [DemandeController::class, 'export'])->name('demandes.export');

        // Gestion des administrateurs
        Route::get('/admins', [
            AdminController::class,
            'index'
        ])->name('admins.index');

        Route::get('/admins/create', [
            AdminController::class,
            'create'
        ])->name('admins.create');

        Route::post('/admins', [
            AdminController::class,
            'store'
        ])->name('admins.store');

        Route::get('/admins/{id}/edit', [
            AdminController::class,
            'edit'
        ])->name('admins.edit');

        Route::put('/admins/{id}', [
            AdminController::class,
            'update'
        ])->name('admins.update');

        Route::delete('/admins/{id}', [
            AdminController::class,
            'destroy'
        ])->name('admins.destroy');
    });

// =============================
// DECONNEXION
// =============================
Route::post('/logout', [
    LoginController::class,
    'logout'
])->name('logout');