<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit;

use ClaudePhp\Tests\TestCase;
use ClaudePhp\Tests\TestUtils;
use ClaudePhp\Types\Message;
use ClaudePhp\Types\MessageTokensCount;
use ClaudePhp\Types\Usage;
use PHPUnit\Framework\AssertionFailedError;

/**
 * Comprehensive type validation tests
 * Equivalent to Python SDK's type system validation
 */
class TypeValidationTest extends TestCase
{
    public function testAssertMatchesTypeWithBasicTypes(): void
    {
        // String validation
        TestUtils::assertMatchesType('string', 'hello');
        TestUtils::assertMatchesType('string', '');

        // Integer validation
        TestUtils::assertMatchesType('int', 42);
        TestUtils::assertMatchesType('integer', 0);
        TestUtils::assertMatchesType('int', -10);

        // Float validation
        TestUtils::assertMatchesType('float', 3.14);
        TestUtils::assertMatchesType('double', 0.0);

        // Boolean validation
        TestUtils::assertMatchesType('bool', true);
        TestUtils::assertMatchesType('boolean', false);

        // Array validation
        TestUtils::assertMatchesType('array', []);
        TestUtils::assertMatchesType('array', [1, 2, 3]);
        TestUtils::assertMatchesType('array', ['key' => 'value']);

        // Object validation
        TestUtils::assertMatchesType('object', new \stdClass());
        TestUtils::assertMatchesType('object', new Message('id', 'message', 'assistant', [], 'claude-sonnet-4-5-20250929', 'end_turn', null, new Usage(10, 20)));

        // Null validation
        TestUtils::assertMatchesType('null', null);
    }

    public function testAssertMatchesTypeWithClasses(): void
    {
        $message = new Message('test', 'message', 'assistant', [], 'claude-sonnet-4-5-20250929', 'end_turn', null, new Usage(10, 20));
        $usage = new Usage(10, 20);

        TestUtils::assertMatchesType(Message::class, $message);
        TestUtils::assertMatchesType(Usage::class, $usage);
        TestUtils::assertMatchesType(\stdClass::class, new \stdClass());
    }

    public function testAssertMatchesTypeWithUnionTypes(): void
    {
        // String or null
        TestUtils::assertMatchesType(['string', 'null'], 'hello');
        TestUtils::assertMatchesType(['string', 'null'], null);

        // Int or float
        TestUtils::assertMatchesType(['int', 'float'], 42);
        TestUtils::assertMatchesType(['int', 'float'], 3.14);

        // Array or object
        TestUtils::assertMatchesType(['array', 'object'], [1, 2, 3]);
        TestUtils::assertMatchesType(['array', 'object'], new \stdClass());

        // Class or null
        TestUtils::assertMatchesType([Message::class, 'null'], null);
        TestUtils::assertMatchesType(
            [Message::class, 'null'],
            new Message('test', 'message', 'assistant', [], 'claude-sonnet-4-5-20250929', 'end_turn', null, new Usage(10, 20)),
        );
    }

    public function testAssertMatchesTypeFailures(): void
    {
        // Wrong basic type
        $this->expectException(AssertionFailedError::class);
        TestUtils::assertMatchesType('string', 42);
    }

    public function testAssertMatchesTypeUnionFailure(): void
    {
        // None of the union types match
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Value does not match any of the union types');
        TestUtils::assertMatchesType(['string', 'int'], 3.14);
    }

    public function testAssertMatchesModelWithMessage(): void
    {
        $message = new Message(
            'msg_123',
            'message',
            'assistant',
            [['type' => 'text', 'text' => 'Hello']],
            'claude-sonnet-4-5-20250929',
            'end_turn',
            null,
            new Usage(10, 20),
        );

        TestUtils::assertMatchesModel($message, [
            'id' => 'string',
            'type' => 'string',
            'role' => 'string',
            'content' => 'array',
            'model' => 'string',
            'stop_reason' => 'string',
            'stop_sequence' => ['string', 'null'],
            'usage' => Usage::class,
        ]);
    }

    public function testAssertMatchesModelWithUsage(): void
    {
        $usage = new Usage(100, 50, 10, 5);

        TestUtils::assertMatchesModel($usage, [
            'input_tokens' => 'int',
            'output_tokens' => 'int',
            'cache_creation_input_tokens' => ['int', 'null'],
            'cache_read_input_tokens' => ['int', 'null'],
        ]);
    }

    public function testAssertMatchesModelFailureMissingProperty(): void
    {
        $object = new \stdClass();
        $object->name = 'test';

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Property 'missing_prop' does not exist on object");

        TestUtils::assertMatchesModel($object, [
            'name' => 'string',
            'missing_prop' => 'string',
        ]);
    }

    public function testAssertMatchesModelFailureWrongType(): void
    {
        $object = new \stdClass();
        $object->name = 123;

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Property 'name' has incorrect type");

        TestUtils::assertMatchesModel($object, [
            'name' => 'string',
        ]);
    }

