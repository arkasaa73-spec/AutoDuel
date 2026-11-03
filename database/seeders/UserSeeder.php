<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Пароли можно задать в .env (SEED_ADMIN_PASSWORD, SEED_PLAYER_PASSWORD), иначе будут случайные
        $this->seedUser('Admin', 'admin@autoduel.test', 'admin', env('SEED_ADMIN_PASSWORD'));
        $this->seedUser('Player', 'player@autoduel.test', 'user', env('SEED_PLAYER_PASSWORD'));
    }

    private function seedUser(string $name, string $email, string $role, ?string $password): void
    {
        // Уже существующего пользователя не трогаем, чтобы не сбросить ему пароль
        if (User::where('email', $email)->exists()) {
            return;
        }

        $password = $password ?: Str::password(12);

        $user = new User(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);
        // role и email_verified_at не в $fillable, поэтому forceFill
        $user->forceFill(['role' => $role, 'email_verified_at' => now()])->save();

        // Пароль показываем один раз при создании, в репозиторий он не попадает
        $this->command?->info("Created {$role}: {$email} / password: {$password}");
    }
}
