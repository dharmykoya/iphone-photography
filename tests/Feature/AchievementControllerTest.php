<?php

namespace Tests\Feature;

use App\Events\LessonWatched;
use App\Models\Lesson;
use App\Models\User;
use App\Services\AchievementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AchievementControllerTest extends TestCase
{
    use RefreshDatabase;
    protected User $user;
    protected AchievementService $achievementService;


    public function setUp(): void
    {
        parent::setUp();

        // Create a user
        $this->user = User::factory()->create();

        DB::table("badges")->insert([
            ["title" => "Beginner", "achievement_points" => 0],
            ["title" => "Intermediate", "achievement_points" => 4],
            ["title" => "Advanced", "achievement_points" => 8],
            ["title" => "Master", "achievement_points" => 10]
        ]);

        // Create an instance of AchievementService
        $this->achievementService = new AchievementService();
    }

    /** @test */
    public function test_it_unlocks_first_lesson_watched_achievement()
    {
        $user = $this->user;
        $lesson = Lesson::factory()->create();

        // make an api call to watch a video so the watch video event can be triggered;
        $this->json('GET', "api/users/{$user->id}/watch/{$lesson->id}/lesson");

        $updatedUser = User::find($user->id);

        $this->assertTrue($updatedUser->achievements->contains('name', 'First Lesson Watched'));

        $nextAvailableAchievements = ['5 Lessons Watched', 'First Comment Written'];

        // Make an API call to the achievements endpoint and assert the JSON response
        $response = $this->json('GET', "/users/{$updatedUser->id}/achievements");

        $response->assertStatus(200)
            ->assertJson([
                'unlocked_achievements' => ['First Lesson Watched'],
                'next_available_achievements' => $nextAvailableAchievements,
                'current_badge' => 'Beginner',
                'next_badge' => 'Intermediate',
                'remaining_to_unlock_next_badge' => 3, // Assuming 4 achievements are needed for Intermediate
            ]);

    }



    /** @test */
    public function test_it_unlocks_five_lesson_watched_achievement()
    {
        $user = $this->user;
        $lessons = Lesson::factory()->count(5)->create();
        foreach ($lessons as $les) {
            // make an api call to watch a video so the watch video event can be triggered;
            $this->json('GET', "api/users/{$user->id}/watch/{$les->id}/lesson");
        }


        // Make an API call to the achievements endpoint and assert the JSON response
        $response = $this->json('GET', "/users/{$user->id}/achievements");

        $response->assertStatus(200)
            ->assertJson([
                'unlocked_achievements' => ['First Lesson Watched', '5 Lessons Watched'],
                'next_available_achievements' => ['10 Lessons Watched', 'First Comment Written'],
                'current_badge' => 'Beginner',
                'next_badge' => 'Intermediate',
                'remaining_to_unlock_next_badge' => 2
            ]);
    }

    public function test_it_calls_lesson_watched_event()
    {
        $user = $this->user;
        $lesson = Lesson::factory()->create();
        Event::fake();
        event(new LessonWatched($lesson, $user));

        // Assert
        Event::assertDispatched(LessonWatched::class, function ($event) use ($lesson, $user) {
            return $event->lesson->id === $lesson->id && $event->user->id === $user->id;
        });
    }
}
