<?php

namespace Thaliak\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Thaliak\Models\Character;

trait BelongsToCharacter
{
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function getCharacterNameAttribute(): String
    {
        return $this->character->name;
    }
}
