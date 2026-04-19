<?php

namespace App\Http\Controllers;

use App\Models\Profil;
use App\Models\Competence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfilController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();

        if ($user->profil) {
            return response()->json([
                'message' => 'Profil déjà créé',
            ], 422);
        }

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'localisation' => 'nullable|string',
            'disponible' => 'nullable|boolean',
        ]);

        $profil = Profil::create([
            ...$validated,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'data' => $profil,
            'message' => 'Profil créé avec succès',
        ], 201);
    }

    public function show()
    {
        $user = Auth::guard('api')->user();
        $profil = $user->profil;

        if (!$profil) {
            return response()->json([
                'message' => 'Profil non trouvé',
            ], 404);
        }

        $profil->load('competences');

        return response()->json([
            'data' => $profil,
            'message' => 'Profil retrieved successfully',
        ], 200);
    }

    public function update(Request $request)
    {
        $user = Auth::guard('api')->user();
        $profil = $user->profil;

        if (!$profil) {
            return response()->json([
                'message' => 'Profil non trouvé',
            ], 404);
        }

        $validated = $request->validate([
            'titre' => 'sometimes|string|max:255',
            'bio' => 'nullable|string',
            'localisation' => 'nullable|string',
            'disponible' => 'nullable|boolean',
        ]);

        $profil->update($validated);

        return response()->json([
            'data' => $profil,
            'message' => 'Profil mis à jour avec succès',
        ], 200);
    }

    public function addCompetence(Request $request)
    {
        $user = Auth::guard('api')->user();
        $profil = $user->profil;

        if (!$profil) {
            return response()->json([
                'message' => 'Profil non trouvé',
            ], 404);
        }

        $validated = $request->validate([
            'competence_id' => 'required|exists:competences,id',
            'niveau' => 'required|in:débutant,intermédiaire,expert',
        ]);

        $profil->competences()->syncWithoutDetaching([
            $validated['competence_id'] => ['niveau' => $validated['niveau']]
        ]);

        return response()->json([
            'message' => 'Compétence ajoutée avec succès',
        ], 200);
    }

    public function removeCompetence(string $competence)
    {
        $user = Auth::guard('api')->user();
        $profil = $user->profil;

        if (!$profil) {
            return response()->json([
                'message' => 'Profil non trouvé',
            ], 404);
        }

        $profil->competences()->detach($competence);

        return response()->json([
            'message' => 'Compétence retirée avec succès',
        ], 200);
    }
}