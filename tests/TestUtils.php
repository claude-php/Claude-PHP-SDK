<?php

declare(strict_types=1);

namespace ClaudePhp\Tests;

use PHPUnit\Framework\Assert;

/**
 * Test utilities for comprehensive type validation and test helpers
 * Equivalent to Python SDK's test utilities
 */
class TestUtils
{
    /**
     * Assert that a value matches the expected type with sophisticated validation
     * Equivalent to Python's assert_matches_type()
     *
     * @param mixed $expected_type The expected type or class name
     * @param mixed $actual_value The value to check
     * @param string $message Optional failure message
     */
    public static function assertMatchesType(mixed $expected_type, mixed $actual_value, string $message = ''): void
    {
        if (is_string($expected_type)) {
            // Handle class names
            if (class_exists($expected_type) || interface_exists($expected_type)) {
                Assert::assertInstanceOf($expected_type, $actual_value, $message);
                return;
            }

            // Handle built-in types
            match ($expected_type) {
                'string' => Assert::assertIsString($actual_value, $message),
                'int', 'integer' => Assert::assertIsInt($actual_value, $message),
                'float', 'double' => Assert::assertIsFloat($actual_value, $message),
                'bool', 'boolean' => Assert::assertIsBool($actual_value, $message),
                'array' => Assert::assertIsArray($actual_value, $message),
                'object' => Assert::assertIsObject($actual_value, $message),
                'null' => Assert::assertNull($actual_value, $message),
                'resource' => Assert::assertIsResource($actual_value, $message),
                'callable' => Assert::assertIsCallable($actual_value, $message),
                default => Assert::fail("Unknown type: $expected_type")
            };
        } elseif (is_array($expected_type)) {
            // Handle union types represented as arrays
            $matched = false;
            $errors = [];
            
            foreach ($expected_type as $type) {
                try {
                    self::assertMatchesType($type, $actual_value, $message);
                    $matched = true;
                    break;
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
            
            if (!$matched) {
                $types = implode('|', $expected_type);
                $error_details = implode(', ', $errors);
                Assert::fail("Value does not match any of the union types [$types]. Errors: $error_details");
            }
        } else {
            Assert::fail('Invalid expected type provided');
        }
    }

    /**
     * Assert that an object has all expected properties with correct types
     *
     * @param object $object The object to validate
     * @param array<string, mixed> $expected_properties Property name => expected type
     * @param string $message Optional failure message
     */
    public static function assertMatchesModel(object $object, array $expected_properties, string $message = ''): void
    {
        foreach ($expected_properties as $property => $expected_type) {
            Assert::assertTrue(
                property_exists($object, $property),
                "Property '$property' does not exist on object. " . $message
            );
            
            $actual_value = $object->$property;
            self::assertMatchesType(
                $expected_type,
                $actual_value,
                "Property '$property' has incorrect type. " . $message
            );
        }
    }

    /**
     * Update environment variables for testing with automatic cleanup
     *
     * @param array<string, string|null> $vars Variables to set (null to unset)
     * @return \Closure Cleanup function to restore original values
     */
    public static function updateEnv(array $vars): \Closure
    {
        $original = [];
        
        foreach ($vars as $key => $value) {
            $original[$key] = $_ENV[$key] ?? null;
            
            if ($value === null) {
                unset($_ENV[$key]);
                if (isset($_SERVER[$key])) {
                    unset($_SERVER[$key]);
                }
            } else {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
        
        return function () use ($original): void {
            foreach ($original as $key => $value) {
                if ($value === null) {
                    unset($_ENV[$key]);
                    if (isset($_SERVER[$key])) {
                        unset($_SERVER[$key]);
                    }
                } else {
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        };
    }

    /**
     * Create a mock HTTP response for testing
     *
     * @param int $status HTTP status code
     * @param array<string, string> $headers Response headers
     * @param string $body Response body
     * @return array Mock response data
     */
    public static function createMockResponse(int $status = 200, array $headers = [], string $body = ''): array
    {
        return [
            'status' => $status,
            'headers' => $headers,
            'body' => $body,
        ];
    }

    /**
     * Generate test API key for consistent testing
     *
     * @return string Test API key
     */
    public static function getTestApiKey(): string
    {
        return 'sk-ant-test-key-12345678901234567890123456789012345678901234567890';
    }

    /**
     * Get test base URL for API mocking
     *
     * @return string Test API base URL
     */
    public static function getTestBaseUrl(): string
    {
        return 'http://127.0.0.1:4010';
    }

    /**
     * Assert that a JSON string contains expected structure
     *
     * @param string $json_string JSON to validate
     * @param array<string, mixed> $expected_structure Expected structure
     * @param string $message Optional failure message
     */
    public static function assertJsonStructure(string $json_string, array $expected_structure, string $message = ''): void
    {
        $decoded = json_decode($json_string, true);
        Assert::assertIsArray($decoded, "Invalid JSON string. " . $message);
        
        self::assertArrayStructure($decoded, $expected_structure, $message);
    }

    /**
     * Assert that an array has the expected structure
     *
     * @param array<mixed> $array Array to validate
     * @param array<string, mixed> $expected_structure Expected structure
     * @param string $message Optional failure message
     */
    public static function assertArrayStructure(array $array, array $expected_structure, string $message = ''): void
    {
        foreach ($expected_structure as $key => $expected_type) {
            if (is_string($key)) {
                Assert::assertArrayHasKey($key, $array, "Missing key '$key'. " . $message);
                
                if (is_array($expected_type)) {
                    if (isset($array[$key]) && is_array($array[$key])) {
                        self::assertArrayStructure($array[$key], $expected_type, $message);
                    }
                } else {
                    self::assertMatchesType($expected_type, $array[$key], "Key '$key' has incorrect type. " . $message);
                }
            }
        }
    }

    /**
     * Validate streaming message format
     *
     * @param string $event_data SSE event data
     * @param string $expected_type Expected event type
     */
    public static function assertStreamingEvent(string $event_data, string $expected_type): void
    {
        // Check for event line
        $lines = explode("\n", $event_data);
        $event_line = null;
        $data_line = null;
        
        foreach ($lines as $line) {
            if (str_starts_with($line, 'event: ')) {
                $event_line = substr($line, 7);
            } elseif (str_starts_with($line, 'data: ')) {
                $data_line = substr($line, 6);
            }
        }
        
        Assert::assertNotNull($event_line, 'No event line found in SSE event');
        Assert::assertEquals($expected_type, $event_line, 'Event type mismatch in event line');
        
        Assert::assertNotNull($data_line, 'No data line found in SSE event');
        
        if (trim($data_line) !== '[DONE]') {
            $json = json_decode($data_line, true);
            Assert::assertIsArray($json, 'Invalid JSON in SSE data');
            Assert::assertArrayHasKey('type', $json, 'Missing type in SSE event data');
            Assert::assertEquals($expected_type, $json['type'], 'Event type mismatch in data');
        }
    }
}