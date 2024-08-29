<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class CleanExpiredTokens extends Command
{
    protected $signature = 'tokens:clean';
    protected $description = 'Supprime automatiquement les tokens expirés';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $now = Carbon::now();
        $deleted = PersonalAccessToken::where('expires_at', '<', $now)->delete();

        $this->info("$deleted token(s) expiré(s) supprimé(s).");
    }
}
