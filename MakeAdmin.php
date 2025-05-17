<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MakeAdmin extends Command
{
    protected $signature = 'make:admin';
    protected $description = 'Admin rolüne sahip kullanıcı oluşturur';

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

        $user->assignRole('admin');

        $this->info("✅ Admin oluşturuldu ve 'admin' rolü atandı: {$user->username}");
    }
}
