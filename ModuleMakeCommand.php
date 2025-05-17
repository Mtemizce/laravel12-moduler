<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeCommand extends Command
{
    protected $signature = 'module:make {name}';
    protected $description = 'Yeni bir modül iskeleti oluşturur (DTO, Repository, Service dahil)';

    public function handle(): void
    {
        $name = Str::studly($this->argument('name'));
        $basePath = app_path("Modules/{$name}");

        if (File::exists($basePath)) {
            $this->error("Modül zaten mevcut: {$name}");
            return;
        }

        $paths = [
            "Http/Controllers",
            "Models",
            "Services",
            "Repositories/Contracts",
            "DTO",
            "routes",
            "database/migrations",
            "Notifications",
            "Observers",
            "Listeners",
            "resources/views",
        ];

        foreach ($paths as $path) {
            File::makeDirectory("{$basePath}/{$path}", 0755, true);
        }

        // Standart dosyaları oluştur
        $this->createBasicFiles($name, $basePath);

        // ModulesServiceProvider'a binding satırını ekle
        $this->appendBindingToModulesServiceProvider($name);

        $this->info("✅ {$name} modülü DTO, Repository, Service ile birlikte başarıyla oluşturuldu ve ModulesServiceProvider'a eklendi!");
    }

    protected function createBasicFiles(string $name, string $basePath): void
    {
        // routes/web.php
        File::put("{$basePath}/routes/web.php", "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\nuse App\\Modules\\{$name}\\Http\\Controllers\\{$name}Controller;\n\nRoute::controller({$name}Controller::class)\n    ->prefix('" . Str::kebab($name) . "')\n    ->name('" . Str::kebab($name) . ".')\n    ->group(function () {\n        Route::get('/', 'index')->name('index');\n    });");

        // routes/api.php
        File::put("{$basePath}/routes/api.php", "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::prefix('api/v1/" . Str::kebab($name) . "')\n    ->middleware('api')\n    ->group(function () {\n        Route::get('/ping', fn() => ['status' => 'ok']);\n    });");

        // views/index.blade.php
        File::put("{$basePath}/resources/views/index.blade.php", "@extends('layouts.app')\n\n@section('content')\n    <h1 class='text-2xl font-bold'>{{ \$title ?? '{$name} Modülü' }}</h1>\n@endsection");

        // Controller
        File::put("{$basePath}/Http/Controllers/{$name}Controller.php", "<?php

namespace App\\Modules\\{$name}\\Http\\Controllers;

use App\\Traits\\ModuleTrait;
use App\\Modules\\{$name}\\Services\\{$name}Service;
use App\\Core\\Services\\LogService;

class {$name}Controller
{
    use ModuleTrait;

    public function __construct(protected {$name}Service \$service) {}

    public function index()
    {
        LogService::activity('{$name}Controller index görüntülendi');
        return \$this->view('index');
    }
}");

        // Model
        File::put("{$basePath}/Models/{$name}.php", "<?php

namespace App\\Modules\\{$name}\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\SoftDeletes;

class {$name} extends Model
{
    use SoftDeletes;

    protected \$guarded = [];
}");

        // Migration
        $datePrefix = now()->format('Y_m_d_His');
        $migrationFile = "{$basePath}/database/migrations/{$datePrefix}_create_" . Str::snake(Str::plural($name)) . "_table.php";

        File::put($migrationFile, "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration {\n    public function up(): void\n    {\n        Schema::create('" . Str::snake(Str::plural($name)) . "', function (Blueprint \$table) {\n            \$table->id();\n            // Alanlar buraya\n            \$table->softDeletes();\n            \$table->timestamps();\n        });\n    }\n\n    public function down(): void\n    {\n        Schema::dropIfExists('" . Str::snake(Str::plural($name)) . "');\n    }\n};");

        // DTO
        File::put("{$basePath}/DTO/{$name}DTO.php", "<?php

namespace App\\Modules\\{$name}\\DTO;

class {$name}DTO
{
    // DTO özellikleri buraya
}");

        // Repository Interface
        File::put("{$basePath}/Repositories/Contracts/{$name}RepositoryInterface.php", "<?php

namespace App\\Modules\\{$name}\\Repositories\\Contracts;

interface {$name}RepositoryInterface
{
    // CRUD methodları buraya
}");

        // Repository Implementation
        File::put("{$basePath}/Repositories/{$name}Repository.php", "<?php

namespace App\\Modules\\{$name}\\Repositories;

use App\\Modules\\{$name}\\Repositories\\Contracts\\{$name}RepositoryInterface;

class {$name}Repository implements {$name}RepositoryInterface
{
    // Method implementasyonları
}");

        // Service
        File::put("{$basePath}/Services/{$name}Service.php", "<?php

namespace App\\Modules\\{$name}\\Services;

use App\\Modules\\{$name}\\Repositories\\Contracts\\{$name}RepositoryInterface;

class {$name}Service
{
    public function __construct(protected {$name}RepositoryInterface \$repository)
    {
    }
}");
    }

    protected function appendBindingToModulesServiceProvider(string $name): void
    {
        $providerPath = app_path('Support/ModulesServiceProvider.php');

        if (!File::exists($providerPath)) {
            $this->error('ModulesServiceProvider bulunamadı.');
            return;
        }

        $binding = "\n        \$this->app->bind(\n            \\App\\Modules\\{$name}\\Repositories\\Contracts\\{$name}RepositoryInterface::class,\n            \\App\\Modules\\{$name}\\Repositories\\{$name}Repository::class\n        );\n";

        $content = File::get($providerPath);

        if (str_contains($content, "\$this->app->bind(")) {
            $content = preg_replace('/(public function boot\(\): void\s*\{\s*)/', "$1$binding", $content);
        } else {
            $content = str_replace('public function boot(): void', "public function boot(): void\n    {{$binding}", $content);
        }

        File::put($providerPath, $content);
    }
}
