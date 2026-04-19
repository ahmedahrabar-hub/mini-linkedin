<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OffreController extends Controller
{
    public function index(Request $request)
    {
        $query = Offre::where('actif', true)->with('recruteur');

        if ($request->has('localisation')) {
            $query->where('localisation', 'like', '%' . $request->localisation . '%');
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $offres = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'data' => $offres,
            'message' => 'Offres retrieved successfully',
        ], 200);
    }

    public function show(string $id)
    {
        $offre = Offre::with('recruteur')->find($id);

        if (!$offre) {
            return response()->json([
                'message' => 'Offre non trouvée',
            ], 404);
        }

        return response()->json([
            'data' => $offre,
            'message' => 'Offre retrieved successfully',
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'localisation' => 'nullable|string',
            'type' => 'required|in:CDI,CDD,stage',
        ]);

        $offre = Offre::create([
            ...$validated,
            'user_id' => Auth::guard('api')->id(),
        ]);

        return response()->json([
            'data' => $offre,
            'message' => 'Offre créée avec succès',
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $offre = Offre::find($id);

        if (!$offre) {
            return response()->json([
                'message' => 'Offre non trouvée',
            ], 404);
        }

        if ($offre->user_id !== Auth::guard('api')->id()) {
            return response()->json([
                'message' => 'Accès refusé',
            ], 403);
        }

        $validated = $request->validate([
            'titre' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'localisation' => 'nullable|string',
            'type' => 'sometimes|in:CDI,CDD,stage',
        ]);

        $offre->update($validated);

        return response()->json([
            'data' => $offre,
            'message' => 'Offre mise à jour avec succès',
        ], 200);
    }

    public function destroy(string $id)
    {
        $offre = Offre::find($id);

        if (!$offre) {
            return response()->json([
                'message' => 'Offre non trouvée',
            ], 404);
        }

        if ($offre->user_id !== Auth::guard('api')->id()) {
            return response()->json([
                'message' => 'Accès refusé',
            ], 403);
        }

        $offre->delete();

        return response()->json([
            'message' => 'Offre supprimée avec succès',
        ], 204);
    }
}