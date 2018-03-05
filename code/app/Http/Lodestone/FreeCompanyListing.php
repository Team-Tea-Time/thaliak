<?php

namespace Thaliak\Http\Lodestone;

/**
 * Convenience class used by the API when returning a list of free
 * companies.
 */
class FreeCompanyListing
{
    public $id;                 // String
    public $crest;              // Array (string)
    public $name;               // String
    public $world;              // String
    public $grandcompany;       // String
    public $activemembers;      // Int
    public $dateformed;         // Int/Timestamp
    public $estate;             // String

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
