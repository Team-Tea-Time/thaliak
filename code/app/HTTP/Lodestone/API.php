<?php

namespace Thaliak\HTTP\Lodestone;

use Goutte\Client;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Lodestone API
 *
 * The FFXIV Lodestone website provides search results and
 * detailed profiles for characters and free companies.
 *
 * First, we use a Goutte client object to call the FFXIV
 * Lodestone website and return a Symfony crawler object for
 * the returned HTML page.
 *
 * Next, we use the crawler object to parse the relevant
 * character/free company entries and return an appropriately
 * populated character or free company object.
 *
 * Note: At some point, this should probably be refactored
 * and split up, but seeing as the Crawler class does the
 * bulk of the work and with the differences between character
 * and fc, there isn't an overall benefit at this time.
 */
class API
{
    protected $client;
    protected $uri;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->uri = 'http://eu.finalfantasyxiv.com/lodestone/';
    }

    /**
     * Return a crawler object for the given lodestone path.
     *
     * @param String $path
     *
     * @return Crawler
     */
    protected function getCrawler(String $path): Crawler
    {
        return $this->client->request('GET', "{$this->uri}{$path}");
    }

    /**
     * Return a collection of characters, populated with basic
     * info, for all characters matching the given name and
     * world.
     *
     * Lodestone will match all characters with $name in either
     * the first or last name and each word within the string
     * will count as a separate match.
     *
     * @param String $name
     * @param String $worldname
     *
     * @return Collection|false
     */
    public function findCharacters(String $name, String $worldname)
    {
        $crawler = $this->getCrawler("character/?q={$name}&worldname={$worldname}");

        $crawler = $crawler->fitler('.ldst__contents .ldst__window');

        if (!$crawler->count()) {
            return false;
        }

        $results = [];

        foreach ($crawler->filter('.entry') as $domElement) {
            try {
                $entry = new Crawler($domElement);

                $character = new Character();

                // ID
                $href = $entry->filter('.entry__link');
                if (!$href->count() || !preg_match('/\/(\d+)\/$/', $href->attr('href'), $matches)) {
                    // If the ID isn't recognised then just throw an
                    // exception and get out of here
                    throw new \InvalidArgumentException('Character ID not recognized.');
                }
                $character->id = trim($matches[1]);

                // Avatar
                $character->avatar = trim($entry->filter('.entry__chara__face img')->attr('src'));

                // Name
                $character->name = trim($entry->filter('.entry__name')->text());

                // World
                $character->world = trim($entry->filter('.entry__world')->text());

                // Grand Company (optional)
                $gc = $entry->filter('.js__tooltip');
                if ($gc->count() && preg_match('/(.*)\/(.*)/', $gc->attr('data-tooltip'), $matches)) {
                    $character->grandcompany = [
                        'name' => trim($matches[1]),
                        'rank' => trim($matches[2])
                    ];
                }

                // Free Company (optional)
                $fc = $entry->filter('.entry__freecompany__link');
                if ($fc->count() && preg_match('/\/(\d+)\/$/', $fc->attr('href'), $matches)) {
                    $character->freecompany = [
                        'id' => trim($matches[1]),
                        'crest' => $fc->filter('.list__ic__crest img')->each(function (Crawler $node) {
                            return trim($node->attr('src'));
                        }),
                        'name' => trim($fc->filter('span')->text()),
                        'rank' => null
                    ];
                }

                $results[] = $character;
            } catch (\Exception $e) {
                // TODO: Error handling
            }
        }

        return count($results) ? collect($results) : false;
    }

    /**
     * Return detailed information for the character matching
     * the given lodestone id.
     *
     * Information returned includes profile information,
     * free company and classes.
     *
     * Note: Mounts, minions, achievements, friends, etc, are
     * not returned at this time, as there is currently no
     * requirement for those details within xiv.world.
     *
     * @param String $id
     *
     * @return Character|false
     */
    public function getCharacter(String $id)
    {
        $crawler = $this->getCrawler("character/{$id}");

        $crawler = $crawler->filter('.ldst__contents .ldst__window');

        if (!$crawler->count()) {
            return false;
        }

        $entry = $crawler->eq(0);

        $character = new Character();

        try {
            // ID
            $href = $entry->filter('.frame__chara__link');
            if (!$href->count() || !preg_match("/\/({$id})\/$/", $href->attr('href'), $matches)) {
                // If the ID isn't valid or doesn't match the id we've
                // requested details for then just throw an exception
                throw new \InvalidArgumentException('Character ID not recognized or does not match.');
            }
            $character->id = $id;

            // Avatar
            $character->avatar = trim($entry->filter('.frame__chara__face img')->attr('src'));

            // Name
            $character->name = trim($entry->filter('.frame__chara__name')->text());

            // World
            $character->world = trim($entry->filter('.frame__chara__world')->text());

            // Portrait
            $character->portrait = trim($entry->filter('.character__view .character__detail__image img')->attr('src'));

            // Introduction
            $character->introduction = trim($entry->filter('.character__selfintroduction')->text());

            // Title
            $character->title = trim($entry->filter('.frame__chara__title')->text());

            // Profile blocks
            $entry->filter('.character__profile__data__detail .character-block__title')->each(function (Crawler $node) use (&$character) {
                switch (trim($node->text())) {
                    // Race, Clan and Gender
                    case 'Race/Clan/Gender':
                        if (preg_match('/(.*)<br>(.*)\s+\/\s+(♂|♀)/', $node->nextAll()->html(), $matches)) {
                            $character->race = trim($matches[1]);
                            $character->clan = trim($matches[2]);
                            $character->gender = (trim($matches[3]) == '♂') ? 'Male' : 'Female';
                        }
                        break;
                    // Nameday
                    case 'Nameday':
                        $character->nameday = trim($node->nextAll()->text());
                        break;
                    // Guardian
                    case 'Guardian':
                        $character->guardian = trim($node->nextAll()->text());
                        break;
                    // City-state
                    case 'City-state':
                        $character->citystate = trim($node->nextAll()->text());
                        break;
                    // Grand Company (optional)
                    case 'Grand Company':
                        if (preg_match('/(.*)\/(.*)/', $node->nextAll()->text(), $matches)) {
                            $character->grandcompany = [
                                'name' => trim($matches[1]),
                                'rank' => trim($matches[2])
                            ];
                        }
                        break;
                }
            });

            // Free Company (optional)
            $fc = $entry->filter('.character__freecompany__name');
            if ($fc->count() && preg_match('/\/(\d+)\/$/', $fc->filter('a')->attr('href'), $matches)) {
                $character->freecompany = [
                    'id' => trim($matches[1]),
                    'crest' => $entry->filter('.character__freecompany__crest__image img')->each(function (Crawler $node) {
                        return trim($node->attr('src'));
                    }),
                    'name' => trim($fc->filter('a')->text())
                ];
            }

            // Active Class/Job - we can get the active class from the
            // equipped main hand (slot 0) and we can tell if it's an
            // actual job from an equipped soul crystal (slot 12)
            $character->activeclass = trim(explode('\'', $entry->filter('.db-tooltip__item__category')->eq(0)->text())[0]);
            $soul = $entry->filter('.db-tooltip__item__name')->eq(12);
            if ($soul->count() && preg_match('/Soul of the (\w+)/', $soul->text(), $matches)) {
                $character->activeclass = trim($matches[1]);
            }

            // All class levels, with current and max xp
            $character->classes = [];
            $entry->filter('.character__job li')->each(function (Crawler $node) use (&$character) {
                $name = trim($node->filter('.character__job__name')->text());
                $level = trim($node->filter('.character__job__level')->text());
                list($current, $max) = explode('/', $node->filter('.character__job__exp')->text());
                $character->classes[strtolower($name)] = [
                    'name' => $name,
                    'level' => intval($level),
                    'exp' => [
                        'current' => intval($current),
                        'max' => intval($max)
                    ]
                ];
            });
        } catch (\Exception $e) {
            // TODO: Error handling
        }

        return $character;
    }

    /**
     * Return a collection of free companies, populated with
     * basic info, for all characters matching the given name
     * and world.
     *
     * Lodestone will match all free companies with $name in
     * within the company name and each word within the string
     * will count as a separate match.
     *
     * @param String $name
     * @param String $worldname
     *
     * @return Collection|false
     */
    public function findFreeCompanies(String $name, String $worldname)
    {
        $crawler = $this->getCrawler("freecompany/?q={$name}&worldname={$worldname}");

        $crawler = $crawler->filter('.ldst__contents .ldst__window');

        if (!$crawler->count()) {
            return false;
        }

        $results = [];

        foreach ($crawler->filter('.entry') as $domElement) {
            try {
                $entry = new Crawler($domElement);

                $freecompany = new FreeCompany();

                // ID
                $href = $entry->filter('.entry__block');
                if (!$href->count() || !preg_match('/\/(\d+)\/$/', $href->attr('href'), $matches)) {
                    // If an invalid free company id then just throw
                    // an exception and get out of here
                    throw new \InvalidArgumentException('Free Company ID not recognized.');
                }
                $freecompany->id = trim($matches[1]);

                // Crest - made up of 3 overlayed images
                $freecompany->crest = $entry->filter('.entry__freecompany__crest__image img')->each(function (Crawler $node) {
                    return trim($node->attr('src'));
                });

                // Name
                $freecompany->name = trim($entry->filter('.entry__name')->text());

                // World
                $freecompany->world = trim($entry->filter('.entry__world')->eq(1)->text());

                // Grand Company
                $freecompany->grandcompany = trim($entry->filter('.entry__world')->eq(0)->text());

                // Active Members
                $freecompany->activemembers = intval($entry->filter('.entry__freecompany__fc-member')->text());

                // Date Formed
                preg_match('/ldst_strftime\((\d+),/', $entry->filter('.entry__freecompany__fc-day')->text(), $matches);
                $freecompany->dateformed = isset($matches[1]) ? $matches[1] : 'n/a';

                $results[] = $freecompany;
            } catch (\Exception $e) {
                // TODO: Error handling
            }
        }

        return count($results) ? collect($results) : false;
    }

    /**
     * Return detailed information for the free company
     * matching the given lodestone id.
     *
     * @param String $id
     *
     * @return FreeCompany|false
     */
    public function getFreeCompany(String $id)
    {
        $crawler = $this->getCrawler("freecompany/{$id}");

        $crawler = $crawler->filter('.ldst__contents .ldst__window');

        if (!$crawler->count()) {
            return false;
        }

        // Free companies have two .ldst__window sections,
        // the first containing company information and the
        // second containing company activity/focus.
        $entry = $crawler->eq(0);

        if (!$entry->count()) {
            return false;
        }

        $freecompany = new FreeCompany();

        try {
            // ID
            $href = $entry->filter('.entry__freecompany');
            if (!$href->count() || !preg_match("/\/({$id})\/$/", $href->attr('href'), $matches)) {
                // If the ID isn't valid or doesn't match the id we've
                // requested details for then throw an exception
                throw new \InvalidArgumentException('Free Company ID not recognized or does not match.');
            }
            $freecompany->id = $id;

            // Crest - made up of 3 overlayed images
            $freecompany->crest = $entry->filter('.entry__freecompany__crest__image img')->each(function (Crawler $node) {
                return trim($node->attr('src'));
            });

            // Name
            $freecompany->name = trim($entry->filter('.freecompany__text__name')->text());

            // World
            $freecompany->world = trim($entry->filter('.entry__freecompany__gc')->eq(1)->text());

            // Grand Company
            $gc = $entry->filter('.entry__freecompany__gc')->eq(0);
            if ($gc->count() && preg_match('/^([A-Z][A-Za-z ]+)\s+</', $gc->text(), $matches)) {
                $freecompany->grandcompany = $matches[1];
            }

            // Profile blocks
            $entry->filter('.heading--lead')->each(function (Crawler $node) use (&$freecompany) {
                switch (trim($node->text())) {
                    // Company Slogan
                    case 'Company Slogan':
                        $freecompany->slogan = trim($node->nextAll()->text());
                        break;
                    // Date formed - timestamp grabbed from a call to
                    // a javascript function ldst_strftime()
                    case 'Formed':
                        preg_match('/ldst_strftime\((\d+),/', $node->nextAll()->text(), $matches);
                        $freecompany->dateformed = isset($matches[1]) ?  intval($matches[1]) : 'n/a';
                        break;
                    // Number of members
                    case 'Active Members':
                        $freecompany->activemembers = intval($node->nextAll()->text());
                        break;
                    // Rank
                    case 'Rank':
                        $freecompany->rank = intval($node->nextAll()->text());
                        break;
                }
            });

            // Company Tag - between « and »
            $freecompany->tag = trim(str_replace(['«','»'], '', $entry->filter('.freecompany__text.freecompany__text__tag')->text()));

            // Reputation/Standing
            $freecompany->standing = [];
            $entry->filter('.freecompany__reputation .freecompany__reputation__gcname')->each(function (Crawler $node) use (&$freecompany) {
                $name = trim($node->text());
                $standing = trim($node->nextAll()->text());
                $freecompany->standing[$name] = $standing;
            });

            // Estate Profile
            $freecompany->estate = [
                'name' => trim($entry->filter('.freecompany__estate__name')->text()),
                'address' => trim($entry->filter('.freecompany__estate__text')->text()),
                'greeting' => trim($entry->filter('.freecompany__estate__greeting')->text())
            ];

            // We now need to process the second .ldst__window section
            // for the company focus and activity.
            $entry = $crawler->eq(1);

            if ($entry->count()) {
                // Activity and Recruitment
                $entry->filter('.heading--lead')->each(function (Crawler $node) use (&$freecompany) {
                    switch (trim($node->text())) {
                        case 'Active':
                            $freecompany->active = trim($node->nextAll()->text());
                            break;
                        case 'Recruitment':
                            $freecompany->recruitment = trim($node->nextAll()->text());
                            break;
                    }
                });

                // Details on company focus and seeking are found in
                // two icon lists, the first for focus and second for
                // seeking. In both instances the focus_icon--off class
                // is used to determine if that option is on or off.
                $focus = $entry->filter('ul.freecompany__focus_icon');

                // Focus
                $freecompany->focus = [];
                $focus->eq(0)->filter('li')->each(function(Crawler $node) use (&$freecompany) {
                    $freecompany->focus[strtolower(trim($node->filter('p')->text()))] = !in_array('freecompany__focus_icon--off', $node->extract('class'));
                });

                // Seeking
                $freecompany->seeking = [];
                $focus->eq(1)->filter('li')->each(function(Crawler $node) use (&$freecompany) {
                    $freecompany->seeking[strtolower(trim($node->filter('p')->text()))] = !in_array('freecompany__focus_icon--off', $node->extract('class'));
                });
            }
        } catch (\Exception $e) {
            // TODO: Error handling
            return false;
        }

        return $freecompany;
    }

    /**
     * Return a collection of free company members, populated
     * with basic character info, for all members of the free
     * company matching the given id.
     *
     * The free company members are obtained by performing
     * successive paged lodestone requests to the members
     * endpoint.
     *
     * @param String $id
     *
     * @return Collection|false
     */
    public function getFreeCompanyMembers(String $id)
    {
        $results = [];

        $page = 1;

        do {
            $crawler = $this->getCrawler("freecompany/{$id}/member/?page={$page}");

            $crawler = $crawler->filter('.ldst__contents .ldst__window');

            if (!$crawler->count()) {
                break;
            }

            // Validate the ID
            $href = $crawler->filter('.entry__freecompany');
            if (!$href->count() || !preg_match("/\/({$id})\/$/", $href->attr('href'), $matches)) {
                // If the ID isn't valid or doesn't match the id we've
                // requested details for then just bail
                break;
            }

            // Process the member list on the current page
            foreach ($crawler->filter('li.entry') as $domElement) {
                try {
                    $entry = new Crawler($domElement);

                    $member = new FreeCompanyMember();

                    // ID
                    $href = $entry->filter('.entry__bg');
                    if (!$href->count() || !preg_match("/\/(\d+)\/$/", $href->attr('href'), $matches)) {
                        // If the ID isn't valid or doesn't match the id we've
                        // requested details for then throw an exception
                        throw new \InvalidArgumentException('Character ID for the free company member not recognized.');
                    }
                    $member->id = trim($matches[1]);

                    // Name
                    $member->name = trim($entry->filter('.entry__name')->text());

                    // Free Company
                    $member->rank = trim($entry->filter('.entry__freecompany__info li span')->text());

                    $results[] = $member;
                } catch (\Exception $e) {
                    // TODO: Error handling
                }
            }

            // Attempt to get the current page of pages and
            // continue the loop with the next page if we
            // haven't just processed the last page
            if (preg_match('/Page\s*(\d+)\s*of\s*(\d+)/', $crawler->filter('.btn__pager__current')->text(), $matches)) {
                $page = intval($matches[1]);
                $pages = intval($matches[2]);
            } else {
                break;
            }
        } while (++$page <= $pages);

        return count($results) ? collect($results) : false;
    }
}
