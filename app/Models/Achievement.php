<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model {
    use HasFactory;

    const ACHIEVEMENT_TYPE = [
        "LESSON_WATCHED" => "lesson_watched",
        "COMMENT_WRITTEN" => "comment_written",
    ];

    const LESSON_WATCHED_MILESTONES = [
        1 => 'First Lesson Watched',
        5 => '5 Lessons Watched',
        10 => '10 Lessons Watched',
        25 => '25 Lessons Watched',
        50 => '50 Lessons Watched',
    ];

    const COMMENT_WRITTEN_MILESTONES = [
        1 => 'First Comment Written',
        3 => '3 Comments Written',
        5 => '5 Comments Written',
        10 => '10 Comments Written',
        20 => '20 Comments Written',
    ];

    protected $fillable = [
        'name',
        'type',
        'unlocked_at'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
