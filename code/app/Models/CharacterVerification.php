<?php

namespace Thaliak\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterVerification extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'code',
    ];
}
