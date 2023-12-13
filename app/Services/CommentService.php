<?php

namespace App\Services;

use App\Events\CommentWritten;
use App\Models\Comment;

class CommentService {
    public function createComment($data) {
        $comment = Comment::create($data);

        CommentWritten::dispatch($comment);
        return $comment;
    }
}
