<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AchievementService;
use Illuminate\Http\Request;

class AchievementsController extends Controller
{
    protected $achievementService;

    public function __construct(AchievementService $achievementService) {
        $this->achievementService = $achievementService;
    }

    public function index(User $user)
    {
        return response()->json([
            'unlocked_achievements' => $user->achievements->pluck("name"),
            'next_available_achievements' => $this->achievementService->getNextAvailableAchievements($user),
            'current_badge' => $this->achievementService->getCurrentBadge($user),
            'next_badge' => $this->achievementService->getNextBadge($user),
            'remaining_to_unlock_next_badge' => $this->achievementService->getRemainingToUnlockNext($user)
        ]);
    }
}
