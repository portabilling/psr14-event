<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Psr14Event;

use Psr\Http\Message\RequestInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * PSR-14 Event object/class
 *
 * This obect is mutable and will pass over all handlers, assuming
 * handlers will process event and register зrocessing result with onSuccess()
 * or onProcessed() methods.
 *
 * Event vars are read-only and may be accessed either as array keyed values
 * and as properties.
 *
 * @api
 */
class Event implements StoppableEventInterface, \ArrayAccess
{

    protected const EVENT_TYPE_KEY = 'event_type';
    protected const EVENT_VARS_KEY = 'variables';

    protected string $type;
    protected array $vars;
    protected RequestInterface $request;
    protected bool $stopOnFirstGood = false;
    protected array $result = [];

    /**
     * Creates event object from RequestInterface object
     *
     * Parse the request, check the format, detect all event parts.
     *
     * @param RequestInterface $request
     * @return void
     * @throws EventException with code 400 in a case it can't detect JSON body
     * or required fields.
     *
     * @api
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
        $this->parseData();
    }

    /**
     * Returns event type
     *
     * Returns ESPF event type like 'Account/BalanceChanged'
     *
     * @return string
     * @api
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Return all event vars as associative array
     *
     * @return array<string, mixed>
     * @api
     */
    public function getVars(): array
    {
        return $this->vars;
    }

    /**
     * Set event to stop propagate after first success (200) happen
     *
     * If methos is called once, the Event is set to stop propagate after first
     * succes code (200) registered by a handler.
     *
     * May be called either before dispatch or by handler. In the second case,
     * the hahdler will be the last one and Event will stop it's propagation.
     *
     * @return self For chaining
     * @api
     */
    public function stopOnFirstGood(): self
    {
        $this->stopOnFirstGood = true;
        return $this;
    }

    /**
     * Indicate to stop future processing by Dispatcher
     *
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopOnFirstGood && in_array(200, $this->result);
    }

    /**
     * Event handler must call this to register processing success
     *
     * The same meaning like to call onProcessed(200), as 200 is the success code
     *
     * @return void
     * @api
     */
    public function onSuccess(): void
    {
        $this->result[] = 200;
    }

    /**
     * Event handler must call this to register processing result
     *
     * The code given will be passed to PortaOne as http result code
     *
     * See [Portaone documentation](https://docs.portaone.com/docs/mr105-receiving-provisional-events)
     * about return code meaning and ESPF action on it:
     *
     * Once a response is received, the ESPF acts as follows:
     * - 200 OK – the event has been received. The ESPF removes the event from
     * the provisioning queue.
     * - 4xx Client Error (e.g., 400 Bad Request) – the event must not be
     * provisioned. The ESPF removes the event from the provisioning queue.
     * - Other status code – an issue appeared during provisioning. The ESPF
     * re-sends the event.
     * - If no response is received from the Application during the timeout (300
     * seconds by default) – the ESPF re-sends the event to the Application.
     * Please make sure your application can accept the same provisioning event
     * multiple times.
     *
     * @param int $code HTTP error code to return.
     * @return void
     * @api
     */
    public function onProcessed(int $code): void
    {
        $this->result[] = $code;
    }

    /**
     * Returns original request object
     *
     * @return RequestInterface
     * @api
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Returns the best (lowest code) of registered results
     *
     * If there was no handlers return gived notfound code or default 404
     *
     * @param int $notFoundCode
     * @return int The best (lowest) code of all registered
     * @api
     */
    public function getBestResult(int $notFoundCode = 404): int
    {
        return [] == $this->result ? $notFoundCode : min($this->result);
    }

    /**
     * Returns the worst (highest code) of registered results
     *
     * If there was no handlers return gived notfound value or default 404
     *
     * @param int $notFoundCode
     * @return int The worst (highest)code of all registered
     * @api
     */
    public function getWorstResult(int $notFoundCode = 404): int
    {
        return [] == $this->result ? $notFoundCode : max($this->result);
    }

    /**
     * Checks event type against array of patterns
     *
     * Each pattern in the array may be a valid event type with wildcard `*` means
     * any count of **letters, but not `/`**.
     *
     * Examples:
     * - '&#x2a;/BalanceChanged' will match 'Customer/BalanceChanged' and 'Account/BalanceChanged'
     * - 'Account/*locked' will match 'Account/Blocked' and 'Account/Unblocked'
     *
     * @param string[] $patterns Array of patterns to match
     * @return bool return true if match any of patterns
     * @api
     */
    public function isMatchPatterns(array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $regexp = str_replace('*', '[a-zA-Z]+', $pattern);
            if (1 === preg_match('|^' . $regexp . '$|', $this->type)) {
                return true;
            }
        }
        return false;
    }

    // Protected methods
    protected function parseData(): void
    {
        if (null === ($requestData = json_decode((string) $this->request->getBody(), true))) {
            throw new EventException("Can't decode request body as JSON, body: '" . (string) $this->request->getBody() . "'", 400);
        }
        if (!key_exists(self::EVENT_TYPE_KEY, $requestData) ||
                !key_exists(self::EVENT_VARS_KEY, $requestData)) {
            throw new EventException("Mailformed JSON data, 'event_type' or 'variables' does not exist", 400);
        }
        $this->type = $requestData[self::EVENT_TYPE_KEY];
        $this->vars = $requestData[self::EVENT_VARS_KEY];
    }

    // Access vars as array elements
    public function offsetExists($offset): bool
    {
        return isset($this->vars[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->vars[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        // Do nothing, read only
    }

    public function offsetUnset($offset): void
    {
        // Do nothing, read only
    }

    // Access vars as properties
    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    public function __set($name, $value)
    {
        // Do nothing, read only
    }

    public function __unset($name)
    {
        // Do nothing, read only
    }
}
