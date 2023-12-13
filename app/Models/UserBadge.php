<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'badge_id'];

    /**
     * Get the user that wrote the comment.
     */
    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }
}
