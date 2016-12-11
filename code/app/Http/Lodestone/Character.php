<?php

namespace Thaliak\Http\Lodestone;

class Character
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $world;

    /**
     * @var string
     */
    public $gender;

    /**
     * @var string
     */
    public $avatar;

    /**
     * @var string
     */
    public $portrait;

    /**
     * @var string
     */
    public $introduction;

    /**
     * @var string
     */
    public $race;

    /**
     * @var string
     */
    public $clan;

    /**
     * @var string
     */
    public $nameday;

    /**
     * @var string
     */
    public $guardian;

    /**
     * @var string
     */
    public $city_state;

    /**
     * @var string
     */
    public $grand_company;

    /**
     * @var array
     */
    public $active_class;

    /**
     * Create a new Lodestone character instance.
     *
     * @param  array  $data
     */
    public function __construct($data)
    {
        foreach ($data as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    /**
     * Return a string representation of the character.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
