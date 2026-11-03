<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetUserRole extends Command
{
    protected $signature = 'users:admin
                            {email : e-mail of an existing user}
                            {--revoke : make the user a regular player again}';

    protected $description = 'Give the admin role to a user (or take it away with --revoke)';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('User not found: ' . $this->argument('email'));

            return self::FAILURE;
        }

        // role не входит в $fillable (защита от массового присвоения), поэтому forceFill
        $role = $this->option('revoke') ? 'user' : 'admin';
        $user->forceFill(['role' => $role])->save();

        $this->info("{$user->email} is now: {$role}");

        return self::SUCCESS;
    }
}
