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

namespace Porta\Psr14\Example;

use Porta\Psr14\Event;
use Porta\Psr14\Auth\AuthBasic;
use Porta\Psr14\EventListenerProvider;
use Porta\Psr14\Dispatcher;
use Porta\Psr14\EventException;
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
    // on auth failure, but we do not need it to return here
    (new AuthBasic('EventUser', 'eventPassword'))->authentificate($event);

    // create Listener Provider, create and register Handler
    $provider = (new EventListenerProvider())
            // Mind the args are arrays even has only single pattern and single handler
            ->register(['*/BalanceChanged'], [new BalanceChangeHandler()]);

    // Create Dispatcher and dispatch Event
    (new Dispatcher($provider))->dispatch($event);

    // Return the best return code to ESPF
    http_response_code($event->getBestResult());

    //Job is done!
    //
} catch (EventException $ex) {
    // If something goes wrong, return the error code to ESPF and log error
    http_response_code($ex->getCode());
    error_log((string) $ex);
}