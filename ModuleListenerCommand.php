<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


class ModuleListenerCommand extends Command
{
    protected $signature = 'module:listener {module} {name}';
    protected $description = 'Bir modüle listener sınıfı ekler';

    public function handle(): void
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $path = app_path("Modules/{$module}/Listeners/{$name}.php");

        if (File::exists($path)) {
            $this->error("Listener zaten var: {$name}");
            return;
        }

        File::put($path, "<?php\n\nnamespace App\\Modules\\{$module}\\Listeners;\n\nclass {$name}\n{\n    public function handle(\$event): void\n    {\n        // olay çözümü\n    }\n}");

        $this->info("✅ {$module} modülüne {$name} listener eklendi");
    }
}
