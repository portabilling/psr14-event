<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Psr14;

/**
 * Exception to throw in a case of Event can't be created or Auth failure
 *
 * It is recommended that exception code be returned to calling ESPF
 * @api
 */
class EventException extends \Exception
{

}
