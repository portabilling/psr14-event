<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Psr14Event\Auth;

use Porta\Psr14Event\EventException;

/**
 * Class to perform custom authentification
 *
 * Create an instance of class with password and username and then check Event
 * for credentials:
 * ```
 * (new AuthBasic('customType','customValue'))->authentificate($event)
 * ```
 *
 * @api
 * @package Auth
 */
class AuthCustom extends Auth
{

    protected string $customeType;
    protected string $customValue;

    /**
     * Sets custom auth type and value
     *
     * @param string $customeType
     * @param string $customValue
     * @api
     */
    public function __construct(string $customeType, string $customValue)
    {
        $this->customeType = $customeType;
        $this->customValue = $customValue;
    }

    /**
     * @internal 
     * @return void
     * @throws EventException
     */
    protected function check(): void
    {
        if (($this->authType != $this->customeType) ||
                ($this->authValue != $this->customValue)) {
            throw new EventException("Custom auth failed", 401);
        }
    }
}
