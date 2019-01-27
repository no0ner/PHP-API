<?php

namespace PaladinsDev\PHP;

use Carbon\Carbon;
use Onoi\Cache\Cache;
use PaladinsDev\PHP\Exceptions\PaladinsException;
use PaladinsDev\PHP\Exceptions\SessionException;

/**
 * Paladins API
 * 
 * This class is the entry point and the main class that you will use to interact with the Hi-Rez/Evil Mojo API.
 * 
 * @author Matthew Hatcher <matthewh@halfpetal.com>
 * @copyright 2018 Halfpetal LLC
 * @license Apache-2.0
 * @link https://github.com/PaladinsDev/PHP-API
 */
class PaladinsAPI
{
    /**
     * The developer id given to the developer upon approval.
     *
     * @var string
     */
    private $devId;
    
    /**
     * The auth key given to authorize the requests to the server.
     *
     * @var string
     */
    private $authKey;

    /**
     * Sets the language id for API usage.
     *
     * @var integer
     */
    private $languageId;

    /**
     * The API endpoint, never changed.
     * 
     * @var string
     */
    private $apiUrl;

    /**
     * The Guzzle client used to make requests.
     *
     * @var \GuzzleHttp\Client
     */
    private $guzzleClient;

    /**
     * The cache driver we interact with
     *
     * @var \Onoi\Cache\Cache
     */
    private $cache;

    /**
     * If you don't use a cache driver, we'll store locally.
     *
     * @var string
     */
    private $sessionId;

    /**
     * Session expiration time.
     *
     * @var [type]
     */
    private $sessionExpiresAt;

    /**
     * Holds the current instance of the Paladins API class.
     *
     * @var PaladinsAPI
     */
    private static $instance;

    public function __construct(string $devId, string $authKey, Cache $cacheDriver = null)
    {
        $this->devId        = $devId;
        $this->authKey      = $authKey;
        $this->cache        = $cacheDriver;
        $this->languageId   = 1;
        $this->apiUrl       = 'http://api.paladins.com/paladinsapi.svc';
        $this->guzzleClient = new \GuzzleHttp\Client;
    }

