<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Psr14\Auth;

use Porta\Psr14\EventException;

/**
 * Class to perform basic authentification
 *
 * @api
 */
class AuthBasic extends Auth
{

    protected const AUTH_BASIC = 'Basic';

    protected string $login;
    protected string $password;

    /**
     * Sets login and password for basic authentification
     *
     * @param string $login
     * @param string $password
     * @api
     */
    public function __construct(string $login, string $password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    protected function check(): void
    {
        if (($this->authType != self::AUTH_BASIC) ||
                ($this->authValue != base64_encode($this->login . ':' . $this->password))) {
            throw new EventException("Basic auth failed", 401);
        }
    }
}
