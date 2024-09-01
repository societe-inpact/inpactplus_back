<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permet de créer un service dans Laravel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $servicePath = app_path('Services');
        $fileName = Str::studly($name) . '.php';
        $filePath = $servicePath . '/' . $fileName;

        // Création du répertoire si nécessaire
        if (!File::isDirectory($servicePath)) {
            File::makeDirectory($servicePath, 0755, true);
        }

        // Contenu du service
        $content = <<<PHP
        <?php

        namespace App\Services;

        class $name
        {
            // Implémentation du service
        }
        PHP;

        // Création du fichier
        File::put($filePath, $content);

        $this->info("Le service '$name' a bien été créé.");
    }
}
