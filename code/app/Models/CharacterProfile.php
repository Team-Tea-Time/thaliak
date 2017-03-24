<?php

namespace Thaliak\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

class CharacterProfile extends Model implements HasMedia
{
    use HasMediaTrait;

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
    protected $with = ['media'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['portrait'];

    /**
     * Relationship: User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function character()
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Attribute: portrait URL. Falls back to the character's lodestone portrait
     * if no portrait is present for this profile.
     *
     * @return string
     */
    public function getPortraitAttribute()
    {
        $portrait = $this->media->where('name', 'profile_portrait')->first();

        if (!$portrait) {
            return null;
        }

        return $portrait->getUrl();
    }
}
