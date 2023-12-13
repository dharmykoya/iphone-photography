<?php

namespace App\Services;

use App\Events\AchievementUnlocked;
use App\Models\Achievement;
use App\Models\Comment;
use App\Models\User;

class AchievementService {

    public function unlockLessonAchievement(User $user): void
    {
        $count = $user->watched()->count();
        $milestone = Achievement::LESSON_WATCHED_MILESTONES[$count] ?? null;

        if ($milestone) {
            $this->createAchievement($user, $milestone, Achievement::ACHIEVEMENT_TYPE['LESSON_WATCHED']);
            event(new AchievementUnlocked($user, $milestone));
        }
    }

    public function unlockCommentAchievement(Comment $comment): void
    {
        $user = $comment->user;
        $count = $user->comments()->count();

        $milestone = Achievement::COMMENT_WRITTEN_MILESTONES[$count] ?? null;

        if ($milestone) {
            $this->createAchievement($user, $milestone, Achievement::ACHIEVEMENT_TYPE['COMMENT_WRITTEN']);
            event(new AchievementUnlocked($user, $milestone));
        }
    }

    public function createAchievement($user, $milestone, $type) {
        $user->achievements()->create([
            'name' => $milestone,
            'type' => $type,
            'unlocked_at' => now()
        ]);
    }
}
