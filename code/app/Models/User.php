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
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['roles'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['role_list'];

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
     * Relationship: roles
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
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
     * Attribute: role list
     *
     * @return string
     */
    public function getRoleListAttribute()
    {
        return implode(', ', $this->roles()->pluck('name')->toArray());
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

    /**
     * Determine if the user has a given role (or one of multiple given roles)
     *
     * @param  string|array  $role
     * @return bool
     */
    public function hasRole($role)
    {
        if (is_array($role)) {
            foreach ($role as $r) {
                if ($this->hasRole($r)) return true;
            }

            return false;
        }

        foreach ($this->roles as $r) {
            if ($r->name == $role) return true;
        }

        return false;
    }
}
