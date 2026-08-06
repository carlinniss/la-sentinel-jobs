<?php

declare(strict_types=1);

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\App\Models\Profile;
use Modules\User\App\Models\User;
use Modules\User\App\Support\DemoUserCatalog;
use Spatie\Permission\Models\Role;

class AuthUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = collect(DemoUserCatalog::records())
            ->map(function (array $record): User {
                $user = User::query()->updateOrCreate(
                    ['email' => $record['email']],
                    [
                        'name' => $record['name'],
                        'password' => $record['password'],
                        'status' => 'active',
                    ],
                );

                Profile::query()->updateOrCreate(
                    ['user_id' => $user->getKey()],
                    $this->profilePayload($record),
                );

                return $user;
            });

        $adminRole = Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $users->each(function (User $user) use ($adminRole): void {
            if (DemoUserCatalog::isAdmin($user->email)) {
                $user->syncRoles([$adminRole->name]);

                return;
            }

            $user->syncRoles([]);
        });
    }

    private function profilePayload(array $record): array
    {
        $email = (string) ($record['email'] ?? '');

        if ($email === 'j@j.com') {
            return [
                'phone' => (string) ($record['phone'] ?? ''),
                'city' => 'Los Angeles',
                'country' => 'United States',
                'website' => 'https://jobs.bmo.com/us/en',
                'bio' => 'BMO Community Banking Careers connects Los Angeles candidates with customer-facing banking, branch leadership, small business, and operations roles. BMO is a top ten North American bank serving 13 million customers, founded in 1817 and guided by its purpose to Boldly Grow the Good in business and life. The employer profile highlights personalized career development, competitive rewards and benefits, wellness support, inclusive teams, and community impact through financial education, economic mobility, and volunteer engagement.',
                'is_verified' => true,
            ];
        }

        return [
            'phone' => (string) ($record['phone'] ?? ''),
            'city' => 'Los Angeles',
            'country' => 'United States',
            'website' => null,
            'bio' => null,
            'is_verified' => false,
        ];
    }
}
