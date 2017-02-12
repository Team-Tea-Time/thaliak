<?php

namespace Thaliak\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasVerificationCodes
{
    /**
     * Relationship: Verification
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function verification()
    {
        $class = get_class($this);
        return $this->hasOne("{$class}Verification");
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
