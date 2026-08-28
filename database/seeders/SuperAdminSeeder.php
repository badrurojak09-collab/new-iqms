<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
final class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('SuperAdminSeeder dilewati pada production.');

            return;
        }

        $email = (string) env('SQM_SUPER_ADMIN_EMAIL', 'superadmin@sqm.test');
        $password = (string) env('SQM_SUPER_ADMIN_PASSWORD', 'SuperAdmin-SQM-2026!');

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Administrator SQM',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'yayasan_id' => null,
                'perguruan_tinggi_id' => null,
                'default_scope_type' => 'institution',
            ],
        );

        $user->assignRole('super_admin');

        $this->command?->info("Akun super_admin lokal tersedia: {$email}");
    }
}
