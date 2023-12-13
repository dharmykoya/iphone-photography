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

    public function getNextAvailableAchievements(User $user): array {
        $nextAchievements = [];
        foreach (Achievement::ACHIEVEMENT_TYPE as $key => $type) {
            $count = $this->countActions($user, $type);
            $nextMilestone = $this->getNextMilestone($type, $count, $user);
            if ($nextMilestone !== null) {
                $nextAchievements[] = $this->getAchievementName($type, $nextMilestone);
            }
        }

        return $nextAchievements;
    }

    protected function countActions(User $user, string $type): int
    {
        if ($type === Achievement::ACHIEVEMENT_TYPE['LESSON_WATCHED']) {
            return $user->watched()->count();
        }

        if ($type === Achievement::ACHIEVEMENT_TYPE['COMMENT_WRITTEN']) {
            return $user->comments()->count();
        }

        return 0;
    }

    protected function getNextMilestone($type, $count, User $user): ?int {
        $milestones = $type === Achievement::ACHIEVEMENT_TYPE['LESSON_WATCHED']
            ? Achievement::LESSON_WATCHED_MILESTONES
            : Achievement::COMMENT_WRITTEN_MILESTONES;

        $unlockedAchievements = $user->achievements->where('type', $type)->pluck('name')->toArray();

        foreach ($milestones as $milestone => $achievementName) {
            if ($count < $milestone && !in_array($achievementName, $unlockedAchievements, true)) {
                return $milestone;
            }
        }
        return null;
    }

    protected function getAchievementName($type, $milestone): string {
        $milestones = $type === Achievement::ACHIEVEMENT_TYPE['LESSON_WATCHED']
            ? Achievement::LESSON_WATCHED_MILESTONES
            : Achievement::COMMENT_WRITTEN_MILESTONES;

        return $milestones[$milestone];
    }

    public function getCurrentBadge($user) {
        $userBadge = $user->badges()->with('badge')->latest()->first();
        if (!$userBadge) {
            return 'Beginner';
        }
        return $userBadge->badge->title;
    }

    public function getNextBadge(User $user): string {
        $userBadge = $user->badges()->with('badge')->latest()->first()->badge ?? Badge::query()->orderBy('achievement_points', 'asc')->first();

        $nextBadges = Badge::all()->sortBy("achievement_points")->filter(function ($value) use ($userBadge) {
            return $value->achievement_points > $userBadge->achievement_points ;
        });

        if($nextBadges->count() > 0){
            return $nextBadges->first()->title;
        }

        return "No badge";
    }

    public function getRemainingToUnlockNext($user)
    {
        $userBadge = $user->badges()->with('badge')->latest()->first()->badge ?? Badge::query()->orderBy('achievement_points', 'asc')->first();

        $nextBadges = Badge::all()->sortBy("achievement_points")->filter(function ($value) use ($userBadge) {
            return $value->achievement_points > $userBadge->achievement_points ;
        });

        if($nextBadges->count() == 0){
            return 0;
        }
        return $nextBadges->first()->achievement_points - $userBadge->achievement_points;
    }
}
