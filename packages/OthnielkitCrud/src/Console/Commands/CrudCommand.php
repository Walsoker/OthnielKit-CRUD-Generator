<?php

namespace Othnielkit\Crud\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CrudCommand extends Command
{
    protected $signature = 'make:crud {name}';
    protected $description = 'Génère un CRUD complet (modèle, migration, contrôleur, vues Tailwind, routes)';

    public function handle()
    {
        $name = $this->argument('name');
        $modelClass = ucfirst(Str::studly($name));
        $table = Str::snake(Str::plural($name));
        $modelVar = Str::camel($name);
        $modelVarPlural = Str::plural($modelVar);

        // 1. Migration
        $migrationFile = database_path('migrations/' . date('Y_m_d_His') . '_create_' . $table . '_table.php');
        $this->generateStub('migration.stub', $migrationFile, [
            '{{table}}' => $table,
            '{{columns}}' => "\$table->string('name');\n            \$table->text('description')->nullable();",
        ]);

        // 2. Modèle
        $modelFile = app_path("Models/{$modelClass}.php");
        $this->generateStub('model.stub', $modelFile, [
            '{{model}}' => $modelClass,
            '{{fillable}}' => "'name', 'description'",
        ]);

        // 3. Contrôleur
        $controllerFile = app_path("Http/Controllers/{$modelClass}Controller.php");
        $this->generateStub('controller.stub', $controllerFile, [
            '{{model}}' => $modelClass,
            '{{modelVar}}' => $modelVar,
            '{{modelVarPlural}}' => $modelVarPlural,
            '{{table}}' => $table,
        ]);

        // 4. Vues
        $viewsPath = resource_path("views/{$table}");
        if (!File::isDirectory($viewsPath)) {
            File::makeDirectory($viewsPath, 0755, true);
        }

        $this->generateStub('index.stub', $viewsPath . '/index.blade.php', [
            '{{modelClass}}' => $modelClass,
            '{{modelVar}}' => $modelVar,
            '{{modelVarPlural}}' => $modelVarPlural,
            '{{table}}' => $table,
        ]);

        $this->generateStub('create.stub', $viewsPath . '/create.blade.php', [
            '{{modelClass}}' => $modelClass,
            '{{modelVar}}' => $modelVar,
            '{{table}}' => $table,
        ]);

        $this->generateStub('edit.stub', $viewsPath . '/edit.blade.php', [
            '{{modelClass}}' => $modelClass,
            '{{modelVar}}' => $modelVar,
            '{{table}}' => $table,
        ]);

        // 5. Route
        $this->addRoute($table, $modelClass);

        $this->info("✅ CRUD pour {$modelClass} généré avec succès !");
        $this->warn("N'oubliez pas de lancer : php artisan migrate");
    }

    protected function generateStub($stubName, $targetPath, $replacements)
    {
        $stubPath = __DIR__ . '/../../Stubs/' . $stubName;
        if (!File::exists($stubPath)) {
            $this->error("Stub introuvable : {$stubName}");
            return;
        }
        $content = File::get($stubPath);
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        File::put($targetPath, $content);
        $this->info("Créé : " . basename($targetPath));
    }

    protected function addRoute($table, $controller)
    {
        $routePath = base_path('routes/web.php');
        $routeLine = "Route::resource('{$table}', App\Http\Controllers\\{$controller}Controller::class);";
        $content = File::get($routePath);
        if (!Str::contains($content, $routeLine)) {
            File::append($routePath, "\n{$routeLine}\n");
            $this->info("Route ajoutée dans routes/web.php");
        }
    }
}