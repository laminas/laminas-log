<?php

namespace LaminasTest\Log\Processor;

use Laminas\Log\Processor\ReferenceId;
use PHPUnit\Framework\TestCase;

class ReferenceIdTest extends TestCase
{
    public function testProcessMixesInReferenceId(): void
    {
        $processor      = new ReferenceId();
        $processedEvent = $processor->process([
            'timestamp'    => '',
            'priority'     => 1,
            'priorityName' => 'ALERT',
            'message'      => 'foo',
        ]);

        $this->assertArrayHasKey('extra', $processedEvent);
        $this->assertIsArray($processedEvent['extra']);
        $this->assertArrayHasKey('referenceId', $processedEvent['extra']);

        $this->assertNotNull($processedEvent['extra']['referenceId']);
    }

    public function testProcessDoesNotOverwriteReferenceId(): void
    {
        $processor      = new ReferenceId();
        $referenceId    = 'bar';
        $processedEvent = $processor->process([
            'timestamp'    => '',
            'priority'     => 1,
            'priorityName' => 'ALERT',
            'message'      => 'foo',
            'extra'        => [
                'referenceId' => $referenceId,
            ],
        ]);

        $this->assertArrayHasKey('extra', $processedEvent);
        $this->assertIsArray($processedEvent['extra']);
        $this->assertArrayHasKey('referenceId', $processedEvent['extra']);

        $this->assertSame($referenceId, $processedEvent['extra']['referenceId']);
    }

    public function testCanSetAndGetReferenceId(): void
    {
        $processor   = new ReferenceId();
        $referenceId = 'foo';

        $processor->setReferenceId($referenceId);

        $this->assertSame($referenceId, $processor->getReferenceId());
    }

    public function testProcessUsesSetReferenceId(): void
    {
        $referenceId = 'foo';
        $processor   = new ReferenceId();

        $processor->setReferenceId($referenceId);

        $processedEvent = $processor->process([
            'timestamp'    => '',
            'priority'     => 1,
            'priorityName' => 'ALERT',
            'message'      => 'foo',
        ]);

        $this->assertArrayHasKey('extra', $processedEvent);
        $this->assertIsArray($processedEvent['extra']);
        $this->assertArrayHasKey('referenceId', $processedEvent['extra']);

        $this->assertSame($referenceId, $processedEvent['extra']['referenceId']);
    }
}
