<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;



class ModuleControllerCommand extends Command
{
    protected $signature = 'module:controller {module} {name}';
    protected $description = 'Bir modüle yeni controller ekler';

    public function handle(): void
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $path = app_path("Modules/{$module}/Http/Controllers/{$name}.php");

        if (File::exists($path)) {
            $this->error("Controller zaten var: {$name}");
            return;
        }

        File::put($path, "<?php\n\nnamespace App\\Modules\\{$module}\\Http\\Controllers;\n\nuse App\\Traits\\ModuleTrait;\nuse Illuminate\\Http\\Request;\nuse App\\Core\\Services\\LogService;\n\nclass {$name}\n{\n    use ModuleTrait;\n\n    public function index()\n    {\n        LogService::activity('{$name} Controller index');\n        return \$this->view('index');\n    }\n}");

        $this->info("✅ {$module} modülüne {$name} controller eklendi");
    }
}
