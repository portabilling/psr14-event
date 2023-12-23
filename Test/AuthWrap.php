<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Test;

use Porta\Psr14\Auth\Auth;

/**
 * test wrapper for abstract class Auth
 *
 */
class AuthWrap extends Auth
{

    protected function check(): void
    {
        
    }
}
