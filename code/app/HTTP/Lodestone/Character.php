<?php

namespace Thaliak\HTTP\Lodestone;

/**
 * Convenience class used by the Lodestone API for returning
 * character data.
 *
 * Basic info is populated by all methods.
 *
 * Extended info is only populated when requesting character 
 * details and search method/s will leave these properties
 * blank/null. 
 */
class Character
{
    // basic info
    public $id;             // String
    public $avatar;         // String
    public $name;           // String
    public $world;          // String
    public $grandcompany;   // Array (name, rank)
    public $freecompany;    // Array (id, crest, name, rank)

    // extended info
    public $portrait;       // String
    public $introduction;   // String
    public $title;          // String
    public $race;           // String
    public $clan;           // String
    public $gender;         // String
    public $nameday;        // String
    public $guardian;       // String
    public $citystate;      // String
    public $activeclass;    // String
    public $classes;        // Array (class => level)

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
