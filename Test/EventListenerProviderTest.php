<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Test;

use Porta\Psr14Event\Event;
use Porta\Psr14Event\EventListenerProvider;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * Description of EventListenerProviderTest
 *
 */
class EventListenerProviderTest extends \PHPUnit\Framework\TestCase
{

    protected function event(string $eventType)
    {
        return new Event(new ServerRequest('POST', '/', ['Authorization' => 'Basic VGVzdFVzZXI6VGVzdFBhc3N3b3Jk', 'Date' => 'Some Date String'], '{"event_type":"' . $eventType . '", "variables":{}}'));
    }

    public function callableMethod()
    {

    }

    public static function staticCallable()
    {

    }

    public function testFound()
    {
        $funcA = function (Event $e) {

        };

        $funcB = 'printf';

        $funcC = [$this, 'callableMethod'];

        $funcD = self::class . '::staticCallable';

        $p = new EventListenerProvider();
        $p->register(['LevelOneA/LevelTwoA'], [$funcA, 'NonCallable']);
        $p->register(['LevelOneB/*', 'LevelOneC/LevelTwoA'], [$funcB, $funcC]);
        $p->register(['LevelOneB/LevelTwoC'], [$funcC, $funcD]);

        $this->assertEquals([$funcA], iterator_to_array($p->getListenersForEvent($this->event('LevelOneA/LevelTwoA')), false));
        $this->assertEquals([], iterator_to_array($p->getListenersForEvent($this->event('LevelOneD/LevelTwoA')), false));
        $this->assertEquals([$funcB, $funcC, $funcC, $funcD], iterator_to_array($p->getListenersForEvent($this->event('LevelOneB/LevelTwoC')), false));

        //iterator_to_array()
    }
}
