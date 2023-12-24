<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 *
 * Example of Event handler
 */

namespace Porta\Psr14\Example;

use Porta\Psr14\Event;
use Porta\Psr14\EventHandlerBase;

/**
 * Example to handle two event types
 *
 * Handle two event types:
 * - Account/BalanceChanged
 * - Customer/BalanceChanged
 *
 * Do nothing, but writing balance changes into php main log
 *
 */
class BalanceChangeHandler extends EventHandlerBase
{

    protected function eventAccountBalanceChanged(Event $event): void
    {
        // Use event variables as array members
        error_log("Account # {$event['billing_entity_id']} balance changed "
                . "from {$event['prev_balance']} to {$event['curr_balance']}");

        // Register processing was successfull
        $event->onSuccess();
    }

    protected function eventCustomerBalanceChanged(Event $event): void
    {
        // Use event variables as class properties
        error_log("Customer # $event->billing_entity_id balance changed "
                . "from $event->prev_balance to $event->curr_balance");

        // Register processing was successfull
        $event->onSuccess();
    }
}
