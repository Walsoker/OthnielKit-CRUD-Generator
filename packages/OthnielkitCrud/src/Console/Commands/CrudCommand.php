<?php

namespace Othnielkit\Crud\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CrudCommand extends Command
{
    protected $signature = 'make:crud {name} {fields?}';
    protected $description = 'Génère un CRUD complet avec champs personnalisables (ex: name:string, price:float)';

    public function handle()
    {
        $name = $this->argument('name');
        $fieldsInput = $this->argument('fields');

        if ($fieldsInput) {
            $fields = $this->parseFields($fieldsInput);
        } else {
            $fields = $this->askForFieldsInteractively();
            if (empty($fields)) {
                $this->error("Aucun champ défini. Génération annulée.");
                return 1;
            }
        }

        $modelClass = ucfirst(Str::studly($name));
        $table = Str::snake(Str::plural($name));
        $modelVar = Str::camel($name);
        $modelVarPlural = Str::plural($modelVar);

        // Génération des chaînes dynamiques
        $columnsPhp = $this->generateMigrationColumns($fields);
        $fillableArray = $this->generateFillableArray($fields);
        $validationRules = $this->generateValidationRules($fields);
        $fieldsArray = $this->generateFieldsArray($fields); // pour le contrôleur

        // 1. Migration
        $migrationFile = database_path('migrations/' . date('Y_m_d_His') . '_create_' . $table . '_table.php');
        $this->generateStub('migration.stub', $migrationFile, [
            '{{table}}' => $table,
            '{{columns}}' => $columnsPhp,
        ]);

        // 2. Modèle
        $modelFile = app_path("Models/{$modelClass}.php");
        $this->generateStub('model.stub', $modelFile, [
            '{{model}}' => $modelClass,
            '{{fillable}}' => $fillableArray,
        ]);

        // 3. Contrôleur
        $controllerFile = app_path("Http/Controllers/{$modelClass}Controller.php");
        $this->generateStub('controller.stub', $controllerFile, [
            '{{model}}' => $modelClass,
            '{{modelVar}}' => $modelVar,
            '{{modelVarPlural}}' => $modelVarPlural,
            '{{table}}' => $table,
            '{{validationRules}}' => $validationRules,
            '{{fieldsArray}}' => $fieldsArray,
        ]);

        // 4. Vues
        $viewsPath = resource_path("views/{$table}");
        if (!File::isDirectory($viewsPath)) {
            File::makeDirectory($viewsPath, 0755, true);
        }

        // Index view
        $this->generateStub('index.stub', $viewsPath . '/index.blade.php', [
            '{{modelClass}}' => $modelClass,
            '{{modelVar}}' => $modelVar,
            '{{modelVarPlural}}' => $modelVarPlural,
            '{{table}}' => $table,
            '{{fields}}' => $fields,
        ]);

        // Create view
        $this->generateStub('create.stub', $viewsPath . '/create.blade.php', [
            '{{modelClass}}' => $modelClass,
            '{{modelVar}}' => $modelVar,
            '{{table}}' => $table,
            '{{fields}}' => $fields,
        ]);

        // Edit view
        $this->generateStub('edit.stub', $viewsPath . '/edit.blade.php', [
            '{{modelClass}}' => $modelClass,
            '{{modelVar}}' => $modelVar,
            '{{table}}' => $table,
            '{{fields}}' => $fields,
        ]);

        // 5. Route
        $this->addRoute($table, $modelClass);

        // 6. Base SQLite et migration
        $this->setupDatabaseAndMigrate();

        $this->info("✅ CRUD pour {$modelClass} généré avec succès !");
    }

    protected function askForFieldsInteractively()
    {
        $fields = [];
        $this->info("Mode interactif : ajoutez les champs un par un. Laissez vide pour terminer.");

        do {
            $this->line("---");
            $name = $this->ask("Nom du champ (ex: title, price, is_active)");
            if (empty($name)) {
                if (empty($fields)) {
                    $this->error("Vous devez ajouter au moins un champ.");
                    continue;
                }
                break;
            }

            $type = $this->choice(
                "Type du champ '{$name}'",
                ['string', 'text', 'integer', 'float', 'boolean', 'date'],
                0
            );

            $nullable = $this->confirm("Le champ peut-il être nul (nullable) ?", false);

            $fields[] = [
                'name' => $name,
                'type' => $type,
                'nullable' => $nullable,
            ];

            $this->info("Champ '{$name}' ajouté avec succès.");
        } while ($this->confirm("Ajouter un autre champ ?", true));

        return $fields;
    }

