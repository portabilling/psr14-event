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
 */
interface AuthInterface
{

    /**
     * Perform authentification of the event
     *
     * @throws EventException with code 401 in a case of failure
     * @api
     */
    public function authentificate(Event $event): Event;
}
