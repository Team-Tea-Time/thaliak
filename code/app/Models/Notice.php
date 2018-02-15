<?php

namespace Thaliak\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Thaliak\Models\Traits\{BelongsToCharacter, BelongsToUser, BelongsToWorld, HasUUIDs};

class Notice extends Model
{
    use BelongsToCharacter, BelongsToUser, BelongsToWorld;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

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

    public function claims(): HasMany
    {
        return $this->hasMany(NoticeClaim::class);
    }
}
