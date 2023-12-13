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

    protected const LESSON_WATCHED = Achievement::ACHIEVEMENT_TYPE['LESSON_WATCHED'];
    protected const COMMENT_WRITTEN = Achievement::ACHIEVEMENT_TYPE['COMMENT_WRITTEN'];

    public function unlockLessonAchievement(User $user): void
    {
        // count the lesson actions taken by the user
        $count = $this->countActions($user, self::LESSON_WATCHED);

        // check if the milestone exist
        $milestone = Achievement::LESSON_WATCHED_MILESTONES[$count] ?? null;

        if ($milestone) {
            $this->unlockAchievement($user, $count, self::LESSON_WATCHED);
        }
        $this->unLockBadge($user);
    }

    public function unlockCommentAchievement(Comment $comment): void
    {
        $user = $comment->user;
        $count = $user->comments()->count();

        // check if the milestone exist
        $milestone = Achievement::COMMENT_WRITTEN_MILESTONES[$count] ?? null;

        if ($milestone) {
            $this->unlockAchievement($user, $count, self::COMMENT_WRITTEN);
        }

        $this->unLockBadge($user);
    }

    protected function unlockAchievement(User $user, int $milestoneNumber, string $type): void
    {
        $achievementName = $this->getAchievementName($type, $milestoneNumber);

        $user->achievements()->create([
            'name' => $achievementName,
            'type' => $type,
            'unlocked_at' => now(),
        ]);
        event(new AchievementUnlocked($user, $achievementName));

    }

    public function createAchievement($user, $milestone, $type) {
        $user->achievements()->create([
            'name' => $milestone,
            'type' => $type,
            'unlocked_at' => now()
        ]);
    }

    protected function unlockBadge(User $user): void
    {
        $achievements = $user->achievements->count();
        $badge = $this->getBadge($achievements);

        if ($achievements === 0 || ($achievements > 0 && $badge)) {
            $this->createUserBadge($user, $badge);
            event(new BadgeUnlocked($user, $badge->title));
        }
    }

    protected function getBadge(int $achievements)
    {
        return Badge::query()->where('achievement_points', '=', (int) $achievements)->first();
    }

    protected function createUserBadge(User $user, $badge): void
    {
        if ($badge) {
            // checks if the user already has the badge
            $oldBadge = UserBadge::query()->with('badge')->where([
                'user_id' => $user->id,
                'badge_id' => $badge->id
            ])->first();

            if (!$oldBadge) {
                UserBadge::create([
                    'user_id' => $user->id,
                    'badge_id' => $badge->id,
                ]);
            }
        }
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
        return $type === self::LESSON_WATCHED
            ? $user->watched()->count()
            : ($type === self::COMMENT_WRITTEN ? $user->comments()->count() : 0);
    }

    protected function getNextMilestone($type, $count, User $user): ?int {
        $milestones = $type === Achievement::ACHIEVEMENT_TYPE['LESSON_WATCHED']
            ? Achievement::LESSON_WATCHED_MILESTONES
            : Achievement::COMMENT_WRITTEN_MILESTONES;

        $unlockedAchievements = $user->achievements->where('type', $type)->pluck('name')->toArray();

        foreach ($milestones as $milestone => $achievementName) {
            // checks if the milestones exist in the user unlocked achievements
            if ($count < $milestone && !in_array($achievementName, $unlockedAchievements, true)) {
                return $milestone;
            }
        }
        return null;
    }

    protected function getAchievementName($type, $milestone): string {
        $milestones = $type === self::LESSON_WATCHED
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
        // checks if the user has a badge or picks the smallest badge by achievement points
        $userBadge = $user->badges()->with('badge')->latest()->first()->badge ?? Badge::query()->orderBy('achievement_points', 'asc')->first();

        // sort and filter the badges by achievements points of the user
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
        // checks if the user has a badge or picks the smallest badge by achievement points
        $userBadge = $user->badges()->with('badge')->latest()->first()->badge ?? Badge::query()->orderBy('achievement_points', 'asc')->first();

        // sort and filter the badges by achievements points of the user
        $nextBadges = Badge::all()->sortBy("achievement_points")->filter(function ($value) use ($userBadge) {
            return $value->achievement_points > $userBadge->achievement_points ;
        });

        if($nextBadges->count() == 0){
            return 0;
        }

        return $nextBadges->first()->achievement_points - $user->achievements->count();
    }
}
