<?php

namespace Thaliak\Http\Lodestone;

/**
 * Convenience class used by the API when returning a list of characters.
 */
class CharacterListing
{
    public $id;             // String
    public $avatar;         // String
    public $name;           // String
    public $world;          // String
    public $grandcompany;   // Array (name, rank)
    public $freecompany;    // Array (id, crest, name, rank)

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
