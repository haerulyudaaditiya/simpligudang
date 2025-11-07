<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::create([
            'id' => Str::uuid(),
            'name' => 'Admin Perusahaan',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $staffUser = User::create([
            'id' => Str::uuid(),
            'name' => 'Staff Gudang',
            'email' => 'staff@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $teamAcme = Team::create([
            'id' => Str::uuid(),
            'name' => 'Acme Corporation',
            'slug' => 'acme',
            'owner_id' => $adminUser->id,
        ]);

        $adminUser->teams()->attach($teamAcme, ['role' => 'admin']);
        $staffUser->teams()->attach($teamAcme, ['role' => 'staff']);
    }
}
