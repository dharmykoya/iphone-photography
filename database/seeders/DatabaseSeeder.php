<?php

namespace Database\Seeders;

use App\Models\Lesson;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->count(5)->create();
        $lessons = Lesson::factory()
            ->count(20)
            ->create();

        DB::table("badges")->insert([
            ["title" => "Beginner", "achievement_points" => 0],
            ["title" => "Intermediate", "achievement_points" => 4],
            ["title" => "Advanced", "achievement_points" => 8],
            ["title" => "Master", "achievement_points" => 10]
        ]);
    }
}
