<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MakeUser extends Command
{
    protected $signature = 'make:user';
    protected $description = 'Normal kullanıcı oluşturur';

    public function handle()
    {
        $name = $this->ask('İsim');
        $username = $this->ask('Kullanıcı Adı');
        $email = $this->ask('Email');
        $password = $this->secret('Şifre');

        $user = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $user->assignRole('user');

        $this->info("✅ Kullanıcı oluşturuldu ve 'user' rolü atandı: {$user->username}");
    }
}
