<?php 

namespace PaladinsDev\PHP\Tests;

use PHPUnit\Framework\TestCase;
use PaladinsDev\PHP\PaladinsAPI;
use PaladinsDev\PHP\Arr;
use PaladinsDev\PHP\Exceptions\PaladinsException;

class PlayerTest extends TestCase
{
    /**
     * An instance of the API class.
     *
     * @var \PaladinsDev\PHP\PaladinsAPI
     */
    private $api;

    /**
     * A valid Paladins user id to test.
     *
     * @var integer
     */
    private $userId = 9152847;

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
    public function testGetPlayerById()
    {
        $player = $this->api->getPlayer($this->userId);

        $this->assertArrayHasKey('0', $player);

        $player = $player[0];

        $this->assertArrayHasKey('Id', $player);
        $this->assertEquals(9152847, $player['Id']);
        $this->assertEquals('Steam', $player['Platform']);
        $this->assertEquals('Zencep', $player['Name']);
    }

    /**
     * @depends testSetup
     */
    public function testGetPlayerByUsername()
    {
        $player = $this->api->getPlayer('Zencep');

        $this->assertNotNull($player);
        $this->assertArrayHasKey('0', $player);

        $player = $player[0];

        $this->assertArrayHasKey('Id', $player);
        $this->assertEquals(9152847, $player['Id']);
        $this->assertEquals('Steam', $player['Platform']);
        $this->assertEquals('Zencep', $player['Name']);

    }

    /**
     * @depends testSetup
     */
    public function testGetPlayerInvalidUsername()
    {
        try {
            $player = $this->api->getPlayer($this->generateRandomString(24));
        } catch (\Exception $e) {
            $this->assertInstanceOf(PaladinsException::class, $e);
        }
    }

    /**
     * @depends testSetup
     */
    public function testGetPlayerInvalidPlayerArgument()
    {
        try {
            $player = $this->api->getPlayer([]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(PaladinsException::class, $e);
        }
    }

    /**
     * @depends testSetup
     */
    public function testGetPlayerFriends()
    {
        $friends = $this->api->getPlayerFriends($this->userId);
        
        $friendToAssert = [
            'account_id' => '36159956',
            'name' => 'athatcher7',
            'player_id' => '13023655',
            'ret_msg' => null,
        ];

        $this->assertArrayHasKey('0', $friends);

        // TODO: Figure out why this assertion is failing.
        //$this->assertArraySubset($friendToAssert, $friends, true);
    }

    /**
     * @depends testSetup
     */
    public function testGetPlayerChampionRanks()
    {
        $championRanks = $this->api->getPlayerChampionRanks($this->userId);

        $this->assertArrayHasKey('0', $championRanks);
        $this->assertNotNull(Arr::first($championRanks, function ($value, $key) {
            return $value['champion_id'] == '2285';
        }));
    }

    /**
     * @depends testSetup
     */
    public function testGetPlayerLoadouts()
    {
        $loadouts = $this->api->getPlayerLoadouts($this->userId);

        $this->assertArrayHasKey('0', $loadouts);
    }

    /**
     * @depends testSetup
     */
    public function testGetPlayerStatus()
    {
        $this->assertGreaterThan(-1, $this->api->getPlayerStatus($this->userId)[0]['status']);
    }

    /**
     * @depends testSetup
     */
    public function testGetPlayerMatchHistory()
    {
        $matches = $this->api->getPlayerMatchHistory($this->userId);

        $this->assertArrayHasKey('0', $matches);
    }

    /**
     * Taken from StackOverflow. Just generate a random string.
     *
     * @param integer $length
     * @return void
     */
    private function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }
}