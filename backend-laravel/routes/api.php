<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\TypeActeController;
use App\Http\Middleware\CorsLocal;
use Illuminate\Support\Facades\Route;

Route::middleware(CorsLocal::class)->group(function () {
    Route::post('/auth/inscription', [AuthController::class, 'inscription']);
    Route::post('/auth/register', [AuthController::class, 'inscription']);
    Route::post('/auth/connexion', [AuthController::class, 'connexion']);
    Route::post('/auth/login', [AuthController::class, 'connexion']);
    Route::get('/types-actes', [TypeActeController::class, 'index']);

    Route::options('/{any}', function () {
        return response()->json();
    })->where('any', '.*');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/profil', [AuthController::class, 'profil']);
        Route::get('/auth/verifier', function () {
            return response()->json([
                'authentifie' => true,
                'utilisateur' => auth()->user()
            ]);
        });
        
        Route::get('/demandes/mes-demandes', [DemandeController::class, 'mesDemandes']);
        Route::get('/demandes/{id}', [DemandeController::class, 'show']);
        Route::post('/demandes', [DemandeController::class, 'store']);
        Route::delete('/demandes/{id}/annuler', [DemandeController::class, 'annuler']);

        Route::get('/serveur/statut', function (\Illuminate\Http\Request $request) {
            return response()->json([
                'message' => "Bienvenue {$request->user()->email}, vous avez bien accès au serveur.",
                'date' => now()->toIso8601String(),
            ]);
        });

        Route::put('/auth/changer-mot-de-passe', [AuthController::class, 'changerMotDePasse']);
        Route::post('/auth/deconnecter', [AuthController::class, 'deconnecter']);

    });
});
