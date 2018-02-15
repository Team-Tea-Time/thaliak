<?php

namespace Thaliak\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Thaliak\Models\Traits\{BelongsToCharacter, BelongsToUser, BelongsToWorld};

class NoticeClaim extends Model
{
    use BelongsToCharacter, BelongsToUser, BelongsToWorld;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['user', 'character', 'world'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['user_name', 'character_name', 'world_name'];

    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }
}
