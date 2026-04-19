<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Offre;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function users()
    {
        $users = User::all();
        return response()->json([
            'data' => $users,
            'message' => 'Users retrieved successfully',
        ], 200);
    }

    public function deleteUser(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non trouvé',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès',
        ], 204);
    }

    public function toggleOffre(string $id)
    {
        $offre = Offre::find($id);

        if (!$offre) {
            return response()->json([
                'message' => 'Offre non trouvée',
            ], 404);
        }

        $offre->update(['actif' => !$offre->actif]);

        return response()->json([
            'data' => $offre,
            'message' => $offre->actif ? 'Offre activée' : 'Offre désactivée',
        ], 200);
    }
}