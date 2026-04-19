<?php

namespace App\Listeners;

use App\Events\StatutCandidatureMis;
use Illuminate\Support\Facades\Log;

class LogStatutCandidatureMis
{
    public function handle(StatutCandidatureMis $event): void
    {
        $candidature = $event->candidature;
        $ancienStatut = $event->ancienStatut;

        $message = sprintf(
            '[%s] Statut candidature mis à jour - Ancien statut: %s - Nouveau statut: %s',
            now()->format('Y-m-d H:i:s'),
            $ancienStatut,
            $candidature->statut
        );

        Log::channel('candidatures')->info($message);
    }
}