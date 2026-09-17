<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DemandePdfController;
use App\Http\Controllers\CitoyenController;
use App\Http\Controllers\TypeActeAdminController;
use App\Http\Controllers\NotificationAdminController;
use App\Http\Controllers\PaiementController;
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
        Route::get('/archives', [DemandeController::class, 'archives'])->name('archives');
        Route::post('/archives/{id}/restaurer', [DemandeController::class, 'restaurer'])->name('archives.restaurer');
        
        Route::get('/statistiques', [DashboardController::class, 'statistiques'])->name('statistiques');

         // ✅ Notifications Admin
        Route::get('/notifications/compteur', [NotificationAdminController::class, 'compteur'])->name('notifications.compteur');
        Route::get('/notifications', [NotificationAdminController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/marquer-lue', [NotificationAdminController::class, 'marquerLue'])->name('notifications.marquer-lue');
        Route::post('/notifications/marquer-toutes-lues', [NotificationAdminController::class, 'marquerToutesLues'])->name('notifications.marquer-toutes-lues');
    

       Route::get('/paiements/liste', [PaiementController::class, 'liste'])->name('paiements.liste');
        Route::get('/paiements', [PaiementController::class, 'index'])->name('paiements.index');
        Route::post('/paiements/{id}/marquer-paye', [PaiementController::class, 'marquerPaye'])->name('paiements.marquer-paye');
        Route::post('/paiements/{id}/annuler', [PaiementController::class, 'annuler'])->name('paiements.annuler');
        // ✅ NOUVELLES ROUTES
        Route::post('/demandes/{id}/traiter', [DemandeController::class, 'traiter'])->name('demandes.traiter');
        Route::post('/demandes/items/{id}/traiter', [DemandeController::class, 'traiterItem'])->name('demandes.traiter-item');
        Route::get('/demandes/details/{type}/{id}', [DemandeController::class, 'detailsActe'])->name('demandes.details');
        Route::get('/types-actes', [TypeActeController::class, 'index'])->name('types-actes.index');
        Route::put('/types-actes/{id}', [TypeActeController::class, 'update'])->name('types-actes.update');
        Route::get('/types-actes', function() {
            $types = \App\Models\TypeActe::all();
            return view('admin.types-actes', compact('types'));
        })->name('types-actes.index');
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
        Route::get('/archives', [DemandeController::class, 'archives'])->name('archives');
        Route::post('/archives/{id}/restaurer', [DemandeController::class, 'restaurer'])->name('archives.restaurer');

         // ✅ CITOYENS
        Route::get('/citoyens', [CitoyenController::class, 'index'])->name('citoyens.index');
        Route::get('/citoyens/{id}', [CitoyenController::class, 'show'])->name('citoyens.show');
        Route::post('/citoyens/{id}/desactiver', [CitoyenController::class, 'desactiver'])->name('citoyens.desactiver');
        Route::post('/citoyens/{id}/reactiver', [CitoyenController::class, 'reactiver'])->name('citoyens.reactiver');
        Route::delete('/citoyens/{id}', [CitoyenController::class, 'destroy'])->name('citoyens.destroy');
    
         // ✅ Notifications Super Admin
        Route::get('/notifications/compteur', [NotificationAdminController::class, 'compteur'])->name('notifications.compteur');
        Route::get('/notifications', [NotificationAdminController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/marquer-lue', [NotificationAdminController::class, 'marquerLue'])->name('notifications.marquer-lue');
        Route::post('/notifications/marquer-toutes-lues', [NotificationAdminController::class, 'marquerToutesLues'])->name('notifications.marquer-toutes-lues');

        // paiements
        Route::get('/paiements/liste', [PaiementController::class, 'liste'])->name('paiements.liste');
        Route::get('/paiements', [PaiementController::class, 'index'])->name('paiements.index');
        Route::post('/paiements/{id}/marquer-paye', [PaiementController::class, 'marquerPaye'])->name('paiements.marquer-paye');
        Route::post('/paiements/{id}/annuler', [PaiementController::class, 'annuler'])->name('paiements.annuler');

        // ✅ NOUVELLES ROUTES
        Route::post('/demandes/{id}/traiter', [DemandeController::class, 'traiter'])->name('demandes.traiter');
        Route::post('/demandes/items/{id}/traiter', [DemandeController::class, 'traiterItem'])->name('demandes.traiter-item');
        Route::post('/demandes/{id}/archiver', [DemandeController::class, 'archiver'])->name('demandes.archiver');
        Route::get('/demandes/export', [DemandeController::class, 'export'])->name('demandes.export');
        Route::get('/statistiques', [DashboardController::class, 'statistiques'])->name('statistiques');

         // ✅ TYPES D'ACTES
        Route::get('/types-actes', [TypeActeAdminController::class, 'index'])->name('types-actes.index');
        Route::get('/types-actes/create', [TypeActeAdminController::class, 'create'])->name('types-actes.create');
        Route::post('/types-actes', [TypeActeAdminController::class, 'store'])->name('types-actes.store');
        Route::get('/types-actes/{id}/edit', [TypeActeAdminController::class, 'edit'])->name('types-actes.edit');
        Route::put('/types-actes/{id}', [TypeActeAdminController::class, 'update'])->name('types-actes.update');
        Route::delete('/types-actes/{id}', [TypeActeAdminController::class, 'destroy'])->name('types-actes.destroy');
        Route::post('/types-actes/{id}/toggle-actif', [TypeActeAdminController::class, 'toggleActif'])->name('types-actes.toggle-actif');

        // ✅ SUPPLÉMENTS
        Route::post('/types-actes/{id}/supplements', [TypeActeAdminController::class, 'storeSupplement'])->name('types-actes.supplements.store');
        Route::delete('/types-actes/supplements/{id}', [TypeActeAdminController::class, 'destroySupplement'])->name('types-actes.supplements.destroy');
    

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