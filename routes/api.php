<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\CandidatureController;
use App\Http\Controllers\AdminController;

// Routes publiques
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Routes protégées
Route::middleware('auth:api')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    // Offres (tout le monde peut voir)
    Route::get('offres', [OffreController::class, 'index']);
    Route::get('offres/{offre}', [OffreController::class, 'show']);

    // Profil (candidats seulement)
    Route::middleware('checkrole:candidat')->group(function () {
        Route::post('profil', [ProfilController::class, 'store']);
        Route::get('profil', [ProfilController::class, 'show']);
        Route::put('profil', [ProfilController::class, 'update']);
        Route::post('profil/competences', [ProfilController::class, 'addCompetence']);
        Route::delete('profil/competences/{competence}', [ProfilController::class, 'removeCompetence']);
        Route::post('offres/{offre}/candidater', [CandidatureController::class, 'candidater']);
        Route::get('mes-candidatures', [CandidatureController::class, 'mesCandidatures']);
    });

    // Offres (recruteurs seulement)
    Route::middleware('checkrole:recruteur')->group(function () {
        Route::post('offres', [OffreController::class, 'store']);
        Route::put('offres/{offre}', [OffreController::class, 'update']);
        Route::delete('offres/{offre}', [OffreController::class, 'destroy']);
        Route::get('offres/{offre}/candidatures', [CandidatureController::class, 'candidaturesOffre']);
        Route::patch('candidatures/{candidature}/statut', [CandidatureController::class, 'updateStatut']);
    });

    // Admin seulement
    Route::middleware('checkrole:admin')->prefix('admin')->group(function () {
        Route::get('users', [AdminController::class, 'users']);
        Route::delete('users/{user}', [AdminController::class, 'deleteUser']);
        Route::patch('offres/{offre}', [AdminController::class, 'toggleOffre']);
    });
});