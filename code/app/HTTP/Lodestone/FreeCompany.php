<?php

namespace Thaliak\HTTP\Lodestone;

/**
 * Convenience class used by the API for returning free company
 * information.
 */
class FreeCompany
{
    public $id;                 // String
    public $crest;              // Array (string)
    public $name;               // String
    public $tag;                // String
    public $slogan;             // String
    public $world;              // String
    public $grandcompany;       // String
    public $activemembers;      // Int
    public $dateformed;         // Int/Timestamp
    public $rank;               // Int
    public $standing;           // Array (string => string)
    public $estate;             // Array (name, address, greeting)
    public $active;             // String
    public $recruitment;        // String
    public $focus;              // Array (string => boolean)
    public $seeking;            // Array (string => boolean)

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
