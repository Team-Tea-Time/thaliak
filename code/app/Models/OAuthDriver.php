<?php

namespace Thaliak\Models;

use Illuminate\Database\Eloquent\Model;

class OauthDriver extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_drivers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'active'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['capitalised_name'];

    public function getCapitalisedNameAttribute(): String
    {
        return ucfirst($this->name);
    }
}
