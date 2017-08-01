<?php

namespace Thaliak\Models\Traits;

use Webpatser\Uuid\Uuid;

trait HasUUIDs
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Uuid::generate()->string;
        });
    }
}
