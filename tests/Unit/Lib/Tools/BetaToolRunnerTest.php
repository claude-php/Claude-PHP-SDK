<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Lib\Tools;

use ClaudePhp\ClaudePhp;

use function ClaudePhp\Lib\Tools\beta_tool;

use ClaudePhp\Lib\Tools\BetaToolRunner;
use ClaudePhp\Resources\Beta\Beta;
use ClaudePhp\Resources\Beta\Messages;
use ClaudePhp\Responses\Message;
use ClaudePhp\Responses\Usage;
use PHPUnit\Framework\TestCase;

class BetaToolRunnerTest extends TestCase
{
    public function testExecutesToolCallsAndContinuesConversation(): void
    {
        $responses = [
            [
                'content' => [
                    [
                        'type' => 'tool_use',
                        'id' => 'tool_1',
                        'name' => 'get_weather',
                        'input' => ['location' => 'San Francisco'],
                    ],
                ],
            ],
            [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Weather summarized.',
                    ],
                ],
            ],
        ];

        $dummyClient = new ClaudePhp(apiKey: 'sk-test');

        $messagesResource = new class ($dummyClient, $responses) extends Messages {
            /** @var array<int, array<string, mixed>> */
            private array $queued;

            public function __construct(ClaudePhp $client, array $responses)
            {
                parent::__construct($client);
                $this->queued = $responses;
            }

            public function create(array $params = []): Message
            {
                $payload = array_shift($this->queued) ?? [];

                return new Message(
                    id: $payload['id'] ?? 'msg',
                    type: 'message',
                    role: 'assistant',
                    content: $payload['content'] ?? [],
                    model: 'claude-sonnet',
                    stop_reason: $payload['stop_reason'] ?? 'end_turn',
                    usage: new Usage(input_tokens: 0, output_tokens: 0),
                );
            }
        };

        $betaStub = new class ($dummyClient, $messagesResource) extends Beta {
            public function __construct(ClaudePhp $client, private Messages $messages)
            {
                parent::__construct($client);
            }

            public function messages(): Messages
            {
                return $this->messages;
            }
        };

        $client = $this->createMock(ClaudePhp::class);
        $client->method('beta')->willReturn($betaStub);

        $tool = beta_tool(
            handler: function (array $input): string {
                TestCase::assertSame('San Francisco', $input['location']);

                return 'It is sunny.';
            },
            name: 'get_weather',
            description: 'Fetch weather',
            inputSchema: [
                'type' => 'object',
                'properties' => [
                    'location' => ['type' => 'string'],
                ],
                'required' => ['location'],
            ],
        );

        $runner = new BetaToolRunner($client, [
            'model' => 'claude-sonnet',
            'max_tokens' => 256,
            'messages' => [
                ['role' => 'user', 'content' => 'What is the weather?'],
            ],
        ], [$tool]);

        $messages = iterator_to_array($runner);

        $this->assertCount(2, $messages);
        $this->assertInstanceOf(Message::class, $messages[1]);
        $this->assertSame('Weather summarized.', $messages[1]->content[0]['text']);
    }
}
