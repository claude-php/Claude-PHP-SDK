<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Integration;

use ClaudePhp\Responses\Decoders\JSONLDecoder;
use ClaudePhp\Tests\TestCase;

class BatchResultsTest extends TestCase
{
    public function testResultsStreamDecodesJsonl(): void
    {
        $resultsUrl = 'http://127.0.0.1:4010/mock-results';

        $this->addMockResponses([
            [
                'status' => 200,
                'body' => json_encode([
                    'id' => 'msgbatch_123',
                    'results_url' => $resultsUrl,
                    'processing_status' => 'succeeded',
                    'usage' => ['input_tokens' => 0, 'output_tokens' => 0],
                ]),
            ],
            [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/binary'],
                'body' => '{"custom_id":"req_1","result":{"type":"succeeded"}}' . "\n"
                    . '{"custom_id":"req_2","result":{"type":"errored","error":{"type":"invalid_request"}}}',
            ],
        ]);

        $batches = $this->testClient->messages()->batches();
        $decoder = $batches->results('msgbatch_123');

        $this->assertInstanceOf(JSONLDecoder::class, $decoder);

        $results = iterator_to_array($decoder);
        $this->assertCount(2, $results);
        $this->assertSame('req_1', $results[0]['custom_id']);
        $this->assertSame('succeeded', $results[0]['result']['type']);
        $this->assertSame('errored', $results[1]['result']['type']);
    }
}
