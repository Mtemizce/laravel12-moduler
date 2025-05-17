<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMigrationCommand extends Command
{
    protected $signature = 'module:migration {module} {name}';
    protected $description = 'Bir modüle migration dosyası ekler';

    public function handle(): void
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::snake($this->argument('name'));
        $basePath = app_path("Modules/{$module}/database/migrations");

        if (!File::exists($basePath)) {
            File::makeDirectory($basePath, 0755, true);
        }

        $timestamp = now()->format('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.php";

        // Laravel'e uygun şekilde create_xxx_table → xxx olarak tablo ismini al
        $tableName = Str::between($name, 'create_', '_table') ?: $name;

        $stub = <<<PHP
<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
            // alanlar buraya
            \$table->softDeletes();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
PHP;

        File::put("{$basePath}/{$filename}", $stub);

        $this->info("✅ {$module} modülüne {$filename} migration dosyası eklendi.");
    }
}
