<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Resources;

use ClaudePhp\Tests\TestCase;
use ClaudePhp\Responses\Message;

class BetaMessagesTest extends TestCase
{
    public function testBetaHeaderIsSetWhenBetasProvided(): void
    {
        $this->addMockResponse(200, [], $this->createMessageResponse());

        $result = $this->testClient->beta()->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
            'betas' => ['test-feature-2024-01-01', 'another-feature-2024-02-01'],
        ]);

        $this->assertInstanceOf(Message::class, $result);
        $this->assertHttpHeadersPresent([
            'anthropic-beta' => 'test-feature-2024-01-01,another-feature-2024-02-01',
        ]);
    }

    public function testBetaHeaderNotSetWhenNoBetas(): void
    {
        $this->addMockResponse(200, [], $this->createMessageResponse());

        $result = $this->testClient->beta()->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);

        $this->assertInstanceOf(Message::class, $result);

        $lastRequest = $this->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertFalse($lastRequest->hasHeader('anthropic-beta'));
    }

    public function testSingleBetaFeature(): void
    {
        $this->addMockResponse(200, [], $this->createMessageResponse());

        $result = $this->testClient->beta()->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
            'betas' => ['structured-outputs-2025-09-17'],
        ]);

        $this->assertInstanceOf(Message::class, $result);
        $this->assertHttpHeadersPresent([
            'anthropic-beta' => 'structured-outputs-2025-09-17',
        ]);
    }

    public function testBetasNotInRequestBody(): void
    {
        $this->addMockResponse(200, [], $this->createMessageResponse());

        $this->testClient->beta()->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
            'betas' => ['test-feature-2024-01-01'],
        ]);

        $lastRequest = $this->getLastRequest();
        $this->assertNotNull($lastRequest, 'Request should have been made');

        $bodyArray = json_decode((string) $lastRequest->getBody(), true);
        $this->assertIsArray($bodyArray, 'Request body should be valid JSON');
        $this->assertArrayNotHasKey('betas', $bodyArray, 'betas should not be in request body');
    }

    public function testEmptyBetasArrayDoesNotSetHeader(): void
    {
        $this->addMockResponse(200, [], $this->createMessageResponse());

        $result = $this->testClient->beta()->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
            'betas' => [],
        ]);

        $this->assertInstanceOf(Message::class, $result);

        $lastRequest = $this->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertFalse($lastRequest->hasHeader('anthropic-beta'));
    }
}
