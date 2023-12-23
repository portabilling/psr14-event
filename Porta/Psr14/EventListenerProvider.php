<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Psr14;

use Psr\EventDispatcher\ListenerProviderInterface;
use Porta\Psr14\Event;

/**
 * Porta-Event specific ListenerProvider with extra features for Porta events
 *
 * Register event type patterns with register() methos and suppy the instance to
 * PSR-14 dispatcher.
 *
 * @api
 */
class EventListenerProvider implements ListenerProviderInterface
{

    const PATTERNS = 'patterns';
    const HANDLERS = 'handlers';

    protected array $handlers = [];

    /**
     * Registre set of patterns to match and set of handlers to call if any pattern match
     *
     * While it is possible to setup multiple patterns and multiple handlers in
     * one record, please be ecouraged to use 'one pattern to multiple handlers'
     * or 'multiple patterns to one handler' approach.
     *
     * If your configuration will allow to call a handler meltiple times because
     * it listed in two and more register() calls, and patterns will math - the
     * same handler will be called multiple times.
     *
     * @param string[] $patterns Array of patterns to match agains event type.
     * If any of array match the event type - all the handlers will run in order
     * of given
     * @param mixed[] $handlers Array of handlers to run. Basically, it should ne array
     * of callable, any item which is not callable will try to resolve to callable by
     * resolveHandler(), which do nothing for this base class. Override it if you wish
     * to resolve your definition into callable.
     * @return self for chaining
     * @api
     */
    public function register(array $patterns, array $handlers): self
    {
        $this->handlers[] = [
            self::PATTERNS => $patterns,
            self::HANDLERS => $handlers,
        ];
        return $this;
    }

    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof Event) {
            foreach ($this->handlers as $handler) {
                if ($event->isMatchPatterns($handler[self::PATTERNS])) {
                    yield from $this->yieldHandlers($handler[self::HANDLERS]);
                }
            }
        }
    }

    protected function yieldHandlers(array $handlers): iterable
    {
        foreach ($handlers as $handler) {
            if (is_callable($handler)) {
                yield $handler;
            } else {
                yield from $this->resolveHandler($handler);
            }
        }
    }

    /**
     * Method to override for add handler definitions other, that just callable
     *
     * Also may be used to catch and log wrong callable problems. Override this
     * method, log $handler value and return [] for other handler work.
     *
     * @param mixed $handler Handler definition
     * @return iterable Must yield callable
     * @api
     */
    protected function resolveHandler(mixed $handler): iterable
    {
        return []; // Do nothing for basic ListenerProvider;
    }
}
