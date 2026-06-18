<?php

namespace Database\Seeders;

use App\Models\Team;
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
        foreach ([
            ['name' => 'Tổ 1', 'login_slug' => 'team-1', 'access_code' => 'vnpt1'],
            ['name' => 'Tổ 2', 'login_slug' => 'team-2', 'access_code' => 'vnpt2'],
            ['name' => 'Tổ 3', 'login_slug' => 'team-3', 'access_code' => 'vnpt3'],
        ] as $team) {
            Team::query()->updateOrCreate(['login_slug' => $team['login_slug']], $team);
        }
    }
}
