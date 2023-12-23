<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Psr14;

use Psr\Http\Message\ServerRequestInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * PSR-14 Event object/class
 *
 * This obect is mutable and will pass over all the handlers, assuming the
 * handlers will process event and register rocessing result with onSuccess
 * or onFailure methods.
 *
 * Event vrs may be read either as array keyed values and as properties.
 *
 * @api
 */
class Event implements StoppableEventInterface, \ArrayAccess
{

    protected const EVENT_TYPE_KEY = 'event_type';
    protected const EVENT_VARS_KEY = 'variables';

    protected string $type;
    protected array $vars;
    protected ServerRequestInterface $request;
    protected bool $stopOnFirstGood = false;
    protected array $result = [];

    /**
     * Creates event object from ServerRequestInterface object
     *
     * Parse the request, check the format, detect all event parts.
     *
     * @param ServerRequestInterface $request
     * @throws EventException with code 400 in a case it can't detect JSON body
     * and it's required fields.
     * @api
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
        $this->parseData();
    }

    /**
     * Returns event type
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
     * @return array
     * @api
     */
    public function getVars(): array
    {
        return $this->vars;
    }

    /**
     * Set event to stop propagate after first success happen
     *
     * @return self - for chaining
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
     * @api
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopOnFirstGood && in_array(200, $this->result);
    }

    /**
     * Event handler must call this to register processing success
     *
     * @return void
     * @api
     */
    public function onSuccess(): void
    {
        $this->result[] = 200;
    }

    /**
     * Event handler must call this to register processing failure
     *
     * @param int $code HTTP error code to return. Consult Portaone documentation
     * about return code meaning.
     * @return void
     * @api
     */
    public function onFailure(int $code): void
    {
        $this->result[] = $code > 400 ? $code : 400;
    }

    /**
     * Returns origonal request object
     *
     * @return ServerRequestInterface
     * @api
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Returns the worst (highest code) of registered results
     *
     * If there was no handlers return gived notfound value or default 404
     *
     * @param int $notFoundCode
     * @return int
     * @api
     */
    public function getBestResult(int $notFoundCode = 404): int
    {
        return [] == $this->result ? $notFoundCode : min($this->result);
    }

    /**
     * Returns the best (lowest code) of registered results
     *
     * If there was no handlers return gived notfound value or default 404
     *
     * @param int $notFoundCode
     * @return int
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
     * - `'*\/BalanceChanged'` will match `'Customer/BalanceChanged'` and `'Account/BalanceChanged'`
     * - `'Account/*locked'` will match `'Account/Blocked'` and `'Account/Unblocked'`
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

    public function offsetGet(mixed $offset): mixed
    {
        return $this->vars[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Do nothing, read only
    }

    public function offsetUnset(mixed $offset): void
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
