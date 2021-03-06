<?php

namespace Thaliak\HTTP\Lodestone;

/**
 * Convenience class used by the API for returning detailed information
 * about a single character.
 */
class Character
{
    public $id;             // String
    public $avatar;         // String
    public $name;           // String
    public $title;          // String
    public $world;          // String
    public $race;           // String
    public $clan;           // String
    public $gender;         // String
    public $nameday;        // String
    public $guardian;       // String
    public $citystate;      // String
    public $grandcompany;   // Array (name, rank)
    public $freecompany;    // Array (id, crest, name, rank)
    public $activeclass;    // String
    public $classes;        // Array (class => level)

    public $portrait;       // String
    public $introduction;   // String

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