    protected function parseFields($input)
    {
        $fields = [];
        $parts = explode(',', $input);
        foreach ($parts as $part) {
            $part = trim($part);
            [$name, $type] = explode(':', $part);
            $nullable = false;
            if (Str::endsWith($name, '?')) {
                $name = substr($name, 0, -1);
                $nullable = true;
            }
            $fields[] = [
                'name' => $name,
                'type' => $type,
                'nullable' => $nullable,
            ];
        }
        return $fields;
    }

    protected function generateMigrationColumns($fields)
    {
        $lines = [];
        foreach ($fields as $field) {
            $type = $field['type'];
            $name = $field['name'];
            $nullable = $field['nullable'] ? '->nullable()' : '';
            switch ($type) {
                case 'string':
                    $lines[] = "\$table->string('{$name}'){$nullable};";
                    break;
                case 'text':
                    $lines[] = "\$table->text('{$name}'){$nullable};";
                    break;
                case 'integer':
                    $lines[] = "\$table->integer('{$name}'){$nullable};";
                    break;
                case 'float':
                    $lines[] = "\$table->float('{$name}'){$nullable};";
                    break;
                case 'boolean':
                    $lines[] = "\$table->boolean('{$name}'){$nullable};";
                    break;
                case 'date':
                    $lines[] = "\$table->date('{$name}'){$nullable};";
                    break;
                default:
                    $lines[] = "\$table->string('{$name}'){$nullable};";
            }
        }
        return implode("\n            ", $lines);
    }

    protected function generateFillableArray($fields)
    {
        $names = array_map(function ($field) {
            return "'" . $field['name'] . "'";
        }, $fields);
        return implode(', ', $names);
    }

    protected function generateValidationRules($fields)
    {
        $rules = [];
        foreach ($fields as $field) {
            $rule = '';
            if (!$field['nullable']) {
                $rule .= 'required|';
            } else {
                $rule .= 'nullable|';
            }
            switch ($field['type']) {
                case 'string':
                    $rule .= 'string|max:255';
                    break;
                case 'text':
                    $rule .= 'string';
                    break;
                case 'integer':
                    $rule .= 'integer';
                    break;
                case 'float':
                    $rule .= 'numeric';
                    break;
                case 'boolean':
                    $rule .= 'boolean';
                    break;
                case 'date':
                    $rule .= 'date';
                    break;
                default:
                    $rule .= 'string|max:255';
            }
            $rules[] = "'{$field['name']}' => '{$rule}'";
        }
        return "[\n            " . implode(",\n            ", $rules) . "\n        ]";
    }

    protected function generateFieldsArray($fields)
    {
        return var_export($fields, true);
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
            if (is_array($replace)) {
                continue;
            }
            $content = str_replace($search, $replace, $content);
        }
        // Traitement spécial pour {{fields}} dans les vues
        if (isset($replacements['{{fields}}'])) {
            $fieldsPhp = var_export($replacements['{{fields}}'], true);
            $content = str_replace('{{fields}}', $fieldsPhp, $content);
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

    protected function setupDatabaseAndMigrate()
    {
        if (!env('DB_CONNECTION') || env('DB_CONNECTION') === 'sqlite') {
            $this->setupSqlite();
        }
        $this->call('migrate');
    }

    protected function setupSqlite()
    {
        $databasePath = database_path('database.sqlite');
        if (!File::exists($databasePath)) {
            File::put($databasePath, '');
            $this->info("✅ Fichier SQLite créé : {$databasePath}");
        }
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $envContent = File::get($envPath);
            if (preg_match('/^DB_CONNECTION=/m', $envContent)) {
                $envContent = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=sqlite', $envContent);
            } else {
                $envContent .= "\nDB_CONNECTION=sqlite\n";
            }
            if (preg_match('/^DB_DATABASE=/m', $envContent)) {
                $envContent = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=' . $databasePath, $envContent);
            } else {
                $envContent .= "DB_DATABASE={$databasePath}\n";
            }
            File::put($envPath, $envContent);
            $this->info("✅ Configuration SQLITE ajoutée dans .env");
        } else {
            File::put($envPath, "APP_ENV=local\nDB_CONNECTION=sqlite\nDB_DATABASE={$databasePath}\n");
            $this->info("✅ Fichier .env créé avec SQLite");
        }
    }
}