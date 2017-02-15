<?php

namespace Thaliak\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Thaliak\Models\Verification;

trait HasVerificationCodes
{
    /**
     * Delete the model from the database.
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function delete()
    {
        $this->verification->delete();
        return parent::delete();
    }

    /**
     * Relationship: Verification
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function verification()
    {
        return $this->morphOne(Verification::class, 'model');
    }

    /**
     * Scope: Verified
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeVerified(Builder $builder)
    {
        return $builder->where('verified', 1);
    }

    /**
     * Scope: Verification
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeByVerification(Builder $builder, $code)
    {
        return $builder->whereHas('verification', function ($query) use ($code) {
            $query->where('code', $code);
        });
    }

    /**
     * Create a new verification code for this model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createVerificationCode()
    {
        $this->verification()->create(['code' => str_random(16)]);
    }

    /**
     * Verify the model and delete the verification (if applicable).
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function verify()
    {
        if (!$this->verified) {
            $this->verification->delete();
            $this->verified = 1;
            $this->save();
        }

        return $this;
    }
}
