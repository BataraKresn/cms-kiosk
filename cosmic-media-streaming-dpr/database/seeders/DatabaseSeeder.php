<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Utils::getRoleModel()::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $user = \App\Models\User::factory()->create([
            'name' => 'Administrator',
            'email' => 'administrator@cms.id',
            'email_verified_at' => now(),
        ]);

        $user->assignRole('super_admin');

        $this->call([
            ScreenSeeder::class,
            AssignMediaSeeder::class,
            LayoutSeeder::class,
        ]);
    }
}
