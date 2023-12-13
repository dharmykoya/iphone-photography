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
            'current_badge' => '',
            'next_badge' => '',
            'remaing_to_unlock_next_badge' => 0
        ]);
    }
}
