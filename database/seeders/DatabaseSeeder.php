<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profil;
use App\Models\Competence;
use App\Models\Offre;
use App\Models\Candidature;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Créer 15 compétences
        $competences = Competence::factory(15)->create();

        // Créer 2 admins
        User::factory(2)->admin()->create();

        // Créer 5 recruteurs avec 2-3 offres chacun
        User::factory(5)->recruteur()->create()->each(function ($recruteur) {
            Offre::factory(rand(2, 3))->create([
                'user_id' => $recruteur->id,
            ]);
        });

        // Créer 10 candidats avec profil et compétences
        User::factory(10)->create()->each(function ($candidat) use ($competences) {
            $profil = Profil::factory()->create([
                'user_id' => $candidat->id,
            ]);

            // Attacher 3 compétences aléatoires avec un niveau
            $niveaux = ['débutant', 'intermédiaire', 'expert'];
            $competencesAleatoires = $competences->random(3);
            foreach ($competencesAleatoires as $competence) {
                $profil->competences()->attach($competence->id, [
                    'niveau' => $niveaux[array_rand($niveaux)],
                ]);
            }
        });
    }
}