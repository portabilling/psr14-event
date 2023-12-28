<?php

/*
 * Library to use PortBilling events with PSR-14 event dispatch
 */

namespace Porta\Test;

use Porta\Psr14Event\Event;
use GuzzleHttp\Psr7\ServerRequest;
use Porta\Psr14Event\EventException;

/**
 * Test class for Event
 *
 */
class EventTest extends \PHPUnit\Framework\TestCase
{

    const JSON = <<<EOT
{

    "event_type": "Subscriber/Created",

    "variables": {

         "i_account": 1000889,

         "i_event":  7615

    }

}
EOT;

    protected function request($headers, $body)
    {
        return new ServerRequest('POST', '/', $headers, $body);
    }

    public function testEvent()
    {
        $r = $this->request(['Authorizetion', 'Basic XXXXXX'], self::JSON);
        $e = new Event($r);
        $this->assertEquals($r, $e->getRequest());

        $this->assertTrue(isset($e['i_account']));
        $this->assertEquals(1000889, $e['i_account']);
        $e['i_account'] = 0;
        $this->assertEquals(1000889, $e['i_account']);
        unset($e['i_account']);
        $this->assertEquals(1000889, $e['i_account']);

        $this->assertTrue(isset($e->i_event));
        $this->assertEquals(7615, $e->i_event);
        $e->i_event = 0;
        $this->assertEquals(7615, $e->i_event);
        unset($e->i_event);
        $this->assertEquals(7615, $e->i_event);

        $this->assertEquals('Subscriber/Created', $e->getType());
        $this->assertEquals(["i_account" => 1000889, "i_event" => 7615], $e->getVars());

        $this->assertFalse($e->isPropagationStopped());
        $this->assertEquals(404, $e->getBestResult());
        $this->assertEquals(404, $e->getWorstResult());
        $this->assertEquals(601, $e->getBestResult(601));
        $this->assertEquals(601, $e->getWorstResult(601));

        $this->assertEquals($e, $e->stopOnFirstGood());
        $this->assertFalse($e->isPropagationStopped());
        $e->onFailure(501);
        $this->assertFalse($e->isPropagationStopped());
        $this->assertEquals(501, $e->getBestResult());
        $this->assertEquals(501, $e->getWorstResult());
        $this->assertEquals(501, $e->getBestResult(601));
        $this->assertEquals(501, $e->getWorstResult(601));
        $this->assertFalse($e->isPropagationStopped());

        $e->onSuccess();
        $this->assertTrue($e->isPropagationStopped());
        $this->assertEquals(200, $e->getBestResult());
        $this->assertEquals(501, $e->getWorstResult());
        $this->assertEquals(200, $e->getBestResult(601));
        $this->assertEquals(501, $e->getWorstResult(601));

        $e = new Event($r);
        $e->onFailure(404);
        $e->onFailure(501);
        $e->onFailure(401);
        $e->onSuccess();
        $e->onFailure(444);
        $this->assertFalse($e->isPropagationStopped());
        $this->assertEquals(200, $e->getBestResult());
        $this->assertEquals(501, $e->getWorstResult());
        $this->assertEquals(200, $e->getBestResult(601));
        $this->assertEquals(501, $e->getWorstResult(601));
        $e->stopOnFirstGood();
        $this->assertTrue($e->isPropagationStopped());
    }

    public function testMatch()
    {
        // Subscriber/Created
        $e = new Event($this->request([], self::JSON));
        $this->assertTrue($e->isMatchPatterns(['Subscriber/Created']));
        $this->assertFalse($e->isMatchPatterns(['Subscriber/created']));
        $this->assertTrue($e->isMatchPatterns(['Subscriber/*']));
        $this->assertTrue($e->isMatchPatterns(['*/Created']));
        $this->assertTrue($e->isMatchPatterns(['*/*']));
        $this->assertFalse($e->isMatchPatterns(['*/*/*']));

        $this->assertTrue($e->isMatchPatterns(['Subscriber/created', 'Subscriber/Created']));
        $this->assertTrue($e->isMatchPatterns(['Subscriber/*', 'Subscriber/created']));
        $this->assertFalse($e->isMatchPatterns(['*/*/*', 'Subscriber/created']));
    }

    public function testNoJsonException()
    {
        $this->expectException(EventException::class);
        $this->expectExceptionMessage("Can't decode request body as JSON, body: 'NotAJsonBody'");
        $this->expectExceptionCode(400);
        $e = new Event($this->request([], 'NotAJsonBody'));
    }

    public function testJsonNoEventType()
    {
        $this->expectException(EventException::class);
        $this->expectExceptionMessage("Mailformed JSON data, 'event_type' or 'variables' does not exist");
        $this->expectExceptionCode(400);
        $e = new Event($this->request([], '{"variables":{}}'));
    }

    public function testJsonNoVariables()
    {
        $this->expectException(EventException::class);
        $this->expectExceptionMessage("Mailformed JSON data, 'event_type' or 'variables' does not exist");
        $this->expectExceptionCode(400);
        $e = new Event($this->request([], '{"event_type":""}'));
    }

    public function testJsonNoBoth()
    {
        $this->expectException(EventException::class);
        $this->expectExceptionMessage("Mailformed JSON data, 'event_type' or 'variables' does not exist");
        $this->expectExceptionCode(400);
        $e = new Event($this->request([], '{}'));
    }
}
