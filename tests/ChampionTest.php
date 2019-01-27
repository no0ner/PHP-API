<?php 

namespace PaladinsDev\PHP\Tests;

use PHPUnit\Framework\TestCase;
use PaladinsDev\PHP\PaladinsAPI;

class ChampionTest extends TestCase
{
    /**
     * An instance of the API class.
     *
     * @var \PaladinsDev\PHP\PaladinsAPI
     */
    private $api;

    protected function setup()
    {
        if (!isset($this->api)) {
            $this->api = PaladinsAPI::getInstance(getenv('PALADINS_API_DEV_ID'), getenv('PALADINS_API_AUTH_KEY'));
        }
    }

    public function testSetup()
    {
        $this->assertInstanceOf(PaladinsAPI::class, $this->api);
    }

    /**
     * @depends testSetup
     */
    public function testGetChampions()
    {
        $champions = $this->api->getChampions();

        $this->assertArrayHasKey('0', $champions);
    }

    /**
     * @depends testSetup
     */
    public function testGetChampionCards()
    {
        $cards = $this->api->getChampionCards(2205);

        $this->assertArrayHasKey('0', $cards);
    }

    /**
     * @depends testSetup
     */
    public function testGetChampionSkins()
    {
        $skins = $this->api->getChampionSkins(2205);

        $this->assertNotNull($skins);
    }
}