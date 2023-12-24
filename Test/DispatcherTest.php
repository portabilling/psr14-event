<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Test;

use Psr\EventDispatcher\ListenerProviderInterface;
use Porta\Psr14\Dispatcher;
use Porta\Psr14\Event;
use Porta\Psr14\EventHandlerBase;

/**
 * Tests class for  Dispatcher
 *
 */
class DispatcherTest extends \PHPUnit\Framework\TestCase
{

    public function callableNormal(Event $e)
    {
        $e->onSuccess();
    }

    public function callableLast(Event $e)
    {
        $e->onSuccess();
        $e->stopOnFirstGood();
    }

    public function callableNever(Event $e)
    {
        $this->fail("This should not be called");
    }

    public function testNormal()
    {
        $event = $this->createMock(Event::class);
        $event->expects($this->exactly(3))
                ->method('onSuccess');
        $event->expects($this->exactly(3))
                ->method('isPropagationStopped')
                ->willReturn(false);
        $provider = $this->getMockForAbstractClass(ListenerProviderInterface::class);
        $provider->expects($this->once())
                ->method('getListenersForEvent')
                ->with($this->equalTo($event))
                ->willReturn([[$this, 'callableNormal'], [$this, 'callableNormal'], [$this, 'callableNormal']]);
        $dispather = new Dispatcher($provider);
        $this->assertEquals($event, $dispather->dispatch($event));
    }

    public function testStoppable()
    {
        $event = $this->createMock(Event::class);
        $event->expects($this->exactly(2))
                ->method('onSuccess');
        $event->expects($this->exactly(3))
                ->method('isPropagationStopped')
                ->willReturnOnConsecutiveCalls(false, false, true);
        $event->expects($this->exactly(1))
                ->method('stopOnFirstGood');
        $provider = $this->getMockForAbstractClass(ListenerProviderInterface::class);
        $provider->expects($this->once())
                ->method('getListenersForEvent')
                ->with($this->equalTo($event))
                ->willReturn([[$this, 'callableNormal'], [$this, 'callableLast'], [$this, 'callableNever']]);
        $dispather = new Dispatcher($provider);
        $this->assertEquals($event, $dispather->dispatch($event));
    }

    public function testEmpty()
    {
        $event = $this->createMock(Event::class);
        $event->expects($this->exactly(0))
                ->method('onSuccess');
        $provider = $this->getMockForAbstractClass(ListenerProviderInterface::class);
        $provider->expects($this->once())
                ->method('getListenersForEvent')
                ->with($this->equalTo($event))
                ->willReturn([]);
        $dispather = new Dispatcher($provider);
        $this->assertEquals($event, $dispather->dispatch($event));
    }
}
