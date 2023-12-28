<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Test;

use Porta\Psr14Event\Auth\AuthBasic;
use Porta\Psr14Event\Event;
use Porta\Psr14Event\EventException;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * Test class for AuthBasic
 *
 */
class AuthBasicTest extends \PHPUnit\Framework\TestCase
{

    protected function event($headers)
    {
        return new Event(new ServerRequest('POST', '/', $headers, '{"event_type":"One/Two", "variables":{}}'));
    }

    public function testSuccess()
    {
        $a = new AuthBasic('TestUser', 'TestPassword');
        $e = $this->event(['Authorization' => 'Basic VGVzdFVzZXI6VGVzdFBhc3N3b3Jk', 'Date' => 'Some Date String']);
        $this->assertEquals($e, $a->authentificate($e));
    }

    public function testFailure()
    {
        $a = new AuthBasic('TestUser1', 'TestPassword');
        $e = $this->event(['Authorization' => 'Basic VGVzdFVzZXI6VGVzdFBhc3N3b3Jk', 'Date' => 'Some Date String']);
        $this->expectException(EventException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage("Basic auth failed");
        $this->assertEquals($e, $a->authentificate($e));
    }
}
