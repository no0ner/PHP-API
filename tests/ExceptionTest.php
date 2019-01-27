<?php 

namespace PaladinsDev\PHP\Tests;

use PHPUnit\Framework\TestCase;
use PaladinsDev\PHP\PaladinsAPI;
use PaladinsDev\PHP\Exceptions\SessionException;

class ExceptionTest extends TestCase
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
            $this->api = PaladinsAPI::getInstance('notvalid', 'notvalid');
        }
    }

    public function testSetup()
    {
        $this->assertInstanceOf(PaladinsAPI::class, $this->api);
    }

    /**
     * @depends testSetup
     */
    public function testSessionException()
    {
        try {
            $this->api->getDataUsage();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SessionException::class, $e);
        }
    }
}