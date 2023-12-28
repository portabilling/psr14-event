<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Test;

use Porta\Psr14Event\Event;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * Description of EventHandlerBaseTest
 *
 */
class EventHandlerBaseTest extends \PHPUnit\Framework\TestCase
{

    protected function event(string $eventType): Event
    {
        return new Event(new ServerRequest('POST', '/', ['Authorization' => 'Basic VGVzdFVzZXI6VGVzdFBhc3N3b3Jk', 'Date' => 'Some Date String'], '{"event_type":"' . $eventType . '", "variables":{}}'));
    }

    public function testFound()
    {
        $h = new EventHandlerBaseWrapper();
        $e = $this->event('Routed/Known');
        $h($e);
        $this->assertEquals(444, $e->getBestResult());
    }

    public function testNotFound()
    {
        $h = new EventHandlerBaseWrapper();
        $e = $this->event('Routed/Unknown');
        $h($e);
        $this->assertEquals(404, $e->getBestResult());
    }

    public function testCustoCode()
    {
        $h = new EventHandlerBaseWrapper();
        $this->assertEquals($h, $h->withNotFoundCode(555));
        $e = $this->event('Routed/Unknown');
        $h($e);
        $this->assertEquals(555, $e->getBestResult());
    }
}
