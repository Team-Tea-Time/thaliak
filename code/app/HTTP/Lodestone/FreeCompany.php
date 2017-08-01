<?php

namespace Thaliak\HTTP\Lodestone;

/**
 * Convenience class for returning free company data.
 *
 * Basic info is populated by all methods.
 *
 * Extended info is only populated when requesting free
 * company details and search method/s will leave these
 * properties blank/null.
 */
class FreeCompany
{
    // basic information
    public $id;                 // String
    public $crest;              // Array (string)
    public $name;               // String
    public $world;              // String
    public $grand_company;      // String
    public $active_members;     // Int
    public $date_formed;        // Int/Timestamp

    // extended information
    public $tag;                // String
    public $slogan;             // String
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
