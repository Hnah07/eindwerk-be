<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;

class UpdateUserRole extends Command
{
    protected $signature = 'user:update-role {email} {role}';
    protected $description = 'Update a user\'s role (admin, superuser, user)';

    public function handle()
    {
        $email = $this->argument('email');
        $role = strtolower($this->argument('role'));

        if (!in_array($role, ['admin', 'superuser', 'user'])) {
            $this->error('Invalid role. Must be one of: admin, superuser, user');
            return 1;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found");
            return 1;
        }

        $user->role = UserRole::from($role);
        $user->save();

        $this->info("Successfully updated {$user->name}'s role to {$role}");
        return 0;
    }
}
