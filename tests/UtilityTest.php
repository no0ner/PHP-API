<?php 

namespace PaladinsDev\PHP\Tests;

use PHPUnit\Framework\TestCase;
use PaladinsDev\PHP\PaladinsAPI;

class UtilityTest extends TestCase
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

    public function testGetDataUsage()
    {
        $usage = $this->api->getDataUsage();

        $this->assertArrayHasKey('0', $usage);

        $usage = $usage[0];

        $this->assertArrayHasKey('Active_Sessions', $usage);
    }
}