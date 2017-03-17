<?php

namespace Thaliak\Models;

use Illuminate\Database\Eloquent\Model;

class OauthUser extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'oauth_driver_id',
        'provider_user_id',
        'access_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'provider_user_id', 'access_token'
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['driver'];

    /**
     * Relationship: OAuth Driver.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function driver() {
        return $this->belongsTo(OAuthDriver::class, 'oauth_driver_id');
    }
}
