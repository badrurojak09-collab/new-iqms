<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
            LamInfokom21CriteriaSeeder::class,
            BanPtIaptCriteriaSeeder::class,
            SpmiStmikRealisticDemoSeeder::class,
            AccreditationStmikRealisticDemoSeeder::class,
        ]);

        if (app()->environment(['local', 'testing'])) {
            $this->call([
                // SqmScenarioSeeder::class,
                StmikNusantaraDemoSeeder::class,
            ]);
        }

        User::factory()->create([
            // 'name' => 'Test User',
            // 'email' => 'test@example.com',
        ]);
    }
}
