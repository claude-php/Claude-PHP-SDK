<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Utils;

use ClaudePhp\Types\NotGiven;
use ClaudePhp\Utils\RequiredArgs;
use PHPUnit\Framework\TestCase;

class RequiredArgsTest extends TestCase
{
    public function testValidateWithRequiredArgsProvided(): void
    {
        $func = function ($id, $name) {
            return "{$id}:{$name}";
        };

        $validated = RequiredArgs::validate($func, ['id', 'name']);
        $result = $validated('123', 'test');

        $this->assertSame('123:test', $result);
    }

    public function testValidateThrowsOnMissingRequired(): void
    {
        $func = function ($id, $name) {
            return "{$id}:{$name}";
        };

        $validated = RequiredArgs::validate($func, ['id', 'name']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Missing required arguments/');
        $validated('123'); // Missing $name
    }

    public function testValidateWithPartialRequired(): void
    {
        $func = function ($id, $name, $email) {
            return "{$id}:{$name}:{$email}";
        };

        $validated = RequiredArgs::validate($func, ['id']);
        $result = $validated('123', 'test', 'test@example.com');

        $this->assertSame('123:test:test@example.com', $result);
    }

    public function testValidateWithNotGivenThrows(): void
    {
        $func = function ($id, $name) {
            return "{$id}:{$name}";
        };

        $validated = RequiredArgs::validate($func, ['id', 'name']);

        $this->expectException(\InvalidArgumentException::class);
        $validated('123', NotGiven::getInstance());
    }

    public function testCheckRequiredArgsWithValidArguments(): void
    {
        $func = function ($id, $name) {
            return true;
        };

        // Should not throw
        RequiredArgs::checkRequiredArgs($func, ['id', 'name'], null, ['123', 'test']);
        $this->assertTrue(true);
    }

    public function testCheckRequiredArgsWithMissingArgument(): void
    {
        $func = function ($id, $name) {
            return true;
        };

        $this->expectException(\InvalidArgumentException::class);
        RequiredArgs::checkRequiredArgs($func, ['id', 'name'], null, ['123']);
    }

    public function testValidateWithMethod(): void
    {
        $obj = new class {
            public function handle($id, $name)
            {
                return "{$id}:{$name}";
            }
        };

        $validated = RequiredArgs::validate([$obj, 'handle'], ['id', 'name']);
        $result = $validated('456', 'method');

        $this->assertSame('456:method', $result);
    }

    public function testValidateWithStaticMethod(): void
    {
        $validated = RequiredArgs::validate([RequiredArgs::class, 'checkRequiredArgs'], ['func']);
        // Static method call should work without throwing during validation
        $this->assertIsCallable($validated);
    }

    public function testValidateWithConditionalArgs(): void
    {
        $func = function ($mode, $api_key, $token) {
            return "{$mode}:{$api_key}:{$token}";
        };

        $validated = RequiredArgs::validate(
            $func,
            ['mode'],
            ['mode' => ['api_key']],
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/must also provide.*api_key/');
        $validated('auth', NotGiven::getInstance(), 'token123');
    }

    public function testValidateConditionalArgsPass(): void
    {
        $func = function ($mode, $api_key, $token) {
            return "{$mode}:{$api_key}:{$token}";
        };

        $validated = RequiredArgs::validate(
            $func,
            ['mode'],
            ['mode' => ['api_key']],
        );

        $result = $validated('auth', 'key123', 'token123');
        $this->assertSame('auth:key123:token123', $result);
    }

    public function testValidateMultipleCallsWithDifferentArgs(): void
    {
        $callCount = 0;
        $func = function ($id) use (&$callCount) {
            ++$callCount;

            return $id;
        };

        $validated = RequiredArgs::validate($func, ['id']);

        $validated('first');
        $validated('second');
        $validated('third');

        $this->assertSame(3, $callCount);
    }

    public function testValidateWithTypedParameters(): void
    {
        $func = function (string $id, int $age) {
            return "{$id}:{$age}";
        };

        $validated = RequiredArgs::validate($func, ['id', 'age']);
        $result = $validated('user123', 25);

        $this->assertSame('user123:25', $result);
    }

    public function testValidatePreservesReturnValue(): void
    {
        $func = function ($data) {
            return ['status' => 'ok', 'data' => $data];
        };

        $validated = RequiredArgs::validate($func, ['data']);
        $result = $validated('test_value');

        $this->assertSame(['status' => 'ok', 'data' => 'test_value'], $result);
    }

    public function testValidatePassesThroughException(): void
    {
        $func = function ($id) {
            throw new \RuntimeException('Custom error');
        };

        $validated = RequiredArgs::validate($func, ['id']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Custom error');
        $validated('123');
    }
}
