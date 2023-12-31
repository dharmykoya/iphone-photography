<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AchievementUnlocked
{
    use Dispatchable, SerializesModels;

    public User $user;
    public string $achievementName;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, string $achievementName)
    {
        $this->user = $user;
        $this->achievementName = $achievementName;
    }
}
