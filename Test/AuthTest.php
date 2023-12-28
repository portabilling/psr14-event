<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Test;

use Porta\Psr14Event\Event;
use Porta\Psr14Event\EventException;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * Test clas for of Auth
 *
 */
class AuthTest extends \PHPUnit\Framework\TestCase
{

    protected function event($headers)
    {
        return new Event(new ServerRequest('POST', '/', $headers, '{"event_type":"One/Two", "variables":{}}'));
    }

    public function testEmpty()
    {
        $this->expectException(EventException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage("Missed or wrong 'Authorization' header in the request");
        (new AuthWrap())->authentificate($this->event([]));
    }

    public function testBadAuthorization()
    {
        $this->expectException(EventException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage("Corrupted content of 'Authorization' header in the request");
        (new AuthWrap())->authentificate($this->event(['Authorization' => 'SingleLine']));
    }

    public function testNoDate()
    {
        $this->expectException(EventException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage("Missed or wrong 'Date' header in the request");
        (new AuthWrap())->authentificate($this->event(['Authorization' => 'Basic credentials']));
    }

    public function testSuccess()
    {
        $e = $this->event(['Authorization' => 'Basic credentials', 'Date' => 'some date string']);
        $this->assertEquals($e, (new AuthWrap())->authentificate($e));
    }

    public function testCustomHeader()
    {
        $a = new AuthWrap();
        $this->assertEquals($a, $a->withAuthHeader('UserTest'));
        $e = $this->event(['UserTest' => 'Basic credentials', 'Date' => 'some date string']);
        $this->assertEquals($e, $a->authentificate($e));
    }
}
