<?php

namespace App\Services;

use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
use App\Models\Achievement;
use App\Models\Badge;
use App\Models\Comment;
use App\Models\User;
use App\Models\UserBadge;

class AchievementService {

    public function unlockLessonAchievement(User $user): void
    {
        $count = $user->watched()->count();
        $milestone = Achievement::LESSON_WATCHED_MILESTONES[$count] ?? null;

        if ($milestone) {
            $this->createAchievement($user, $milestone, Achievement::ACHIEVEMENT_TYPE['LESSON_WATCHED']);
            event(new AchievementUnlocked($user, $milestone));
        }
        $this->unLockBadge($user);
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

        $this->unLockBadge($user);
    }

    public function createAchievement($user, $milestone, $type) {
        $user->achievements()->create([
            'name' => $milestone,
            'type' => $type,
            'unlocked_at' => now()
        ]);
    }

    public function unLockBadge($user) {
        $achievements = $user->achievements->count();

        $badge = Badge::query()->where('achievement_points', '=', (int) $achievements)->first();

        if ($achievements === 0) {
            UserBadge::create([
                'user_id' => $user->id,
                'badge_id' => $badge->id
            ]);
            BadgeUnlocked::dispatch($user, $badge->title);
        }

        if ($achievements > 0 && $badge) {
            $oldBadge = UserBadge::query()->with('badge')->where([
                'user_id' => $user->id,
                'badge_id' => $badge->id
            ])->first();
            if (!$oldBadge) {
                UserBadge::create([
                    'user_id' => $user->id,
                    'badge_id' => $badge->id
                ]);
                BadgeUnlocked::dispatch($user, $badge->title);
            }
        }
        return $badge;
    }


}
