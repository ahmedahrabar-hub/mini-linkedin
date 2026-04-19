<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\Candidature;
use App\Events\CandidatureDeposee;
use App\Events\StatutCandidatureMis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidatureController extends Controller
{
    public function candidater(Request $request, string $offre)
    {
        $user = Auth::guard('api')->user();
        $profil = $user->profil;

        if (!$profil) {
            return response()->json([
                'message' => 'Vous devez créer un profil avant de postuler',
            ], 422);
        }

        $offre = Offre::find($offre);
        if (!$offre || !$offre->actif) {
            return response()->json([
                'message' => 'Offre non trouvée ou inactive',
            ], 404);
        }

        $existante = Candidature::where('offre_id', $offre->id)
                                ->where('profil_id', $profil->id)
                                ->first();

        if ($existante) {
            return response()->json([
                'message' => 'Vous avez déjà postulé à cette offre',
            ], 422);
        }

        $validated = $request->validate([
            'message' => 'nullable|string',
        ]);

        $candidature = Candidature::create([
            'offre_id' => $offre->id,
            'profil_id' => $profil->id,
            'message' => $validated['message'] ?? null,
            'statut' => 'en_attente',
        ]);

        event(new CandidatureDeposee($candidature));

        return response()->json([
            'data' => $candidature,
            'message' => 'Candidature soumise avec succès',
        ], 201);
    }

    public function mesCandidatures()
    {
        $user = Auth::guard('api')->user();
        $profil = $user->profil;

        if (!$profil) {
            return response()->json([
                'message' => 'Profil non trouvé',
            ], 404);
        }

        $candidatures = Candidature::with('offre')
                                   ->where('profil_id', $profil->id)
                                   ->get();

        return response()->json([
            'data' => $candidatures,
            'message' => 'Candidatures retrieved successfully',
        ], 200);
    }

    public function candidaturesOffre(string $offre)
    {
        $user = Auth::guard('api')->user();
        $offre = Offre::find($offre);

        if (!$offre) {
            return response()->json([
                'message' => 'Offre non trouvée',
            ], 404);
        }

        if ($offre->user_id !== $user->id) {
            return response()->json([
                'message' => 'Accès refusé',
            ], 403);
        }

        $candidatures = Candidature::with('profil')
                                   ->where('offre_id', $offre->id)
                                   ->get();

        return response()->json([
            'data' => $candidatures,
            'message' => 'Candidatures retrieved successfully',
        ], 200);
    }

    public function updateStatut(Request $request, string $candidature)
    {
        $user = Auth::guard('api')->user();
        $candidature = Candidature::with('offre')->find($candidature);

        if (!$candidature) {
            return response()->json([
                'message' => 'Candidature non trouvée',
            ], 404);
        }

        if ($candidature->offre->user_id !== $user->id) {
            return response()->json([
                'message' => 'Accès refusé',
            ], 403);
        }

        $validated = $request->validate([
            'statut' => 'required|in:en_attente,acceptee,refusee',
        ]);

        $ancienStatut = $candidature->statut;
        $candidature->update($validated);

        event(new StatutCandidatureMis($candidature, $ancienStatut));

        return response()->json([
            'data' => $candidature,
            'message' => 'Statut mis à jour avec succès',
        ], 200);
    }
}