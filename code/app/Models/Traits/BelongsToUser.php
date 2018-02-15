<?php

namespace Thaliak\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Thaliak\Models\User;

trait BelongsToUser
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUserNameAttribute(): String
    {
        return $this->user->name;
    }
}
