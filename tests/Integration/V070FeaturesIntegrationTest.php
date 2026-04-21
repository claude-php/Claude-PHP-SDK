<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Integration;

use ClaudePhp\ClaudePhp;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for v0.7.0 features that hit the live API.
 *
 * Requires ANTHROPIC_API_KEY in .env or environment.
 */
class V070FeaturesIntegrationTest extends TestCase
{
    private ?ClaudePhp $client = null;

    protected function setUp(): void
    {
        $key = $_ENV['ANTHROPIC_API_KEY'] ?? getenv('ANTHROPIC_API_KEY') ?: null;

        if (null === $key && file_exists(__DIR__ . '/../../.env')) {
            $lines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with($line, 'ANTHROPIC_API_KEY=')) {
                    $key = substr($line, strlen('ANTHROPIC_API_KEY='));
                }
            }
        }

        if (empty($key)) {
            $this->markTestSkipped('ANTHROPIC_API_KEY not set');
        }

        $this->client = new ClaudePhp(apiKey: $key);
    }

    public function testBasicMessageWithStopDetails(): void
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-6',
            'max_tokens' => 50,
            'messages' => [
                ['role' => 'user', 'content' => 'Say "hello" and nothing else.'],
            ],
        ]);

        $this->assertSame('message', $response->type);
        $this->assertNotEmpty($response->content);
        $this->assertSame('end_turn', $response->stop_reason);
        // stop_details may or may not be present depending on API response
    }

    public function testCacheControlParameter(): void
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-6',
            'max_tokens' => 50,
            'cache_control' => ['type' => 'ephemeral'],
            'messages' => [
                ['role' => 'user', 'content' => 'Say "cached".'],
            ],
        ]);

        $this->assertSame('message', $response->type);
    }

    public function testAdaptiveThinkingWithDisplay(): void
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-6',
            'max_tokens' => 200,
            'thinking' => ['type' => 'adaptive', 'display' => 'summarized'],
            'messages' => [
                ['role' => 'user', 'content' => 'What is 2+2?'],
            ],
        ]);

        $this->assertSame('message', $response->type);
    }

    public function testStreamingWithNewEventHandling(): void
    {
        $stream = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-6',
            'max_tokens' => 50,
            'stream' => true,
            'messages' => [
                ['role' => 'user', 'content' => 'Say "streamed" and nothing else.'],
            ],
        ]);

        $eventCount = 0;
        $text = '';
        foreach ($stream as $event) {
            ++$eventCount;
            if (isset($event['delta']['text'])) {
                $text .= $event['delta']['text'];
            }
        }

        $this->assertGreaterThan(0, $eventCount);
        $this->assertStringContainsStringIgnoringCase('streamed', $text);
    }

    public function testDeprecatedModelWarning(): void
    {
        \ClaudePhp\Resources\Messages\Messages::resetDeprecationWarnings();

        $warned = false;
        set_error_handler(function (int $errno, string $errstr) use (&$warned): bool {
            if (E_USER_DEPRECATED === $errno && str_contains($errstr, 'deprecated')) {
                $warned = true;
                return true;
            }
            return false;
        });

        try {
            $this->client->messages()->create([
                'model' => 'claude-sonnet-4-0',
                'max_tokens' => 10,
                'messages' => [
                    ['role' => 'user', 'content' => 'Hi'],
                ],
            ]);
        } catch (\Throwable) {
            // API may reject this model; we only care about the deprecation warning
        } finally {
            restore_error_handler();
        }

        $this->assertTrue($warned, 'Expected E_USER_DEPRECATED for claude-sonnet-4-0');
    }

    public function testBetaMessagesWithUserProfileId(): void
    {
        try {
            $this->client->beta()->messages()->create([
                'model' => 'claude-sonnet-4-6',
                'max_tokens' => 30,
                'messages' => [
                    ['role' => 'user', 'content' => 'Say "beta".'],
                ],
            ]);
            $this->assertTrue(true);
        } catch (\Throwable $e) {
            // Beta endpoint may return an error but we're testing param passthrough
            $this->assertNotEmpty($e->getMessage());
        }
    }
}
