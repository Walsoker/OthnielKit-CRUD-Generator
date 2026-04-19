<?php

namespace Othnielkit\Crud\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CrudCommand extends Command
{
    protected $signature = 'make:crud {name}';
    protected $description = 'Génère un modèle et une migration pour une entité';

    public function handle()
    {
        $name = $this->argument('name');
        $table = Str::snake(Str::plural($name));

        // Générer le modèle
        $this->call('make:model', ['name' => $name]);
        
        // Générer la migration
        $this->call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ]);

        $this->info("✅ Modèle et migration pour {$name} créés avec succès !");
        $this->warn("N'oubliez pas d'ajouter vos champs dans la migration, puis lancez : php artisan migrate");
    }
}