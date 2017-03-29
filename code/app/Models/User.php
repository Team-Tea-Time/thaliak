<?php

namespace Thaliak\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Thaliak\Models\Traits\HasUuids;
use Thaliak\Models\Traits\HasVerificationCodes;

class User extends Authenticatable
{
    use HasApiTokens, HasUuids, HasVerificationCodes, Notifiable;

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
    protected $with = ['auths', 'roles'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['role_list'];

    public function auths(): HasMany
    {
        return $this->hasMany(OAuthUser::class);
    }

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('active', 1);
    }

    public function scopeForAuth(Builder $builder, Int $authId): Builder
    {
        return $builder->whereHas('auths', function ($query) use ($authId) {
            $query->where('provider_user_id', $authId);
        });
    }

    public function getRoleListAttribute(): String
    {
        return implode(', ', $this->roles()->pluck('name')->toArray());
    }

    public function findForPassport(String $identity): User
    {
        $builder = (!!filter_var($identity, FILTER_VALIDATE_EMAIL))
            ? $this->where('email', $identity)
            : $this->where('name', $identity);

        return $builder->verified()->active()->first();
    }

    public function activate(): User
    {
        if (!$this->active) {
            $this->active = 1;
            $this->save();
        }

        return $this;
    }

    public function deactivate(): User
    {
        if ($this->active) {
            $this->active = 0;
            $this->save();
        }

        return $this;
    }

    public function hasRole($role): Bool
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
