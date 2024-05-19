<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->approved_at !== null;
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }
}
