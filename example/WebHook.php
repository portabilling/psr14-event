<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 *
 * Example of a webhook code, processing two types of events:
 * - Account/BalanceChanged
 * - Customer/BalanceChanged
 *
 * To make this hook work, you need to configure PortaOne with EventSeder, using
 * Version 1 events and basic auth.
 */

namespace Porta\Psr14Event\Example;

use Porta\Psr14Event\Event;
use Porta\Psr14Event\Auth\AuthBasic;
use Porta\Psr14Event\EventListenerProvider;
use Porta\Psr14Event\Dispatcher;
use Porta\Psr14Event\EventException;
use GuzzleHttp\Psr7\ServerRequest;

// This need to eb adjusted to correct 'vendor' dir path
require __DIR__ . '/../vendor/autoload.php';

// Catch event-related exceptioin
try {

    // Crating Event from globals using Guzzle PSR-7 implementation
    // If anything wrong with request, it will throw EventException with code 400
    $event = new Event(ServerRequest::fromGlobals());

    // Perform basic auth using proper class using username 'eventUser' and
    // password 'eventPassword'
    // It returns same Event object if Ok or throw EventException with code 401
    // on auth failure. No need to reassign $event, just to show it returns.
    $event = (new AuthBasic('EventUser', 'eventPassword'))->authentificate($event);

    // Crate handler for event (see BalanceChangeHandleer.php)
    $handler = new BalanceChangeHandler();

    // create Listener Provider
    $provider = new EventListenerProvider();

    // Register the handler to ListenProvider
    // Mind the args are arrays even has only single pattern and single handler
    // Both 'Account/BalanceChanged' and 'Customer/BalanceChanged' types covered with
    // one pattern
    $provider->register(['*/BalanceChanged'], [$handler]);

    // Create Dispatcher and dispatch Event
    $dispatcher = new Dispatcher($provider);
    $event = $dispatcher->dispatch($event);

    // Return the best return code to ESPF
    http_response_code($event->getBestResult());

    //Job is done!
    //
} catch (EventException $ex) {
    // If something goes wrong, return the error code to ESPF and log error
    http_response_code($ex->getCode());
    error_log((string) $ex);
}