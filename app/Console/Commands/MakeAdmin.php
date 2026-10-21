<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Modules\User\App\Models\Profile;
use Modules\User\App\Models\User;
use Spatie\Permission\Models\Role;

class MakeAdmin extends Command
{
    protected $signature = 'jobs:make-admin
        {email : Email address for the admin login}
        {--name= : Display name (defaults to the part of the email before @)}
        {--reset-password : Set a new password for an existing account}';

    protected $description = 'Create or promote an admin account that can log in to /admin';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('That is not a valid email address.');

            return self::FAILURE;
        }

        $user = User::withTrashed()->firstOrNew(['email' => $email]);
        $isNew = ! $user->exists;

        if ($isNew || $this->option('reset-password')) {
            $password = (string) $this->secret('Password (at least 12 characters, hidden while typing)');
            $confirm = (string) $this->secret('Type it again');

            if (mb_strlen($password) < 12 || $password !== $confirm) {
                $this->error('Passwords must match and be at least 12 characters. Nothing was changed.');

                return self::FAILURE;
            }

            $user->password = Hash::make($password);
        }

        if ($isNew || $this->option('name')) {
            $user->name = (string) ($this->option('name') ?: strstr($email, '@', true));
        }

        $user->email_verified_at ??= now();
        $user->save();

        if ($user->trashed()) {
            $user->restore();
        }

        Profile::withTrashed()->firstOrCreate(['user_id' => $user->id]);

        $role = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user->assignRole($role);

        $this->info(sprintf('%s %s is now an admin. Log in at %s/admin', $isNew ? 'Created' : 'Updated', $email, rtrim((string) config('app.url'), '/')));

        return self::SUCCESS;
    }
}
