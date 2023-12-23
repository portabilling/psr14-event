<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Psr14\Auth;

use Porta\Psr14\EventException;

/**
 * Class to perform custom authentification
 *
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

    protected function check(): void
    {
        if (($this->authType != $this->customeType) ||
                ($this->authValue != $this->customValue)) {
            throw new EventException("Custom auth failed", 401);
        }
    }
}
