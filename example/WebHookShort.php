<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 *
 * Example of a webhook code, same as WebHook.php, but absolutely collapsed to
 * chain/args, using no variables at all.
 *
 * It seems insane but it should work. Just for fun!
 *
 */

namespace Porta\Psr14\Example;

use Porta\Psr14\Event;
use Porta\Psr14\Auth\AuthBasic;
use Porta\Psr14\EventListenerProvider;
use Porta\Psr14\Dispatcher;
use Porta\Psr14\EventException;
use GuzzleHttp\Psr7\ServerRequest;

// This need to bу adjusted to correct 'vendor' dir path
require __DIR__ . '/../vendor/autoload.php';

// Catch event-related exceptioin
try {
    http_response_code(
            (new Dispatcher(
                            (new EventListenerProvider())
                            ->register(['*/BalanceChanged'], [new BalanceChangeHandler()])
                    ))
                    ->dispatch(
                            (new AuthBasic('EventUser', 'eventPassword'))
                            ->authentificate(
                                    new Event(ServerRequest::fromGlobals())
                            )
                    )
                    ->getBestResult()
    );
} catch (EventException $ex) {
    http_response_code($ex->getCode());
    error_log((string) $ex);
}