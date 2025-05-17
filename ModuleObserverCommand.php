<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


class ModuleObserverCommand extends Command
{
    protected $signature = 'module:observer {module} {name}';
    protected $description = 'Bir modüle observer sınıfı ekler';

    public function handle(): void
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $path = app_path("Modules/{$module}/Observers/{$name}.php");

        if (File::exists($path)) {
            $this->error("Observer zaten var: {$name}");
            return;
        }

        File::put($path, "<?php\n\nnamespace App\\Modules\\{$module}\\Observers;\n\nclass {$name}\n{\n    // created, updated gibi metotlar buraya\n}");

        $this->info("✅ {$module} modülüne {$name} observer eklendi");
    }
}
