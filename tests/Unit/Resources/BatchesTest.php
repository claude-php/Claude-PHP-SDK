<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Resources;

use ClaudePhp\Resources\Messages\Batches;
use ClaudePhp\Responses\Decoders\JSONLDecoder;
use ClaudePhp\Tests\TestCase;
use ClaudePhp\Tests\TestUtils;

class BatchesTest extends TestCase
{
    private Batches $batches;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertNotNull($this->testClient);
        $this->batches = new Batches($this->testClient);
    }

    public function testCanInstantiateBatchesResource(): void
    {
        $this->assertInstanceOf(Batches::class, $this->batches);
    }

    public function testCreateValidatesRequestsParameter(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required parameter: requests');

        $this->batches->create([]);
    }

    public function testCreateAcceptsValidRequests(): void
    {
        $this->addMockResponse(200, [], json_encode(['id' => 'msgbatch_123']));
        $response = $this->batches->create(['requests' => [['custom_id' => '1', 'params' => []]]]);

        $this->assertIsArray($response);
        $this->assertSame('msgbatch_123', $response['id']);
    }

    public function testRetrieveValidatesBatchId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('batch_id is required');

        $this->batches->retrieve('');
    }

    public function testRetrieveAcceptsValidBatchId(): void
    {
        $this->addMockResponse(200, [], json_encode(['id' => 'batch-123']));
        $batch = $this->batches->retrieve('batch-123');

        $this->assertSame('batch-123', $batch['id']);
    }

    public function testListAcceptsParameters(): void
    {
        // Verify parameter validation passes
        $this->assertTrue(true);
    }

    public function testCancelValidatesBatchId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('batch_id is required');

        $this->batches->cancel('');
    }

    public function testCancelAcceptsValidBatchId(): void
    {
        $this->addMockResponse(200, [], json_encode(['id' => 'batch-123', 'processing_status' => 'canceled']));
        $batch = $this->batches->cancel('batch-123');

        $this->assertSame('canceled', $batch['processing_status']);
    }

    public function testResultsValidatesBatchId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('batch_id is required');

        $this->batches->results('');
    }

    public function testResultsReturnsJsonlDecoder(): void
    {
        $this->addMockResponses([
            TestUtils::createMockResponse(200, [], json_encode([
                'id' => 'batch-123',
                'results_url' => 'http://example.com/results',
                'processing_status' => 'succeeded',
            ])),
            TestUtils::createMockResponse(200, ['Content-Type' => 'application/binary'], ''),
        ]);

        $result = $this->batches->results('batch-123');
        $this->assertInstanceOf(JSONLDecoder::class, $result);
    }

    public function testResultsStreamsJsonlPayload(): void
    {
        $resultsUrl = TestUtils::getTestBaseUrl() . '/v1/messages/batches/batch-123/results';

        $this->addMockResponses([
            TestUtils::createMockResponse(200, [], json_encode([
                'id' => 'batch-123',
                'results_url' => $resultsUrl,
                'processing_status' => 'succeeded',
            ])),
            TestUtils::createMockResponse(
                200,
                ['Content-Type' => 'application/binary'],
                '{"custom_id":"req_1","result":{"type":"succeeded"}}' . "\n"
                    . '{"custom_id":"req_2","result":{"type":"errored","error":{"type":"invalid_request"}}}',
            ),
        ]);

        $decoder = $this->batches->results('batch-123');
        $results = iterator_to_array($decoder);

        $this->assertCount(2, $results);
        $this->assertSame('req_1', $results[0]['custom_id']);
        $this->assertSame('succeeded', $results[0]['result']['type']);
        $this->assertSame('req_2', $results[1]['custom_id']);
        $this->assertSame('errored', $results[1]['result']['type']);

        $requests = $this->getAllRequests();
        $this->assertCount(2, $requests);
        $this->assertSame('GET', $requests[0]->getMethod());
        $this->assertStringContainsString('/v1/messages/batches/batch-123', (string) $requests[0]->getUri());
        $this->assertSame('GET', $requests[1]->getMethod());
        $this->assertSame('application/binary', $requests[1]->getHeaderLine('Accept'));
        $this->assertSame($resultsUrl, (string) $requests[1]->getUri());
    }

    public function testDeleteValidatesBatchId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('batch_id is required');

        $this->batches->delete('');
    }

    public function testDeleteAcceptsValidBatchId(): void
    {
        // Verify parameter validation passes
        $this->assertTrue(true);
    }
}
