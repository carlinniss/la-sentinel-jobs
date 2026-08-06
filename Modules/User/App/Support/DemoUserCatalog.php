<?php

declare(strict_types=1);

namespace Modules\User\App\Support;

use Illuminate\Support\Str;

final class DemoUserCatalog
{
    public static function records(): array
    {
        $password = self::resolvePassword();

        return [
            [
                'email' => 'a@a.com',
                'name' => 'LA Sentinel Admin',
                'password' => $password,
                'phone' => '+13235550100',
                'is_admin' => true,
            ],
            [
                'email' => 'b@b.com',
                'name' => 'Sentinel Talent Desk',
                'password' => $password,
                'phone' => '+13235550101',
                'is_admin' => false,
            ],
            [
                'email' => 'c@c.com',
                'name' => 'Kedren Health Careers',
                'password' => $password,
                'phone' => '+13235550102',
                'is_admin' => false,
            ],
            [
                'email' => 'd@d.com',
                'name' => 'South LA Transit Partners',
                'password' => $password,
                'phone' => '+13235550103',
                'is_admin' => false,
            ],
            [
                'email' => 'e@e.com',
                'name' => 'Crenshaw Creative Studio',
                'password' => $password,
                'phone' => '+13235550104',
                'is_admin' => false,
            ],
        ];
    }

    public static function resolvePassword(): string
    {
        $configured = trim((string) config('demo.user_password', ''));

        if ($configured !== '') {
            return $configured;
        }

        return Str::password(20);
    }

    public static function emails(): array
    {
        return array_column(self::records(), 'email');
    }

    public static function phoneFor(string $email): string
    {
        foreach (self::records() as $record) {
            if ($record['email'] === $email) {
                return $record['phone'];
            }
        }

        return '+13235550199';
    }

    public static function isAdmin(string $email): bool
    {
        foreach (self::records() as $record) {
            if ($record['email'] === $email) {
                return (bool) $record['is_admin'];
            }
        }

        return false;
    }
}
