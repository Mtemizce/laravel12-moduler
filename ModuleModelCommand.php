<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleModelCommand extends Command
{
    protected $signature = 'module:model {module} {name}';
    protected $description = 'Bir modüle yeni model ekler';

    public function handle(): void
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $path = app_path("Modules/{$module}/Models/{$name}.php");

        if (File::exists($path)) {
            $this->error("Model zaten var: {$name}");
            return;
        }

        File::put($path, "<?php\n\nnamespace App\\Modules\\{$module}\\Models;\n\nuse Illuminate\\Database\\Eloquent\\Model;\nuse Illuminate\\Database\\Eloquent\\SoftDeletes;\n\nclass {$name} extends Model\n{\n    use SoftDeletes;\n\n    protected \$guarded = [];\n}");

        $this->info("✅ {$module} modülüne {$name} modeli eklendi");
    }
}
