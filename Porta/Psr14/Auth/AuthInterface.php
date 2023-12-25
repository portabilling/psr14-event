<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Psr14\Auth;

use Porta\Psr14\Event;
use Porta\Psr14\EventException;

/**
 * Interface for ESPF call authentificator
 *
 * @api
 * @package Auth
 */
interface AuthInterface
{

    /**
     * Perform authentification
     *
     * The method takes Event, retrieves auth data and perform authentification.
     * - In a case of success it returns the Event itself.
     * - In a case of failure it will throw EventException with code 401
     *
     * @param Event $event
     * @return Event
     * @throws EventException with code 401 in a case of failure
     * @api
     */
    public function authentificate(Event $event): Event;
}
