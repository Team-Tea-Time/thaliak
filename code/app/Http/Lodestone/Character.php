<?php

namespace Thaliak\Http\Lodestone;

class Character
{
    public $id;             // Int
    public $name;           // String
    public $world;          // String
    public $gender;         // String
    public $avatar;         // String
    public $portrait;       // String
    public $introduction;   // String
    public $race;           // String
    public $clan;           // String
    public $nameday;        // String
    public $guardian;       // String
    public $city_state;     // String
    public $grand_company;  // String
    public $active_class;   // Array

    public function __construct(Array $data)
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
