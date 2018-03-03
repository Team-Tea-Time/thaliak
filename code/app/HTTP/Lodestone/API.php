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
        $this->uri = 'https://eu.finalfantasyxiv.com/lodestone/';
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
    public function findCharacters(String $name, String $worldname=null, $page=null)
    {
        // Get the crawler for the character search results
        $name = urlencode($name);
        $worldname = urlencode($worldname);
        $page = urlencode($page);

        $crawler = $this->getCrawler(
            "character/?q={$name}&worldname={$worldname}&page={$page}"
        );

        // Find the lodestone window element
        $crawler = $crawler->filter('.ldst__contents .ldst__window');

        if (!$crawler->count()) {
            return false;
        }

        $results = [];

        // Get the list of characters that have been returned by the
        // lodestone api, with each character being found in a div
        // with class="entry"
        foreach ($crawler->filter('.entry') as $entry) {
            try {
                $c = new CharacterListing();

                $entry = new Crawler($entry);
                foreach ($entry->filter('a') as $node) {
                    switch ($node->getAttribute('class')) {
                        // character link
                        case 'entry__link':
                            // ID
                            if (!preg_match('/\/(\d+)\/$/', $node->getAttribute('href'), $matches)) {
                                throw new \InvalidArgumentException('Character ID not recognized.');
                            }
                            $c->id = $matches[1];

                            foreach($node->childNodes as $node) {
                                switch ($node->getAttribute('class')) {
                                    // Avatar
                                    case 'entry__chara__face':
                                        try {
                                            $c->avatar = (new Crawler($node))->filter('img')->attr('src');
                                        } catch (\Exception $e) {
                                        }
                                        break;

                                    // name/world/gc box
                                    case 'entry__box entry__box--world':
                                        foreach($node->childNodes as $node) {
                                            switch ($node->getAttribute('class')) {
                                                // Name
                                                case 'entry__name':
                                                    $c->name = $node->textContent;
                                                    break;

                                                // World
                                                case 'entry__world':
                                                    $c->world = $node->textContent;
                                                    break;

                                                // Grand company
                                                case 'entry__chara_info':
                                                    try {
                                                        $gc = (new Crawler($node))->filter('.js__tooltip')->attr('data-tooltip');
                                                        $gc = explode('/', $gc);
                                                        $c->grandcompany = [
                                                            'name' => trim($gc[0]),
                                                            'rank' => trim($gc[1])
                                                        ];
                                                    } catch (\Exception $e) {
                                                    }
                                                    break;
                                            }
                                        }
                                        break;

                                }
                            }
                            break;

                        // free company link
                        case 'entry__freecompany__link':
                            if (preg_match('/\/(\d+)\/$/', $node->getAttribute('href'), $matches)) {
                                // ID
                                $c->freecompany['id'] = $matches[1];
                                try {
                                    // crest/name
                                    $c->freecompany = [
                                        'crest' => (new Crawler($node))->filter('.list__ic__crest img')->each(function (Crawler $node) {
                                              return $node->attr('src');
                                        }),
                                        'name' => $node->getElementsByTagName('span')[0]->textContent
                                    ];
                                } catch (\Exception $e) {
                                }
                            }
                            break;
                    }
                }

                $results[] = $c;
            } catch (\Exception $e) {
                // TODO: Error handling - just skip to next for now
            }
        }

        // Return results or empty array if no matches found
        if (!empty($results)) {
            try {
                $pager = $crawler->filter('.btn__pager__current');
                list($page, $pages) = explode(' of ', $pager->text());
            } catch (\Except $e) {
                $page = $pages = 1;
            }

            return [
                'page' => (int)$page,
                'pages' => (int)$pages,
                'count' => count($results),
                'results' => $results
            ];
        }
        return [];
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

        // Find the lodestone window element
        $crawler = $crawler->filter('.ldst__contents .ldst__window');

        if (!$crawler->count()) {
            return false;
        }

        $entry = $crawler->eq(0);

        $c = new Character();

        // ID
        $node = $entry->filter('.frame__chara .frame__chara__link');
        if (!$node->count() || !preg_match("/\/({$id})\/$/", $node->attr('href'), $matches)) {
            // If the ID isn't valid or doesn't match the id we've
            // requested details for then just throw an exception
            throw new \InvalidArgumentException('Character ID not recognized or does not match.');
        }
        $c->id = $id;

        foreach ($node->children() as $node) {
            switch($node->getAttribute('class')) {
                // Avatar
                case 'frame__chara__face':
                    try {
                        $c->avatar = (new Crawler($node))->filter('img')->attr('src');
                    } catch (\Exception $e) {
                    }
                    break;

                // name/title/world
                case 'frame__chara__box':
                    foreach ((new Crawler($node))->children() as $node) {
                        switch($node->getAttribute('class')) {
                            // Name
                            case 'frame__chara__name':
                                $c->name = $node->textContent;
                                break;

                            // Title
                            case 'frame__chara__title':
                                $c->title = $node->textContent;
                                break;

                            // World
                            case 'frame__chara__world':
                                $c->world = $node->textContent;
                                break;
                        }
                    }
                    break;
            }
        }

        foreach ($entry->filter('.character__content') as $entry) {
            // Each block of character data consists of:
            //    <h3 class="heading--md">{title}<h3>
            //    <div class="clearfix">{block data}</div>
            //    ...

            $entry = new Crawler($entry);

            // first child should be the title
            $title = $entry->children()->eq(0);
            if (!$title || $title->nodeName() != 'h3') {
                continue;
            }

            switch ($title->text()) {
                // profile blocks
                case 'Profile':
                    foreach ($entry->filter('.character-block__box .character-block__title') as $node) {
                        $next = (new Crawler($node))->nextAll();
                        if ($next->count()) {
                            switch ($node->textContent) {
                                // Race/Clan/Gender
                                case 'Race/Clan/Gender':
                                    if (preg_match('/(.*)<br>(.*)\s+\/\s+(♂|♀)/', $next->html(), $matches)) {
                                        $c->race = trim($matches[1]);
                                        $c->clan = trim($matches[2]);
                                        $c->gender = (trim($matches[3]) == '♂') ? 'Male' : 'Female';
                                    }
                                    break;

                                // Nameday
                                case 'Nameday':
                                    $c->nameday = trim($next->text());
                                    break;

                                // Guardian
                                case 'Guardian':
                                    $c->guardian = trim($next->text());
                                    break;

                                // City-state
                                case 'City-state':
                                    $c->citystate = trim($next->text());
                                    break;

                                // Grand Company (optional)
                                case 'Grand Company':
                                    try {
                                        $gc = explode('/', $next->text());
                                        $c->grandcompany = [
                                            'name' => trim($gc[0]),
                                            'rank' => trim($gc[1])
                                        ];
                                    } catch (\Exception $e) {
                                    }
                                    break;
                            }
                        }
                    }

                    // Free Company (optional)
                    try {
                        $fc = $entry->filter('.character__freecompany__name h4 a');
                        if ($fc->count() && preg_match('/\/(\d+)\/$/', $fc->attr('href'), $matches)) {
                            $c->freecompany = [
                                'id' => trim($matches[1]),
                                'crest' => $entry->filter('.character__freecompany__crest__image img')->each(function (Crawler $node) {
                                    return trim($node->attr('src'));
                                }),
                                'name' => trim($fc->text())
                            ];
                        }
                    } catch (\Exception $e) {
                    }

                    $detail = $entry->filter('.character__view');
                    if ($detail->count()) {
                        foreach ($detail->children() as $node) {
                            switch ($node->getAttribute('class')) {
                                // Active Class
                                case 'character__class':
                                    try {
                                        $node = (new Crawler($node))->filter('.character__class__arms .db-tooltip__item__category');
                                        $c->activeclass = trim(explode('\'', $node->text())[0]);
                                    } catch (\Exception $e) {
                                    }
                                    break;

                                case 'character__detail':
                                    // update active class with info from soul if equipped
                                    $soul = (new Crawler($node))->filter('.icon-c--13 .db-tooltip__item__name');
                                    if ($soul->count() && preg_match('/Soul of the (\w+)/', $soul->text(), $matches)) {
                                        $c->activeclass = trim($matches[1]);
                                    }

                                    // Portrait
                                    $node = (new Crawler($node))->filter('.character__detail__image a');
                                    $c->portrait = $node->count() ? $node->attr('href') : '';
                                    break;
                            }
                        }
                    }

                    // Introduction
                    $node = $entry->filter('div.character__selfintroduction');
                    $c->introduction = $node->count() ? trim($node->text()) : '';
                    break;

                // attributes
                case 'Attributes':
                    // ignore for now
                    break;

                // classes
                case 'DoW/DoM':
                case 'DoH/DoL':
                    foreach ($entry->filter('.character__job__role .character__job li') as $node) {
                        $class = [];
                        foreach ($node->childNodes as $node) {
                            switch($node->getAttribute('class')) {
                                // Name
                                case 'character__job__name js__tooltip':
                                case 'character__job__name character__job__name--meister js__tooltip':
                                    $class['name'] = trim($node->textContent);
                                    break;
                                // Level
                                case 'character__job__level':
                                    $class['level'] = trim($node->textContent);
                                    break;
                                // Exp
                                case 'character__job__exp':
                                    list($current, $max) = explode('/', $node->textContent);
                                    $class['exp'] = [
                                        'current' => intval($current),
                                        'max' => intval($max)
                                    ];
                                    break;
                            }
                        }
                        if (isset($class['name'])) {
                            $c->classes[strtolower($class['name'])] = $class;
                        }
                    }
                    break;

                // ignore for now
                case 'Mounts':
                case 'Minions':
                    break;
            }
        }

        return $c;
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
    public function findFreeCompanies(String $name, String $worldname=null, $page=null)
    {
        // Get the crawler for the free company search results
        $name = urlencode($name);
        $worldname = urlencode($worldname);
        $page = urlencode($page);

        $crawler = $this->getCrawler(
            "freecompany/?q={$name}&worldname={$worldname}&page={$page}"
        );

        // Find the lodestone window element
        $crawler = $crawler->filter('.ldst__contents .ldst__window');

        if (!$crawler->count()) {
            return false;
        }

        $results = [];

        // Get the list of freecompanies that have been returned by
        // the lodestone api, with each character being found in an
        // anchor with class="entry__block"
        foreach ($crawler->filter('.entry__block') as $entry) {
            try {
                $fc = new FreeCompanyListing();

                // ID
                if (!preg_match('/\/(\d+)\/$/', $entry->getAttribute('href'), $matches)) {
                    // If an invalid free company id then just throw
                    // an exception and get out of here
                    throw new \InvalidArgumentException('Free Company ID not recognized.');
                }
                $fc->id = $matches[1];

                // $entry = new Crawler($entry);
                foreach ($entry->childNodes as $node) {
                    switch ($node->getAttribute('class')) {
                        case 'entry__freecompany__inner':
                            foreach ($node->childNodes as $node) {
                                switch ($node->getAttribute('class')) {
                                    // Crest
                                    case 'entry__freecompany__crest':
                                        $fc->crest = (new Crawler($node))->filter('.entry__freecompany__crest__image img')->each(function (Crawler $node) {
                                            return $node->attr('src');
                                        });
                                        break;

                                    // name/world/gc
                                    case 'entry__freecompany__box':
                                        foreach ($node->childNodes as $node) {
                                            switch ($node->getAttribute('class')) {
                                                // Name
                                                case 'entry__name':
                                                    $fc->name = $node->textContent;
                                                    break;

                                                // World
                                                case 'entry__world':
                                                    // first entry is gc, second is world
                                                    if (empty($f->grandcompany)) {
                                                        $fc->grandcompany = $node->textContent;
                                                    } else {
                                                        $fc->world = $node->textContent;
                                                    }
                                                    break;
                                            }
                                        }
                                        break;
                                }
                            }
                            break;

                        // members/estate/dateformed
                        case 'entry__freecompany__fc-data clearix':
                            foreach ($node->childNodes as $node) {
                                switch ($node->getAttribute('class')) {
                                    // Active members
                                    case 'entry__freecompany__fc-member':
                                        $fc->activemembers = $node->textContent;
                                        break;

                                    // Estate
                                    case 'entry__freecompany__fc-housing':
                                        $fc->estate = $node->textContent;
                                        break;

                                    // Date formed
                                    case 'entry__freecompany__fc-day':
                                        if (preg_match('/ldst_strftime\((\d+),/', $node->textContent, $matches)) {
                                            $fc->dateformed = isset($matches[1]) ? $matches[1] : 'n/a';
                                        }
                                        break;
                                }
                            }
                            break;
                    }
                }

                $results[] = $fc;
            } catch (\Exception $e) {
                // TODO: Error handling
            }
        }

        // Return results or an empty array if no matches found
        if (!empty($results)) {
            // TODO: add pager info
            return [
                'count' => count($results),
                'results' => $results
            ];
        }
        return [];
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

        // Find the lodestone window elements
        $crawler = $crawler->filter('.ldst__contents .ldst__window');

        if (!$crawler->count()) {
            return false;
        }

        // Free companies have two .ldst__window sections, the first
        // containing company information and the second containing
        // company activity/focus.
        $entry = $crawler->eq(0);

        if (!$entry->count()) {
            return false;
        }

        $fc = new FreeCompany();

        // ID
        $node = $entry->filter('.entry .entry__freecompany');
        if (!$node->count() || !preg_match("/\/({$id})\/$/", $node->attr('href'), $matches)) {
            // If the ID isn't valid or doesn't match the id we've
            // requested details for then throw an exception
            throw new \InvalidArgumentException('Free Company ID not recognized or does not match.');
        }
        $fc->id = $id;

        // Crest
        try {
            $fc->crest = $node->filter('.entry__freecompany__crest .entry__freecompany__crest__image img')->each(function (Crawler $node) {
                return $node->attr('src');
            });
        } catch (\Exception $e) {
        }

        // name/world/gc
        $node = $node->filter('.entry__freecompany__box');
        if ($node->count()) {
            foreach ($node->children() as $node) {
                switch ($node->getAttribute('class')) {
                    // Name
                    case 'entry__freecompany__name':
                        $fc->name = $node->textContent;
                        break;

                    // gc/world
                    case 'entry__freecompany__gc':
                        if (empty($f->grandcompany)) {
                            preg_match('/^([A-Z][A-Za-z ]+)\s+</', $node->textContent, $matches);
                            $fc->grandcompany = isset($matches[1]) ? $matches[1] : 'n/a';
                        } else {
                            $fc->world = trim($node->textContent);
                        }
                        break;
                }
            }
        }

        // Tag
        $node = $entry->filter('p.freecompany__text.freecompany__text__tag');
        $fc->tag = $node->count() ? trim(str_replace(['«','»'], '', $node->text())) : '';

        $nodes = $entry->filter('h3.heading--lead');
        if ($nodes->count()) {
            $nodes->each(function (Crawler $node) use (&$f) {
                $next = $node->nextAll();
                if ($next->count()) {
                    switch (trim($node->text())) {
                        // Company slogan
                        case 'Company Slogan':
                            $fc->slogan = trim($next->text());
                            break;

                        // Date formed
                        case 'Formed':
                            preg_match('/ldst_strftime\((\d+),/', $next->text(), $matches);
                            $fc->dateformed = isset($matches[1]) ?  intval($matches[1]) : 'n/a';
                            break;

                        // Acive members
                        case 'Active Members':
                            $fc->activemembers = intval($next->text());
                            break;

                        // Rank
                        case 'Rank':
                            $fc->rank = intval($next->text());
                            break;
                    }
                }
            });
        }

        // Standing
        $entry->filter('.freecompany__reputation .freecompany__reputation__gcname')->each(function (Crawler $node) use (&$f) {
            $next = $node->nextAll();
            $fc->standing[$node->text()] = $next->count() ? $next->text() : 'n/a';
        });

        // Estate Profile
        $estate = $entry->filter('.freecompany__estate__name');
        $fc->estate['name'] = $estate->count() ? $estate->text() : '';
        $estate = $entry->filter('.freecompany__estate__text');
        $fc->estate['address'] = $estate->count() ? $estate->text() : '';
        $estate = $entry->filter('.freecompany__estate__greeting');
        $fc->estate['greeting'] = $estate->count() ? $estate->text() : '';

        // Now process the second lodestone section
        $entry = $crawler->eq(1);

        if ($entry->count()) {
            $entry->filter('h3.heading--lead')->each(function (Crawler $node) use (&$f) {
                $next = $node->nextAll();
                if ($next->count()) {
                    switch (trim($node->text())) {
                        // Active
                        case 'Active':
                            $fc->active = trim($next->text());
                            break;

                        // Recruitment
                        case 'Recruitment':
                            $fc->recruitment = trim($next->text());
                            break;

                        // Focus
                        case 'Focus':
                            foreach ($next->children() as $focus) {
                                $fc->focus[trim($focus->textContent)] =  strpos($focus->getAttribute('class'), 'freecompany__focus_icon--off') === false;
                            }
                            break;

                        // Seeking
                        case 'Seeking':
                            foreach ($next->children() as $seeking) {
                                $fc->seeking[trim($seeking->textContent)] =  strpos($seeking->getAttribute('class'), 'freecompany__focus_icon--off') === false;
                            }
                            break;
                    }
                }
            });
        }

        return $fc;
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
            foreach ($crawler->filter('li.entry .entry__bg') as $entry) {
                try {
                    $m = new FreeCompanyMember();

                    // ID
                    if (!preg_match("/\/(\d+)\/$/", $entry->getAttribute('href'), $matches)) {
                        throw new \InvalidArgumentException('Character ID for the free company member not recognized.');
                    }
                    $m->id = trim($matches[1]);

                    $entry = new Crawler($entry);
                    foreach ($entry->filter('.entry__freecompany__center')->children() as $node) {
                        switch ($node->getAttribute('class')) {
                            // Name
                            case 'entry__name':
                                $m->name = $node->textContent;
                                break;

                            // Rank
                            case 'entry__freecompany__info':
                                try {
                                    $m->rank = (new Crawler($node))->filter('li span')->text();
                                } catch (\Exception $e) {
                                }
                                break;
                        }
                    }

                    $results[] = $m;
                } catch (\Exception $e) {
                    // TODO: Error handling - just skip to next for now
                }
            }

            // Attempt to get the current page of pages and continue the
            // loop until we've processed the last page
            try {
                $pager = $crawler->filter('.btn__pager__current');
                list($page, $pages) = explode(' of ', $pager->text());
            } catch (\Except $e) {
                $pages = $page;
            }
        } while (++$page <= $pages);

        // Return the results
        if (!empty($results)) {
            return [
                'count' => count($results),
                'results' => $results
            ];
        }
        return [];
    }
}
