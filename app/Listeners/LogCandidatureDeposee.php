<?php

namespace App\Listeners;

use App\Events\CandidatureDeposee;
use Illuminate\Support\Facades\Log;

class LogCandidatureDeposee
{
    public function handle(CandidatureDeposee $event): void
    {
        $candidature = $event->candidature;
        $candidature->load('profil.user', 'offre');

        $message = sprintf(
            '[%s] Candidature déposée - Candidat: %s - Offre: %s',
            now()->format('Y-m-d H:i:s'),
            $candidature->profil->user->name,
            $candidature->offre->titre
        );

        Log::channel('candidatures')->info($message);
    }
}