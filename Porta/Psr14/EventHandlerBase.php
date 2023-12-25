<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Psr14;

/**
 * Base class for event handler with routing of event type by methods
 *
 * To use, extend with your class and add methods which match desired event types.
 * Method name laterally comes from event name by add prefix 'event' and remove
 * slashes.
 * Example: event type Subscriber/Created will use method to handle:
 * ```
 * protected function eventSubscriberCreated(Event $event):void
 * ```
 * Function *must* call $event->onSuccess() or $event->onFailure() to report the
 * result of event processing
 *
 * See [usage example](https://github.com/portabilling/psr14-events/tree/master/example)
 *
 * @example ../../example/BalanceChangeHandler.php Usage exampe
 * @api
 */
class EventHandlerBase
{

    protected $notFoundCode = 404;

    /**
     * Set the return code for no method for an event.
     *
     * Default is 404 (not found). ESPF will log the error and remove event from
     * queue
     * Set code to 200 if it is Ok not to handle udefined events silently for ESPF.
     *
     * @param int $code code to return for undefined events
     * @return self for chaining
     * @api
     */
    public function withNotFoundCode(int $code): self
    {
        $this->notFoundCode = $code;
        return $this;
    }

    /**
     * Accept call and dispatch it to other method based on event type
     *
     * Handling method name laterally comes from event type string by add
     * prefix 'event' and remove slashes.
     * Example: event type Subscriber/Created will use method to handle:
     * ```
     * protected function eventSubscriberCreated(Event $event):void
     * ```
     * @param Event $event
     * @return void
     */
    public function __invoke(Event $event)
    {
        $handlerName = 'event' . str_replace('/', '', $event->getType());
        $this->$handlerName($event);
    }

    /**
     * Catches all calls to undefined methods
     *
     * If __invoke() dispatch an event to undefined method, it will catch it,
     * check that the first argument has Event type and, if so, register
     * 'not found' code to the Event instance.
     *
     * The code to use as 'not found' is 404 by default, but it could be change
     * by withNotFoundCode() method. If you set it to 200, all undefined event
     * types will return Ok to ESPF as it were normally processed.
     *
     */
    public function __call($name, $arguments)
    {
        if (isset($arguments[0]) && ($arguments[0] instanceof Event)) {
            $arguments[0]->onFailure($this->notFoundCode);
        }
    }
}
