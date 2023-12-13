<?php

namespace App\Http\Controllers;

use App\Events\LessonWatched;
use App\Models\Lesson;
use App\Models\User;

class LessonController extends Controller
{
    public function lessonWatched(User $user, Lesson $lesson)
    {
        LessonWatched::dispatch($lesson, $user);
    }
}
