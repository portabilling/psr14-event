<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Psr14\Auth;

use Porta\Psr14\Event;
use Psr\Http\Message\RequestInterface;
use Porta\Psr14\EventException;

/**
 * Base class for event call authentification
 *
 * @api
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
     * Changes auth header
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
     * @inheritDoc
     */
    public function authentificate(Event $event): Event
    {
        $this->request = $event->getRequest();
        $this->parseData();
        $this->check();
        return $event;
    }

    /**
     * Function to perform auth check
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
