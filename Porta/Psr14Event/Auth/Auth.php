<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Psr14Event\Auth;

use Porta\Psr14Event\Event;
use Psr\Http\Message\RequestInterface;
use Porta\Psr14Event\EventException;

/**
 * Abstract sase class for event call authentification
 *
 * @api
 * @package Auth
 */
abstract class Auth implements AuthInterface
{

    const DATE_HEADER = 'Date';

    protected $authHeader = 'Authorization';
    protected RequestInterface $request;
    protected string $authType;
    protected string $authValue;
    protected string $dateHeader;

    /**
     * Set class to use custom auth header
     *
     * PortaBilling support use of custom header instead of 'Aithorization:'.
     * If you use custom header, use this method. You may chain it as
     * `(new AuthBasic($user,$pass))->withAuthHeader('Verify');`
     *
     * @param string $header - alternate auth header
     * @return self for chaining
     * @api
     */
    public function withAuthHeader(string $header): self
    {
        $this->authHeader = $header;
        return $this;
    }

    /**
     * Perform authentification
     *
     * The method takes Event, retrieves auth data and perform authentification.
     * - In a case of success it returns the Event itself.
     * - In a case of failure it will throw EventException with code 401
     *
     * @param Event $event
     * @return Event For chaining the methid with other methods
     * @throws EventException with code 401 in a case of failure
     * @api
     */
    public function authentificate(Event $event): Event
    {
        $this->request = $event->getRequest();
        $this->parseData();
        $this->check();
        return $event;
    }

    /**
     * Abstract method to perform auth check
     *
     * Override this to implement exact auth method
     *
     * @throws EventException with code 401 in a case of authfailure
     * @api
     */
    abstract protected function check(): void;

    protected function parseData(): void
    {
        $parts = explode(' ', $this->extractHeader($this->authHeader));
        if (count($parts) != 2) {
            throw new EventException("Corrupted content of '" . $this->authHeader . "' header in the request", 401);
        }
        $this->authType = $parts[0];
        $this->authValue = $parts[1];
        $this->dateHeader = $this->extractHeader(self::DATE_HEADER);
    }

    protected function extractHeader(string $headerName): string
    {
        $header = $this->request->getHeader($headerName);
        if (count($header) != 1) {
            throw new EventException("Missed or wrong '" . $headerName . "' header in the request", 401);
        }
        return $header[0];
    }
}
