<?php

namespace Thaliak\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Thaliak\Models\Traits\HasVerificationCodes;

class User extends Authenticatable
{
    use HasApiTokens, HasVerificationCodes, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'verified', 'active',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Relationship: Characters
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function characters()
    {
        return $this->hasMany(Character::class);
    }

    /**
     * Scope: Active
     *
     * @param  $builder  Builder
     * @return Builder
     */
    public function scopeActive(Builder $builder)
    {
        return $builder->where('active', 1);
    }

    /**
     * Find a user by identity for Passport requests.
     *
     * @param  string  $identity
     * @return User
     */
    public function findForPassport($identity)
    {
        $builder = (!!filter_var($identity, FILTER_VALIDATE_EMAIL))
            ? $this->where('email', $identity)
            : $this->where('name', $identity);

        return $builder->verified()->active()->first();
    }

    /**
     * Activate the user (if applicable).
     *
     * @return User
     */
    public function activate()
    {
        if (!$this->active) {
            $this->active = 1;
            $this->save();
        }

        return $this;
    }

    /**
     * Deactivate the user (if applicable).
     *
     * @return User
     */
    public function deactivate()
    {
        if ($this->active) {
            $this->active = 0;
            $this->save();
        }

        return $this;
    }
}
