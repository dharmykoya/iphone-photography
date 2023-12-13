<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Services\CommentService;

class CommentController extends Controller
{
    protected $commentService;

    public function __construct(CommentService $commentService) {
        $this->commentService = $commentService;
    }

    public function comment(CommentRequest $request)
    {
        try {
            $comment = $this->commentService->createComment($request->validated());
            return response()->json([
                'success' => true,
                'data' => $comment
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
                'success' => false,
            ]);
        }
    }
}
