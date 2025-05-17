<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleNotificationCommand extends Command
{
    protected $signature = 'module:notification {module} {name}';
    protected $description = 'Bir modüle notification sınıfı ekler';

    public function handle(): void
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $path = app_path("Modules/{$module}/Notifications/{$name}.php");

        if (File::exists($path)) {
            $this->error("Notification zaten var: {$name}");
            return;
        }

        File::put($path, "<?php\n\nnamespace App\\Modules\\{$module}\\Notifications;\n\nuse Illuminate\\Bus\\Queueable;\nuse Illuminate\\Notifications\\Notification;\nuse Illuminate\\Notifications\\Messages\\MailMessage;\n\nclass {$name} extends Notification\n{\n    use Queueable;\n\n    public function via(\$notifiable): array\n    {\n        return ['mail'];\n    }\n\n    public function toMail(\$notifiable): MailMessage\n    {\n        return (new MailMessage)->line('Bildirim: {$name}');\n    }\n}");

        $this->info("✅ {$module} modülüne {$name} notification eklendi");
    }
}
