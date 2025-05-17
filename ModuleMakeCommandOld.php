<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeCommandOld extends Command
{
    protected $signature = 'module:make {name}';
    protected $description = 'Yeni bir modül iskeleti oluşturur';

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

        // routes/web.php (group, controller, prefix)
        File::put("{$basePath}/routes/web.php", "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\nuse App\\Modules\\{$name}\\Http\\Controllers\\{$name}Controller;\n\nRoute::controller({$name}Controller::class)\n    ->prefix('" . Str::kebab($name) . "')\n    ->name('" . Str::kebab($name) . ".')\n    ->group(function () {\n        Route::get('/', 'index')->name('index');\n    });");

        // routes/api.php
        File::put("{$basePath}/routes/api.php", "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::prefix('api/v1/" . Str::kebab($name) . "')\n    ->middleware('api')\n    ->group(function () {\n        Route::get('/ping', fn() => ['status' => 'ok']);\n    });");

        // views/index.blade.php
        File::put("{$basePath}/resources/views/index.blade.php", "@extends('layouts.app')\n\n@section('content')\n    <h1 class='text-2xl font-bold'>{{ \$title ?? '{$name} Modülü' }}</h1>\n@endsection");

        // Controller örneği + model + logservice
        File::put("{$basePath}/Http/Controllers/{$name}Controller.php",
            "<?php

namespace App\\Modules\\{$name}\\Http\\Controllers;

use App\\Traits\\ModuleTrait;
use App\\Modules\\{$name}\\Models\\{$name};
use App\\Core\\Services\\LogService;

class {$name}Controller
{
    use ModuleTrait;

    public function index()
    {
        LogService::activity('{$name}Controller index görüntülendi');
        return \$this->view('index');
    }
}"
        );

        // Model örneği (SoftDeletes dahil)
        File::put("{$basePath}/Models/{$name}.php",
            "<?php

namespace App\\Modules\\{$name}\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\SoftDeletes;

class {$name} extends Model
{
    use SoftDeletes;

    protected \$guarded = [];
}");

        // Migration dosyası (id + softDeletes dahil, diğer alanlar boş)
        $datePrefix = now()->format('Y_m_d_His');
        $migrationFile = "{$basePath}/database/migrations/{$datePrefix}_create_" . Str::snake(Str::plural($name)) . "_table.php";

        File::put($migrationFile, "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration {\n    public function up(): void\n    {\n        Schema::create('" . Str::snake(Str::plural($name)) . "', function (Blueprint \$table) {\n            \$table->id();\n            // Alanlar buraya\n            \$table->softDeletes();\n            \$table->timestamps();\n        });\n    }\n\n    public function down(): void\n    {\n        Schema::dropIfExists('" . Str::snake(Str::plural($name)) . "');\n    }\n};");

        // Notifications, Listeners, Observers örnek dosyalar
        File::put("{$basePath}/Notifications/{$name}Notification.php",
            "<?php

namespace App\\Modules\\{$name}\\Notifications;

use Illuminate\\Bus\\Queueable;
use Illuminate\\Notifications\\Notification;

class {$name}Notification extends Notification
{
    use Queueable;

    public function via(\$notifiable): array
    {
        return ['mail'];
    }

    public function toMail(\$notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
                    ->line('{$name} modülü bildirimi.');
    }
}"
        );

        File::put("{$basePath}/Observers/{$name}Observer.php",
            "<?php

namespace App\\Modules\\{$name}\\Observers;

class {$name}Observer
{
    // created, updated gibi metotlar
}"
        );

        File::put("{$basePath}/Listeners/{$name}Listener.php",
            "<?php

namespace App\\Modules\\{$name}\\Listeners;

class {$name}Listener
{
    public function handle(\$event): void
    {
        // olay çözümü
    }
}"
        );

        $this->info("✅ {$name} modülü başarıyla oluşturuldu!");
    }
}
