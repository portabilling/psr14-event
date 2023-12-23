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
 * shashes.
 * Example: event type Subscriber/Created will need methot to handle:
 * ```
 * protected function eventSubscriberCreated(Event $event):void
 * ```
 * Function *must* call $event->onSuccess() or $event->onFailure() to report the
 * result of event processing
 *
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

    public function __invoke(Event $event)
    {
        $handlerName = 'event' . str_replace('/', '', $event->getType());
        $this->$handlerName($event);
    }

    public function __call($name, $arguments)
    {
        if (isset($arguments[0]) && ($arguments[0] instanceof Event)) {
            $arguments[0]->onFailure($this->notFoundCode);
        }
    }
}
