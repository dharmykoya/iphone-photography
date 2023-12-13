<?php

namespace Tests\Feature;

use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
use App\Models\Comment;
use App\Models\Lesson;
use App\Models\User;
use App\Services\AchievementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AchievementServiceTest extends TestCase
{
    use RefreshDatabase;
    protected User $user;
    protected Comment $comment;
    protected AchievementService $achievementService;


    public function setUp(): void
    {
        parent::setUp();

        // Create a user
        $this->user = User::factory()->create();
        $lesson = Lesson::factory()->create();
        $this->comment = Comment::factory()->create(['user_id' => $this->user->id]);

        DB::table("badges")->insert([
            ["title" => "Beginner", "achievement_points" => 0],
            ["title" => "Intermediate", "achievement_points" => 4],
            ["title" => "Advanced", "achievement_points" => 8],
            ["title" => "Master", "achievement_points" => 10]
        ]);

        DB::table("lesson_user")->insert([
            ["user_id" => $this->user->id, "lesson_id" => $lesson->id, "watched" => true],
        ]);

        // Create an instance of AchievementService
        $this->achievementService = new AchievementService();
    }
    public function test_unlock_comment_achievement()
    {
        Event::fake();

        $this->achievementService->unlockCommentAchievement($this->comment);

        Event::assertDispatched(AchievementUnlocked::class, function ($event) {
            return $event->user->id === $this->user->id && $event->achievementName === "First Comment Written";
        });

        $this->assertDatabaseHas('achievements', [
            'user_id' => $this->user->id,
            'name' => 'First Comment Written'
        ]);
    }

    public function test_unlock_lesson_achievement()
    {
        Event::fake();

        $this->achievementService->unlockLessonAchievement($this->user);

        Event::assertDispatched(AchievementUnlocked::class, function ($event) {
            return $event->user->id === $this->user->id && $event->achievementName === "First Lesson Watched";
        });

        $this->assertDatabaseHas('achievements', [
            'user_id' => $this->user->id,
            'name' => 'First Lesson Watched'
        ]);
    }

    public function test_badge_unlocked()
    {
        Event::fake();
        $user = User::factory()->create();
        Comment::factory()->count(3)->create(['user_id' => $user->id]);
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $this->achievementService->unlockCommentAchievement($comment);

        Event::assertDispatched(BadgeUnlocked::class, function ($event) use ($user) {
            return $event->user->id === $user->id && $event->badgeName === "Beginner";
        });
    }

    public function test_next_available_achievements() {
        //note this user has written a comment and watched a video
        $result = $this->achievementService->getNextAvailableAchievements($this->user);
        $expectedAchievements = ["5 Lessons Watched", "3 Comments Written"];
        $this->assertCount(count($expectedAchievements), $result);
        $this->assertEquals($expectedAchievements, $result);
    }

    public function test_current_badge() {
        $result = $this->achievementService->getCurrentBadge($this->user);
        $this->assertEquals("Beginner", $result);
    }
}
