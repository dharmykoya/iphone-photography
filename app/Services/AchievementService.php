<?php

namespace App\Services;

use App\Events\AchievementUnlocked;
use App\Models\Achievement;
use App\Models\User;

class AchievementService {

    public function unlockAchievement(User $user): void
    {
        $count = $user->watched()->count();
        $milestone = Achievement::LESSON_WATCHED_MILESTONES[$count] ?? null;

        if ($milestone) {
            $user->achievements()->create([
                'name' => $milestone,
                'type' => Achievement::ACHIEVEMENT_TYPE['LESSON_WATCHED'],
                'unlocked_at' => now()
            ]);
            event(new AchievementUnlocked($user, $milestone));
        }
    }
}
