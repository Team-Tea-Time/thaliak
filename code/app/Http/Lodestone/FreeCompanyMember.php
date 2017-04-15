<?php

namespace Thaliak\Http\Lodestone;

/**
 * Convenience class used by the Lodestone API for returning
 * a list of free company members.
 */
class FreeCompanyMember
{
    public $id;             // String
    public $name;           // String
    public $rank;           // String

    public function __construct(Array $data = [])
    {
        foreach ($data as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    public function __toString()
    {
        return $this->name;
    }
}
