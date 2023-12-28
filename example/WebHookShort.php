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

namespace Porta\Psr14Event\Example;

use Porta\Psr14Event\Event;
use Porta\Psr14Event\Auth\AuthBasic;
use Porta\Psr14Event\EventListenerProvider;
use Porta\Psr14Event\Dispatcher;
use Porta\Psr14Event\EventException;
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