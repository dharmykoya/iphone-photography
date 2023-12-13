<?php

namespace App\Listeners;

use App\Events\LessonWatched;
use App\Services\AchievementService;

class LessonWatchedListener
{
    protected $achievementService;
    /**
     * Create the event listener.
     */
    public function __construct(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    /**
     * Handle the event.
     */
    public function handle(LessonWatched $event): void
    {
        // mark te video as watched
        $event->user->watched()->attach($event->lesson->id, ['watched' => true]);

        $this->achievementService->unlockAchievement($event->user);
    }
}
