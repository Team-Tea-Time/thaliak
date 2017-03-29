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
     * Relationship: Character
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function character()
    {
        return $this->belongsTo(Character::class);
    }

    public function getPortraitAttribute()
    {
        $portrait = $this->media->where('name', 'profile_portrait')->first();

        if (!$portrait) {
            return null;
        }

        return $portrait->getUrl();
    }
}
