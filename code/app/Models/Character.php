<?php

namespace Thaliak\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Slugify;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use Thaliak\HTTP\Lodestone\Character as LodestoneCharacter;
use Thaliak\Models\Enum\CharacterStatus;
use Thaliak\Models\Traits\HasVerificationCodes;

class Character extends Model implements HasMedia
{
    use HasMediaTrait, HasVerificationCodes;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

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
    protected $with = ['world', 'media'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['slug', 'user_name', 'avatar', 'portrait'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['user'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(CharacterProfile::class);
    }

    public function getSlugAttribute(): String
    {
        return Slugify::slugify($this->name);
    }

    public function getUserNameAttribute(): String
    {
        return $this->user->name;
    }

    public function getAvatarAttribute(): String
    {
        return $this->media->where('name', 'avatar')->first()->getUrl();
    }

    public function getPortraitAttribute(): String
    {
        return $this->media->where('name', 'portrait')->first()->getUrl();
    }

    public function scopeWorld(Builder $query, World $world): Builder
    {
        return $query->whereHas('world', function ($query) use ($world) {
            $query->whereRaw('LOWER(name) = ?', [$world->name]);
        });
    }

    public static function createFromLodestone(
        LodestoneCharacter $character,
        User $user,
        World $world
    ): Character
    {
        return static::create([
            'id'            => $character->id,
            'user_id'       => $user->id,
            'world_id'      => $world->id,
            'verified'      => false,
            'name'          => $character->name,
            'gender'        => $character->gender,
            'race'          => $character->race,
            'clan'          => $character->clan,
            'nameday'       => $character->nameday,
            'guardian'      => $character->guardian,
            'city_state'    => $character->city_state,
            'active_class'  => $character->active_class,
            'status'        => CharacterStatus::ALT
        ]);
    }

    public function setMain(): Character
    {
        $this->update(['status' => CharacterStatus::MAIN]);

        $this->user->characters()->where('id', '!=', $this->id)->update([
            'status' => CharacterStatus::ALT
        ]);

        return $this;
    }
}