    /**
     * Get the current instance of the Paladins API. Useful for singleton based applications.
     *
     * @param string $devId
     * @param string $authKey
     * @param \Onoi\Cache\Cache $cacheDriver
     * @return PaladinsAPI
     */
    public static function getInstance(string $devId = null, string $authKey = null, Cache $cacheDriver = null)
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($devId, $authKey, $cacheDriver);
        }

        return self::$instance;
    }

    /**
     * Get the top 50 most watched/recent matches.
     *
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getTopMatches()
    {
        return $this->makeRequest($this->buildUrl('gettopmatches'));
    }

    /** 
     * @codeCoverageIgnore
     */
    public function getMatchIdsByQueue(string $hour, $date, int $queue = 424)
    {
        return $this->makeRequest("{$this->apiUrl}/getmatchidsbyqueueJson/{$this->devId}/{$this->getSignature('getmatchidsbyqueue')}/{$this->getSession()}/{$this->getTimestamp()}/{$queue}/{$date}/{$hour}");
    }

    /**
     * Get all the champions for the game.
     *
     * @return mixed
     */
    public function getChampions()
    {
        return $this->makeRequest($this->buildUrl('getchampions', null, $this->languageId));
    }

    /**
     * Get all the available cards for the requested champion.
     *
     * @param integer $championId
     * @return mixed
     */
    public function getChampionCards(int $championId)
    {
        return $this->makeRequest($this->buildUrl('getchampioncards', null, $this->languageId, null, $championId));
    }

    /**
     * Get all the available skins for the requested champion.
     *
     * @param integer $championId
     * @return mixed
     */
    public function getChampionSkins(int $championId)
    {
        return $this->makeRequest($this->buildUrl('getchampionskins', null, $this->languageId, null, $championId));
    }

    /**
     * Get all the available in game items.
     *
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getItems()
    {
        return $this->makeRequest($this->buildUrl('getitems', null, $this->languageId));
    }

    /**
     * Get a player and their details from the API.
     *
     * @param mixed $player
     * @param int $platform
     * @return mixed
     */
    public function getPlayer($player, int $platform = 5)
    {
        if (!is_string($player) && !is_int($player))
        {
            throw new PaladinsException('The player must be either a name, string, or a player id, integer.');
        }

        if (is_string($player)) {
            $players = $this->getPlayerIdByName($player);

            $firstPlayer = Arr::first($players, function($value, $key) use ($platform) {
                return $value['portal_id'] == $platform;
            }, null);

            if ($firstPlayer == null) {
                throw new PaladinsException('The requested player could not be found in the Paladins system.');
            } else {
                $player = $firstPlayer['player_id'];
            }
        }

        return $this->makeRequest($this->buildUrl('getplayer', $player));
    }

    /**
     * Get an array of players with the requested name.
     *
     * @param string $name
     * @return mixed
     */
    public function getPlayerIdByName(string $name)
    {
        return $this->makeRequest($this->buildUrl('getplayeridbyname', $name));
    }

    /**
     * Get a player from PC or PSN. Does not work with Xbox or Switch.
     *
     * @param string $name
     * @param integer $platform
     * @return mixed
     */
    public function getPlayerIdByPortalUserId(string $name, int $platform)
    {
        return $this->makeRequest($this->buildUrl('getplayeridbyportaluserid', $name, null, null, null, null, null, null, $platform));
    }

    /**
     * Get player ids by the gamertag.
     *
     * @param string $name
     * @param integer $platform
     * @return mixed
     */
    public function getPlayerIdsByGamertag(string $name, int $platform)
    {
        return $this->makeRequest($this->buildUrl('getplayeridsbygamertag', $name, null, null, null, null, null, null, $platform));
    }

    /**
     * Get player id info for Xbox and Switch.
     *
     * @param string $name
     * @return mixed
     */
    public function getPlayerIdInfoForXboxAndSwitch(string $name)
    {
        return $this->makeRequest($this->buildUrl('getplayeridinfoforxboxandswitch', $name));
    }

    /**
     * Get all the friends for the requested player.
     *
     * @param integer $playerId
     * @return mixed
     */
    public function getPlayerFriends(int $playerId)
    {
        return $this->makeRequest($this->buildUrl('getfriends', $playerId));
    }

    /**
     * Get all the champion ranks for the requested player.
     *
     * @param integer $playerId
     * @return mixed
     */
    public function getPlayerChampionRanks(int $playerId)
    {
        return $this->makeRequest($this->buildUrl('getchampionranks', $playerId));
    }

    /**
     * Get all the champion loadouts for the requested player.
     *
     * @param integer $playerId
     * @return mixed
     */
    public function getPlayerLoadouts(int $playerId)
    {
        return $this->makeRequest($this->buildUrl('getplayerloadouts', $playerId, $this->languageId));
    }

    /**
     * Get the current status of the player.
     *
     * @param integer $playerId
     * @return mixed
     */
    public function getPlayerStatus(int $playerId)
    {
        return $this->makeRequest($this->buildUrl('getplayerstatus', $playerId));
    }

    /**
     * Get the match history of the requested player.
     *
     * @param integer $playerId
     * @return mixed
     */
    public function getPlayerMatchHistory(int $playerId)
    {
        return $this->makeRequest($this->buildUrl('getmatchhistory', $playerId));
    }

    /**
     * Get the information for an ended match.
     *
     * @param integer $matchId
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getMatchModeDetails(int $matchId)
    {
        return $this->makeRequest($this->buildUrl('getmodedetails', $matchId));
    }

    /**
     * Get match details from an ended match.
     *
     * @param integer $matchId
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getMatchDetails(int $matchId)
    {
        return $this->makeRequest($this->buildUrl('getmatchdetails', null, null, $matchId));
    }

    /**
     * Get some basic info for a live/active match.
     *
     * @param integer $matchId
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getActiveMatchDetails(int $matchId)
    {
        return $this->makeRequest($this->buildUrl('getmatchplayerdetails', null, null, $matchId));
    }

    /**
     * Show the current usage and usage limits for the API.
     *
     * @return mixed
     */
    public function getDataUsage()
    {
        return $this->makeRequest($this->buildUrl('getdataused'));
    }

    /**
     * Get the current session id, or set it if it's not set.
     *
     * @return string
     * 
     * @codeCoverageIgnore
     */
    private function getSession()
    {
        if (isset($this->cache)) {
            $cacheId = 'paladinsdev.php-api.sessionId';
        
            if ($this->cache->contains($cacheId) || $this->cache->fetch($cacheId) == null) {
                try {
                    $response = $this->guzzleClient->get("{$this->apiUrl}/createsessionJson/{$this->devId}/{$this->getSignature('createsession')}/{$this->getTimestamp()}");
                    $body = json_decode($response->getBody(), true);

                    if ($body['ret_msg'] != 'Approved' || !isset($body['session_id'])) {
                        throw new SessionException($body['ret_msg']);
                    } else {
                        $this->cache->save($cacheId, $body['session_id'], Carbon::now()->addMinutes(12));

                        return $this->cache->fetch($cacheId);
                    }
                } catch (\Exception $e) {
                    throw new SessionException($e->getMessage());
                }
            } else {
                return $this->cache->fetch($cacheId);
            }
        } else {
            if (Carbon::now()->greaterThan($this->sessionExpiresAt) || !isset($this->sessionExpiresAt)) {
                try {
                    $response = $this->guzzleClient->get("{$this->apiUrl}/createsessionJson/{$this->devId}/{$this->getSignature('createsession')}/{$this->getTimestamp()}");
                    $body = json_decode($response->getBody(), true);

                    if ($body['ret_msg'] != 'Approved' || !isset($body['session_id'])) {
                        throw new SessionException($body['ret_msg']);
                    } else {
                        $this->sessionId = $body['session_id'];
                        $this->sessionExpiresAt = Carbon::now()->addMinutes(12);

                        return $this->sessionId;
                    }
                } catch (\Exception $e) {
                    throw new SessionException($e->getMessage());
                }
            } else {
                return $this->sessionId;
            }
        }

        // if (!Cache::has($cacheId) || Cache::get($cacheId) == null) {
        //     try {
        //         $response = $this->guzzleClient->get("{$this->apiUrl}/createsessionJson/{$this->devId}/{$this->getSignature('createsession')}/{$this->getTimestamp()}");
        //         $body = json_decode($response->getBody(), true);

        //         if ($body['ret_msg'] != 'Approved' || !isset($body['session_id'])) {
        //             throw new PaladinsException($body['ret_msg']);
        //         } else {
        //             Cache::put($cacheId, $body['session_id'], Carbon::now()->addMinutes(12));

        //             return Cache::get($cacheId);
        //         }
        //     } catch (\Exception $e) {
        //         throw new PaladinsException($e->getMessage());
        //     }
        // } else {
        //     return Cache::get($cacheId);
        // }
    }

    /**
     * Get the current timestamp in a simple format for API calls.
     *
     * @return string
     * 
     * @codeCoverageIgnore
     */
    private function getTimestamp()
    {
        return Carbon::now('UTC')->format('YmdHis');
    }

    /**
     * Get the authorization signature for the API calls.
     *
     * @param string $method
     * @return string
     * 
     * @codeCoverageIgnore
     */
    private function getSignature(string $method)
    {
        return md5($this->devId . $method . $this->authKey . $this->getTimestamp());
    }

    /**
     * Build the proper URL for a variety of methods.
     *
     * @param string $method
     * @param mixed $player
     * @param integer $lang
     * @param integer $match_id
     * @param integer $champ_id
     * @param integer $queue
     * @param integer $tier
     * @param integer $season
     * @param integer $platform
     * @return string
     * 
     * @codeCoverageIgnore
     */
    private function buildUrl(string $method = null, $player = null, int $lang = null, int $match_id = null, int $champ_id = null, int $queue = null, int $tier = null, int $season = null, int $platform = null)
    {
        $baseUrl = $this->apiUrl . '/' . $method . 'Json/' . $this->devId . '/' . $this->getSignature($method) . '/' . $this->getSession() . '/' . $this->getTimestamp();

        $platform ? ($baseUrl .= '/' . $platform) : null;
        $player ? ($baseUrl .= '/' . $player) : null;
        $champ_id ? ($baseUrl .= '/' . $champ_id) : null;
        $lang ? ($baseUrl .= '/' . $lang) : null;
        $match_id ? ($baseUrl .= '/' . $match_id) : null;
        $queue ? ($baseUrl .= '/' . $queue) : null;
        $tier ? ($baseUrl .= '/' . $tier) : null;
        $season ? ($baseUrl .= '/' . $season) : null;

        return $baseUrl;
    }

    /**
     * Makes the request to the API and error checks it as well.
     *
     * @param string $url
     * @return mixed
     * 
     * @codeCoverageIgnore
     */
    private function makeRequest(string $url)
    {
        $response = $this->guzzleClient->get($url);
        $body = json_decode($response->getBody(), true);

        if (isset($body['ret_msg'])) {
            throw new PaladinsException($body['ret_msg']);
        }

        return $body;
    }
}
