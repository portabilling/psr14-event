<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Test;

use Porta\Psr14Event\EventHandlerBase;
use Porta\Psr14Event\Event;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * Wrapper for EventHandlerBase testing
 *
 */
class EventHandlerBaseWrapper extends EventHandlerBase
{

    protected function eventRoutedKnown(Event $event): void
    {
        $event->onFailure(444);
    }
}
