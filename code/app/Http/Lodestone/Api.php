<?php

namespace Thaliak\Http\Lodestone;

use Goutte\Client;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

class Api
{
    protected $client;  // Client
    protected $uri;     // String

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->uri = 'http://eu.finalfantasyxiv.com/lodestone/';
    }

    protected function getCrawler($path): Crawler
    {
        return $this->client->request('GET', "{$this->uri}{$path}");
    }

    public function searchCharacter(String $name, String $world): Collection
    {
        $crawler = $this->getCrawler("character/?q={$name}&worldname={$world}");
        $results = $crawler
            ->filter('.ldst__window .entry')
            ->each(function (Crawler $node, $i) use ($world) {
                // ID & name
                $link = $node->filter('.entry__link')->first();
                $id = preg_replace('/[^0-9]/', '', $link->attr('href'));
                $name = $link->filter('.entry__name')->text();

                // Avatar
                $avatar = $node->filter('.entry__chara__face img')->first()->attr('src');

                return new Character(compact('id', 'name', 'world', 'avatar'));
            });

        return collect($results);
    }

    public function getCharacter(Int $id): Character
    {
        $attributes = compact('id');

        $crawler = $this->getCrawler("character/{$id}");

        // Name
        $attributes['name'] = $crawler->filter('.frame__chara__name')->text();

        // World
        $attributes['world'] = $crawler->filter('.frame__chara__world')->text();

        // Avatar
        $attributes['avatar'] = $crawler->filter('.frame__chara__face img')->attr('src');

        // Portrait
        $attributes['portrait'] = $crawler->filter('.character__view .character__detail__image a img')->attr('src');

        // Profile blocks
        $profileBlocks = $crawler->filter('.character__profile__data__detail .character-block__box');
        $profileBlocks->each(function ($node) use (&$attributes) {
            $node->filter('.character-block__title')->each(function ($node) use (&$attributes) {
                switch ($node->text()) {
                    case 'Race/Clan/Gender':
                        $matches = [];
                        preg_match(
                            '/(.*)<br>(.*)\s+\/\s+(♂|♀)/',
                            $node->nextAll()->html(),
                            $matches
                        );
                        array_shift($matches);
                        list($race, $clan, $gender) = $matches;
                        $gender = ($gender == '♂') ? 'Male' : 'Female';

                        $attributes += compact('race', 'clan', 'gender');
                        break;
                    case 'Nameday':
                        $attributes['nameday'] = $node->nextAll()->text();
                        break;
                    case 'Guardian':
                        $attributes['guardian'] = $node->nextAll()->text();
                        break;
                    case 'City-state':
                        $attributes['city_state'] = $node->nextAll()->text();
                        break;
                    case 'Grand Company':
                        $attributes['grand_company'] = $node->nextAll()->text();
                        break;
                }
            });
        });

        // Active class
        $class = $crawler->filter('.character__class');
        $classImage = $class->filter('.character__class_icon img')->attr('src');
        $matches = [];
        preg_match('/^.*\/(.+).png$/', $classImage, $matches);
        $classID = $matches[1];

        $attributes['active_class'] = [
            'id' => $classID,
            'level' => preg_replace(
                '/[^0-9]/',
                '',
                $class->filter('.character__class__data p')->first()->text()
            )
        ];

        return new Character($attributes);
    }
}
