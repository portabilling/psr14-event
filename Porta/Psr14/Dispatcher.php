<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Psr14;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Simple implemention of PSR-14 EventDispatcher
 *
 * @api
 */
class Dispatcher implements EventDispatcherInterface
{

    protected ListenerProviderInterface $provider;

    /**
     * Setup the Dispatcher with one ListenreProvider
     *
     * @param ListenerProviderInterface $provider
     * @api
     */
    public function __construct(ListenerProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Performs event dispatch as per PSR-14 spec
     *
     * @param object $event Event to dispatch
     * @return object Dispatched event.
     */
    public function dispatch(object $event): object
    {
        foreach ($this->provider->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }
            $listener($event);
        }
        return $event;
    }
}
