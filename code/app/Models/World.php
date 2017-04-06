<?php

namespace Thaliak\Models;

use Illuminate\Database\Eloquent\Model;

class World extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['name_lowercase'];

    public function getNameLowercaseAttribute(): String
    {
        return strtolower($this->name);
    }
}
