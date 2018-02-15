<?php

namespace Thaliak\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Thaliak\Models\World;

trait BelongsToWorld
{
    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }

    public function getWorldNameAttribute(): String
    {
        return $this->world->name;
    }
}
