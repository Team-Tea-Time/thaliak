<?php

namespace Thaliak\Http\Lodestone;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class Api
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $uri;

    /**
     * Create a new Lodestone API instance.
     *
     * @param  Client  $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->uri = 'http://eu.finalfantasyxiv.com/lodestone/';
    }

    /**
     * Get a crawler instance.
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    protected function getCrawler($path)
    {
        return $this->client->request('GET', "{$this->uri}{$path}");
    }

    /**
     * Search for a character by name and world.
     *
     * @param  string  $name
     * @param  string  $world
     * @return \Illuminate\Support\Collection
     */
    public function searchCharacter($name, $world)
    {
        $crawler = $this->getCrawler("character/?q={$name}&worldname={$world}");
        $results = $crawler
            ->filter('.table_black_border_bottom > table tr')
            ->each(function (Crawler $node, $i) use ($world) {
                // ID & name
                $link = $node->filter('.player_name_area a')->first();
                $id = preg_replace('/[^0-9]/', '', $link->attr('href'));
                $name = $link->text();

                // Avatar
                $avatar = $node->filter('th img')->first()->attr('src');

                return new Character(compact('id', 'name', 'world', 'avatar'));
            });

        return collect($results);
    }

    /**
     * Get a character by ID.
     *
     * @param  int  $id
     * @return Character|mixed
     */
    public function getCharacter($id)
    {
        $crawler = $this->getCrawler("character/{$id}");

        $profileBoxes = $crawler->filter('.chara_profile_footer .chara_profile_box_info');

        // Name
        $name = $crawler->filter('.player_name_txt h2 a')->text();

        // World
        $world = str_replace(' ', '', str_replace(['(', ')'], '', $crawler->filter('.player_name_txt h2 span')->text()));

        // Race, clan and gender
        list($race, $clan, $gender) = explode(' / ', $crawler->filter('.chara_profile_footer .chara_profile_title')->text());
        $gender = ($gender == '♂') ? 'Male' : 'Female';

        // Avatar
        $avatar = $crawler->filter('.player_name_thumb img')->attr('src');

        // Portrait
        $portrait = $crawler->filter('#chara_img_area .img_area img')->attr('src');

        // Introduction
        $introduction = $crawler->filter('.txt_selfintroduction')->html();

        // Nameday
        $nameday = $profileBoxes->first()->filter('.txt_name')->eq(0)->text();

        // Guardian
        $guardian = $profileBoxes->first()->filter('.txt_name')->eq(1)->text();

        // City state
        $city_state = $profileBoxes->eq(1)->filter('.txt_name')->text();

        // Grand company
        $node = $profileBoxes->eq(2)->filter('.txt_name');
        $grand_company = $node->count() ? $node->text() : '';

        // Active class
        $class = $crawler->filter('#class_info')->first();
        $classImage = $class->filter('.ic_class_wh24_box img')->first()->attr('src');
        $matches = [];
        preg_match('^.*\/class\/\d*\/(.*)\.png^', $classImage, $matches);
        $classID = $matches[1];
        $active_class = ['id' => $classID, 'level' => preg_replace('/[^0-9]/', '', $class->filter('.level')->text())];

        return new Character(
            compact(
                'id', 'name', 'world', 'gender', 'avatar', 'portrait', 'introduction', 'race',
                'clan', 'nameday', 'guardian', 'city_state', 'grand_company', 'active_class'
            )
        );
    }
}
