<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Test;

use Porta\Psr14Event\Auth\AuthCustom;
use Porta\Psr14Event\Event;
use Porta\Psr14Event\EventException;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * Test class for AuthBasic
 *
 */
class AuthCustomTest extends \PHPUnit\Framework\TestCase
{

    protected function event($headers)
    {
        return new Event(new ServerRequest('POST', '/', $headers, '{"event_type":"One/Two", "variables":{}}'));
    }

    public function testSuccess()
    {
        $a = new AuthCustom('AuthTypeString', 'AuthDataString');
        $e = $this->event(['Authorization' => 'AuthTypeString AuthDataString', 'Date' => 'Some Date String']);
        $this->assertEquals($e, $a->authentificate($e));
    }

    public function testFailure()
    {
        $a = new AuthCustom('AuthTypeString', 'AuthDataString');
        $e = $this->event(['Authorization' => 'AuthTypeString AuthDataString2', 'Date' => 'Some Date String']);
        $this->expectException(EventException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage("Custom auth failed");
        $this->assertEquals($e, $a->authentificate($e));
    }
}
