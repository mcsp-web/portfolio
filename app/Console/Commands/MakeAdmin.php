<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeAdmin extends Command
{
    protected $signature = 'portfolio:admin {email} {--name=Admin}';

    protected $description = 'Create the portfolio admin account, or reset its password';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $password = (string) $this->secret('Password (min 12 characters)');

        $check = Validator::make(['email' => $email, 'password' => $password], [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:12'],
        ]);

        if ($check->fails()) {
            $this->error($check->errors()->first());

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => (string) $this->option('name'), 'password' => Hash::make($password)],
        );

        $this->info($user->wasRecentlyCreated ? "Admin {$email} created." : "Password reset for {$email}.");

        return self::SUCCESS;
    }
}