    public function testMessageTokensCountValidation(): void
    {
        $tokenCount = new MessageTokensCount(150, 0);

        TestUtils::assertMatchesModel($tokenCount, [
            'input_tokens' => 'int',
            'output_tokens' => 'int',
        ]);

        $this->assertMatchesType('int', $tokenCount->input_tokens);
        $this->assertMatchesType('int', $tokenCount->output_tokens);
    }

    public function testComplexNestedValidation(): void
    {
        // Create a complex object structure
        $message = new Message(
            'msg_complex',
            'message',
            'assistant',
            [
                [
                    'type' => 'text',
                    'text' => 'Hello world',
                ],
                [
                    'type' => 'tool_use',
                    'id' => 'tool_123',
                    'name' => 'calculator',
                    'input' => ['operation' => 'add', 'numbers' => [1, 2]],
                ],
            ],
            'claude-sonnet-4-5-20250929',
            'tool_use',
            null,
            new Usage(25, 15, 5, 2),
        );

        // Validate top-level structure
        TestUtils::assertMatchesModel($message, [
            'id' => 'string',
            'type' => 'string',
            'role' => 'string',
            'content' => 'array',
            'model' => 'string',
            'stop_reason' => 'string',
            'usage' => Usage::class,
        ]);

        // Validate content array structure
        $this->assertIsArray($message->content);
        $this->assertCount(2, $message->content);

        // Validate first content block (text)
        $textBlock = $message->content[0];
        TestUtils::assertArrayStructure($textBlock, [
            'type' => 'string',
            'text' => 'string',
        ]);
        $this->assertEquals('text', $textBlock['type']);
        $this->assertEquals('Hello world', $textBlock['text']);

        // Validate second content block (tool_use)
        $toolBlock = $message->content[1];
        TestUtils::assertArrayStructure($toolBlock, [
            'type' => 'string',
            'id' => 'string',
            'name' => 'string',
            'input' => 'array',
        ]);
        $this->assertEquals('tool_use', $toolBlock['type']);
        $this->assertEquals('calculator', $toolBlock['name']);

        // Validate tool input structure
        TestUtils::assertArrayStructure($toolBlock['input'], [
            'operation' => 'string',
            'numbers' => 'array',
        ]);
    }

    public function testJsonStructureValidation(): void
    {
        $jsonString = json_encode([
            'id' => 'msg_123',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'Hello',
                ],
            ],
            'model' => 'claude-sonnet-4-5-20250929',
            'stop_reason' => 'end_turn',
            'usage' => [
                'input_tokens' => 10,
                'output_tokens' => 5,
            ],
        ]);

        TestUtils::assertJsonStructure($jsonString, [
            'id' => 'string',
            'type' => 'string',
            'role' => 'string',
            'content' => 'array',
            'model' => 'string',
            'stop_reason' => 'string',
            'usage' => [
                'input_tokens' => 'int',
                'output_tokens' => 'int',
            ],
        ]);
    }

    public function testJsonStructureValidationFailure(): void
    {
        $jsonString = '{"name": 123}'; // name should be string, not int

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Key 'name' has incorrect type");

        TestUtils::assertJsonStructure($jsonString, [
            'name' => 'string',
        ]);
    }

    public function testInvalidJsonValidation(): void
    {
        $invalidJson = '{invalid json}';

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Invalid JSON string');

        TestUtils::assertJsonStructure($invalidJson, []);
    }

    public function testStreamingEventValidation(): void
    {
        $eventData = "event: message_start\ndata: " . json_encode([
            'type' => 'message_start',
            'message' => [
                'id' => 'msg_123',
                'type' => 'message',
                'role' => 'assistant',
            ],
        ]) . "\n\n";

        // This should not throw an exception
        TestUtils::assertStreamingEvent($eventData, 'message_start');
    }

    public function testStreamingEventValidationFailure(): void
    {
        $eventData = "event: wrong_type\ndata: " . json_encode([
            'type' => 'message_start', // Mismatch between event type and data type
        ]) . "\n\n";

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Event type mismatch');

        TestUtils::assertStreamingEvent($eventData, 'message_start');
    }

    public function testArrayStructureWithNestedArrays(): void
    {
        $complexArray = [
            'metadata' => [
                'user_id' => 'user123',
                'conversation' => [
                    'id' => 'conv456',
                    'messages' => [
                        ['role' => 'user', 'content' => 'Hello'],
                        ['role' => 'assistant', 'content' => 'Hi there'],
                    ],
                ],
            ],
            'settings' => [
                'temperature' => 0.7,
                'max_tokens' => 100,
            ],
        ];

        TestUtils::assertArrayStructure($complexArray, [
            'metadata' => [
                'user_id' => 'string',
                'conversation' => [
                    'id' => 'string',
                    'messages' => 'array',
                ],
            ],
            'settings' => [
                'temperature' => 'float',
                'max_tokens' => 'int',
            ],
        ]);
    }

    public function testInvalidExpectedType(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Unknown type: invalid_type');

        TestUtils::assertMatchesType('invalid_type', 'test');
    }

    public function testInvalidExpectedTypeFormat(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Invalid expected type provided');

        TestUtils::assertMatchesType(123, 'test'); // Invalid type format
    }
}
