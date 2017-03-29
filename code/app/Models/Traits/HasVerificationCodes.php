<?php

namespace Thaliak\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use Thaliak\Models\Verification;

trait HasVerificationCodes
{
    public function delete()
    {
        if ($this->verification) {
            $this->verification->delete();
        }

        return parent::delete();
    }

    public function verification(): MorphOne
    {
        return $this->morphOne(Verification::class, 'model');
    }

    public function scopeVerified(Builder $builder): Builder
    {
        return $builder->where('verified', 1);
    }

    public function scopeUnverified(Builder $builder): Builder
    {
        return $builder->where('verified', 0);
    }

    public function scopeByVerification(Builder $builder, $code): Builder
    {
        return $builder->whereHas('verification', function ($query) use ($code) {
            $query->where('code', $code);
        });
    }

    public function createVerificationCode(): Model
    {
        $this->verification()->create(['code' => Str::random(16)]);
    }

    public function verify(): Model
    {
        if (!$this->verified) {
            $this->verification->delete();
            $this->verified = 1;
            $this->save();
        }

        return $this;
    }
}
