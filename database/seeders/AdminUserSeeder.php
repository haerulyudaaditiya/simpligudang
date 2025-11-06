<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::create([
            'id' => Str::uuid(),
            'name' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $teamAcme = Team::create([
            'id' => Str::uuid(),
            'name' => 'Acme Corporation',
            'slug' => 'acme',
            'owner_id' => $adminUser->id,
        ]);

        $teamInovasi = Team::create([
            'id' => Str::uuid(),
            'name' => 'PT. Inovasi Cepat',
            'slug' => 'inovasi',
            'owner_id' => $adminUser->id,
        ]);

        $adminUser->teams()->attach($teamAcme, ['role' => 'admin']);
        $adminUser->teams()->attach($teamInovasi, ['role' => 'admin']);
    }
}