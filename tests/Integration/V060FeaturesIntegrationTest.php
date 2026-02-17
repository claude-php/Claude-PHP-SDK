<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Integration;

use ClaudePhp\Responses\Message as ResponseMessage;
use ClaudePhp\Tests\TestCase;
use ClaudePhp\Types\Message;
use ClaudePhp\Types\MessageTokensCount;

/**
 * Integration tests for v0.6.0 features.
 *
 * Covers:
 *  - Adaptive thinking (ThinkingConfigAdaptiveParam)
 *  - Speed / fast-mode parameter in Beta Messages
 *  - output_config in GA Messages
 *  - New model constants via ModelParam
 *  - Code execution tool types
 *  - Memory tool types
 *  - Web fetch tool types
 *  - Beta web search v2
 */
class V060FeaturesIntegrationTest extends TestCase
{
    // -------------------------------------------------------------------------
    // SDK Version
    // -------------------------------------------------------------------------

    public function testSdkVersionIsCorrect(): void
    {
        $this->assertEquals('0.6.0', \ClaudePhp\ClaudePhp::SDK_VERSION);
    }

    public function testUserAgentReflectsNewVersion(): void
    {
        $responseBody = $this->createMessageResponse('OK');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $this->testClient->messages()->create([
            'model'      => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 10,
            'messages'   => [['role' => 'user', 'content' => 'Hi']],
        ]);

        $this->assertHttpHeadersPresent(['User-Agent' => 'ClaudePhp/0.6.0']);
    }

    // -------------------------------------------------------------------------
    // Adaptive Thinking
    // -------------------------------------------------------------------------

    public function testAdaptiveThinkingParamPassesThrough(): void
    {
        $responseBody = $this->createMessageResponse('Adaptive response');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $response = $this->testClient->messages()->create([
            'model'      => 'claude-opus-4-6',
            'max_tokens' => 4096,
            'thinking'   => ['type' => 'adaptive'],
            'messages'   => [['role' => 'user', 'content' => 'Test adaptive thinking']],
        ]);

        $this->assertInstanceOf(Message::class, $response);

        // Verify the request body contained the adaptive thinking config
        $lastRequest = $this->getLastRequest();
        $body        = json_decode((string) $lastRequest->getBody(), true);

        $this->assertArrayHasKey('thinking', $body);
        $this->assertEquals('adaptive', $body['thinking']['type']);
    }

    public function testAdaptiveThinkingWithBetaMessages(): void
    {
        $responseBody = $this->createMessageResponse('Beta adaptive response');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $response = $this->testClient->beta()->messages()->create([
            'model'      => 'claude-opus-4-6',
            'max_tokens' => 4096,
            'thinking'   => ['type' => 'adaptive'],
            'messages'   => [['role' => 'user', 'content' => 'Test beta adaptive thinking']],
        ]);

        $this->assertInstanceOf(ResponseMessage::class, $response);

        $lastRequest = $this->getLastRequest();
        $body        = json_decode((string) $lastRequest->getBody(), true);

        $this->assertEquals('adaptive', $body['thinking']['type']);
    }

    public function testAdaptiveThinkingTypeClass(): void
    {
        $param = new \ClaudePhp\Types\ThinkingConfigAdaptiveParam();

        $this->assertEquals('adaptive', $param->type);
        $this->assertEquals(['type' => 'adaptive'], $param->toArray());
    }

    public function testBetaAdaptiveThinkingTypeClass(): void
    {
        $param = new \ClaudePhp\Types\Beta\BetaThinkingConfigAdaptiveParam();

        $this->assertEquals('adaptive', $param->type);
        $this->assertEquals(['type' => 'adaptive'], $param->toArray());
    }

    // -------------------------------------------------------------------------
    // Speed / Fast-mode Parameter
    // -------------------------------------------------------------------------

    public function testSpeedParameterPassesThroughToBetaMessages(): void
    {
        $responseBody = $this->createMessageResponse('Fast response');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $response = $this->testClient->beta()->messages()->create([
            'model'      => 'claude-opus-4-6',
            'max_tokens' => 512,
            'speed'      => 'fast',
            'messages'   => [['role' => 'user', 'content' => 'Test fast mode']],
        ]);

        $this->assertInstanceOf(ResponseMessage::class, $response);

        $lastRequest = $this->getLastRequest();
        $body        = json_decode((string) $lastRequest->getBody(), true);

        $this->assertArrayHasKey('speed', $body);
        $this->assertEquals('fast', $body['speed']);
    }

    public function testStandardSpeedParameterPassesThrough(): void
    {
        $responseBody = $this->createMessageResponse('Standard response');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $this->testClient->beta()->messages()->create([
            'model'      => 'claude-opus-4-6',
            'max_tokens' => 512,
            'speed'      => 'standard',
            'messages'   => [['role' => 'user', 'content' => 'Test standard mode']],
        ]);

        $lastRequest = $this->getLastRequest();
        $body        = json_decode((string) $lastRequest->getBody(), true);

        $this->assertEquals('standard', $body['speed']);
    }

    public function testSpeedParameterInCountTokens(): void
    {
        $tokenResponse = json_encode([
            'input_tokens'  => 25,
            'output_tokens' => 0,
        ]);
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $tokenResponse);

        $result = $this->testClient->beta()->messages()->countTokens([
            'model'    => 'claude-opus-4-6',
            'speed'    => 'fast',
            'messages' => [['role' => 'user', 'content' => 'Count tokens fast']],
        ]);

        $this->assertInstanceOf(MessageTokensCount::class, $result);

        $lastRequest = $this->getLastRequest();
        $body        = json_decode((string) $lastRequest->getBody(), true);

        $this->assertEquals('fast', $body['speed']);
    }

    // -------------------------------------------------------------------------
    // output_config in GA Messages
    // -------------------------------------------------------------------------

    public function testOutputConfigPassesThroughToGaMessages(): void
    {
        $responseBody = $this->createMessageResponse('Structured output');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $this->testClient->messages()->create([
            'model'         => 'claude-sonnet-4-5-20250929',
            'max_tokens'    => 1024,
            'output_config' => ['effort' => 'high'],
            'messages'      => [['role' => 'user', 'content' => 'Structured test']],
        ]);

        $lastRequest = $this->getLastRequest();
        $body        = json_decode((string) $lastRequest->getBody(), true);

        $this->assertArrayHasKey('output_config', $body);
        $this->assertEquals('high', $body['output_config']['effort']);
    }

    public function testOutputConfigAndOutputFormatInBetaMessages(): void
    {
        $responseBody = $this->createMessageResponse('Beta structured output');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $this->testClient->beta()->messages()->create([
            'model'         => 'claude-sonnet-4-5-20250929',
            'max_tokens'    => 1024,
            'output_config' => ['effort' => 'medium'],
            'messages'      => [['role' => 'user', 'content' => 'Beta structured test']],
        ]);

        $lastRequest = $this->getLastRequest();
        $body        = json_decode((string) $lastRequest->getBody(), true);

        $this->assertArrayHasKey('output_config', $body);
        $this->assertEquals('medium', $body['output_config']['effort']);
    }

    // -------------------------------------------------------------------------
    // Model Constants
    // -------------------------------------------------------------------------

    public function testModelParamContainsNewModelConstants(): void
    {
        // Claude 4.6 models (Feb 2026)
        $this->assertEquals('claude-opus-4-6', \ClaudePhp\Types\ModelParam::MODEL_CLAUDE_OPUS_4_6);
        $this->assertEquals('claude-sonnet-4-6', \ClaudePhp\Types\ModelParam::MODEL_CLAUDE_SONNET_4_6);

        // Claude 3.7 Sonnet
        $this->assertEquals('claude-3-7-sonnet-latest', \ClaudePhp\Types\ModelParam::MODEL_CLAUDE_3_7_SONNET_LATEST);
        $this->assertEquals('claude-3-7-sonnet-20250219', \ClaudePhp\Types\ModelParam::MODEL_CLAUDE_3_7_SONNET_20250219);

        // Claude 4.5 family
        $this->assertEquals('claude-opus-4-5-20251101', \ClaudePhp\Types\ModelParam::MODEL_CLAUDE_OPUS_4_5);
        $this->assertEquals('claude-sonnet-4-5-20250929', \ClaudePhp\Types\ModelParam::MODEL_CLAUDE_SONNET_4_5);
        $this->assertEquals('claude-haiku-4-5-20251001', \ClaudePhp\Types\ModelParam::MODEL_CLAUDE_HAIKU_4_5);

        // Claude 3.5 family
        $this->assertEquals('claude-3-5-haiku-latest', \ClaudePhp\Types\ModelParam::MODEL_CLAUDE_3_5_HAIKU_LATEST);
        $this->assertEquals('claude-3-5-haiku-20241022', \ClaudePhp\Types\ModelParam::MODEL_CLAUDE_3_5_HAIKU_20241022);

        // Claude 3 legacy
        $this->assertEquals('claude-3-opus-latest', \ClaudePhp\Types\ModelParam::MODEL_CLAUDE_3_OPUS_LATEST);
        $this->assertEquals('claude-3-haiku-20240307', \ClaudePhp\Types\ModelParam::MODEL_CLAUDE_3_HAIKU_20240307);
    }

    public function testModelParamConstantUsedInApiCall(): void
    {
        $responseBody = $this->createMessageResponse('Using constant');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $this->testClient->messages()->create([
            'model'      => \ClaudePhp\Types\ModelParam::MODEL_CLAUDE_OPUS_4_6,
            'max_tokens' => 10,
            'messages'   => [['role' => 'user', 'content' => 'Hi']],
        ]);

        $lastRequest = $this->getLastRequest();
        $body        = json_decode((string) $lastRequest->getBody(), true);

        $this->assertEquals('claude-opus-4-6', $body['model']);
    }

    // -------------------------------------------------------------------------
    // Code Execution Tool Types
    // -------------------------------------------------------------------------

    public function testCodeExecutionTool20250522ParamProperties(): void
    {
        $tool = new \ClaudePhp\Types\CodeExecutionTool20250522Param(
            name:             'code_execution',
            type:             'code_execution_20250522',
            allowed_callers:  ['direct'],
            defer_loading:    false,
            strict:           true,
        );

        $this->assertEquals('code_execution', $tool->name);
        $this->assertEquals('code_execution_20250522', $tool->type);
        $this->assertEquals(['direct'], $tool->allowed_callers);
        $this->assertFalse($tool->defer_loading);
        $this->assertTrue($tool->strict);
    }

    public function testCodeExecutionTool20250825ParamProperties(): void
    {
        $tool = new \ClaudePhp\Types\CodeExecutionTool20250825Param(
            name: 'code_execution',
            type: 'code_execution_20250825',
        );

        $this->assertEquals('code_execution', $tool->name);
        $this->assertEquals('code_execution_20250825', $tool->type);
    }

    public function testBetaCodeExecutionTool20260120ParamProperties(): void
    {
        $tool = new \ClaudePhp\Types\Beta\BetaCodeExecutionTool20260120Param(
            name:   'code_execution',
            type:   'code_execution_20260120',
            strict: true,
        );

        $this->assertEquals('code_execution', $tool->name);
        $this->assertEquals('code_execution_20260120', $tool->type);
        $this->assertTrue($tool->strict);
    }

    public function testCodeExecutionResultBlockProperties(): void
    {
        $output = new \ClaudePhp\Types\CodeExecutionOutputBlock(file_id: 'file_abc123');
        $result = new \ClaudePhp\Types\CodeExecutionResultBlock(
            content:     [$output],
            return_code: 0,
            stderr:      '',
            stdout:      'Hello, World!',
        );

        $this->assertEquals('code_execution_result', $result->type);
        $this->assertEquals(0, $result->return_code);
        $this->assertEquals('Hello, World!', $result->stdout);
        $this->assertCount(1, $result->content);
        $this->assertEquals('file_abc123', $result->content[0]->file_id);
    }

    public function testCodeExecutionToolResultErrorCode(): void
    {
        $this->assertEquals('timeout', \ClaudePhp\Types\CodeExecutionToolResultErrorCode::TIMEOUT);
        $this->assertEquals('execution_error', \ClaudePhp\Types\CodeExecutionToolResultErrorCode::EXECUTION_ERROR);
        $this->assertEquals('internal_error', \ClaudePhp\Types\CodeExecutionToolResultErrorCode::INTERNAL_ERROR);
    }

    public function testCodeExecutionToolSentInApiRequest(): void
    {
        $responseBody = $this->createMessageResponse('Code execution response');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $this->testClient->messages()->create([
            'model'      => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 2048,
            'tools'      => [
                [
                    'name' => 'code_execution',
                    'type' => 'code_execution_20250825',
                ],
            ],
            'messages'   => [
                ['role' => 'user', 'content' => 'Run some code'],
            ],
        ]);

        $lastRequest = $this->getLastRequest();
        $body        = json_decode((string) $lastRequest->getBody(), true);

        $this->assertArrayHasKey('tools', $body);
        $this->assertEquals('code_execution_20250825', $body['tools'][0]['type']);
    }

    // -------------------------------------------------------------------------
    // Memory Tool Types
    // -------------------------------------------------------------------------

    public function testMemoryTool20250818ParamProperties(): void
    {
        $tool = new \ClaudePhp\Types\MemoryTool20250818Param(
            name:             'memory',
            type:             'memory_20250818',
            allowed_callers:  ['direct'],
            input_examples:   [['command' => 'view', 'path' => '/memory/']],
        );

        $this->assertEquals('memory', $tool->name);
        $this->assertEquals('memory_20250818', $tool->type);
        $this->assertEquals(['direct'], $tool->allowed_callers);
        $this->assertCount(1, $tool->input_examples);
    }

    public function testBetaMemoryTool20250818ParamProperties(): void
    {
        $tool = new \ClaudePhp\Types\Beta\BetaMemoryTool20250818Param(
            name:   'memory',
            type:   'memory_20250818',
            strict: true,
        );

        $this->assertEquals('memory', $tool->name);
        $this->assertEquals('memory_20250818', $tool->type);
        $this->assertTrue($tool->strict);
    }

    public function testMemoryToolCommandClasses(): void
    {
        $create     = new \ClaudePhp\Types\Beta\BetaMemoryTool20250818CreateCommand(
            path:      '/memory/test.md',
            file_text: '# Test',
        );
        $view       = new \ClaudePhp\Types\Beta\BetaMemoryTool20250818ViewCommand(
            path:       '/memory/',
            view_range: [1, 10],
        );
        $strReplace = new \ClaudePhp\Types\Beta\BetaMemoryTool20250818StrReplaceCommand(
            path:    '/memory/test.md',
            old_str: '# Test',
            new_str: '# Updated',
        );
        $insert     = new \ClaudePhp\Types\Beta\BetaMemoryTool20250818InsertCommand(
            path:        '/memory/test.md',
            insert_line: 5,
            insert_text: '- New line',
        );
        $delete     = new \ClaudePhp\Types\Beta\BetaMemoryTool20250818DeleteCommand(path: '/memory/old.md');
        $rename     = new \ClaudePhp\Types\Beta\BetaMemoryTool20250818RenameCommand(
            old_path: '/memory/old.md',
            new_path: '/memory/new.md',
        );

        $this->assertEquals('create', $create->command);
        $this->assertEquals('/memory/test.md', $create->path);
        $this->assertEquals('# Test', $create->file_text);

        $this->assertEquals('view', $view->command);
        $this->assertEquals([1, 10], $view->view_range);

        $this->assertEquals('str_replace', $strReplace->command);
        $this->assertEquals('# Updated', $strReplace->new_str);

        $this->assertEquals('insert', $insert->command);
        $this->assertEquals(5, $insert->insert_line);

        $this->assertEquals('delete', $delete->command);

        $this->assertEquals('rename', $rename->command);
        $this->assertEquals('/memory/new.md', $rename->new_path);
    }

    public function testMemoryToolSentInApiRequest(): void
    {
        $responseBody = $this->createMessageResponse('Memory response');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $this->testClient->messages()->create([
            'model'      => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 2048,
            'tools'      => [
                [
                    'name' => 'memory',
                    'type' => 'memory_20250818',
                ],
            ],
            'messages'   => [
                ['role' => 'user', 'content' => 'Remember this'],
            ],
        ]);

        $lastRequest = $this->getLastRequest();
        $body        = json_decode((string) $lastRequest->getBody(), true);

        $this->assertArrayHasKey('tools', $body);
        $this->assertEquals('memory_20250818', $body['tools'][0]['type']);
        $this->assertEquals('memory', $body['tools'][0]['name']);
    }

    // -------------------------------------------------------------------------
    // Web Fetch Tool Types
    // -------------------------------------------------------------------------

    public function testWebFetchTool20250910ParamProperties(): void
    {
        $tool = new \ClaudePhp\Types\WebFetchTool20250910Param(
            name:             'web_fetch',
            type:             'web_fetch_20250910',
            allowed_domains:  ['docs.anthropic.com'],
            blocked_domains:  ['example.com'],
            max_uses:         5,
            max_content_tokens: 10000,
        );

        $this->assertEquals('web_fetch', $tool->name);
        $this->assertEquals('web_fetch_20250910', $tool->type);
        $this->assertEquals(['docs.anthropic.com'], $tool->allowed_domains);
        $this->assertEquals(['example.com'], $tool->blocked_domains);
        $this->assertEquals(5, $tool->max_uses);
        $this->assertEquals(10000, $tool->max_content_tokens);
    }

    public function testBetaWebFetchTool20260209ParamProperties(): void
    {
        $tool = new \ClaudePhp\Types\Beta\BetaWebFetchTool20260209Param(
            name:            'web_fetch',
            type:            'web_fetch_20260209',
            allowed_callers: ['direct'],
            max_uses:        3,
        );

        $this->assertEquals('web_fetch', $tool->name);
        $this->assertEquals('web_fetch_20260209', $tool->type);
        $this->assertEquals(['direct'], $tool->allowed_callers);
        $this->assertEquals(3, $tool->max_uses);
    }

    public function testWebFetchBlockProperties(): void
    {
        $block = new \ClaudePhp\Types\WebFetchBlock(
            content:      ['type' => 'document', 'source' => []],
            url:          'https://example.com',
            retrieved_at: '2026-02-18T12:00:00Z',
        );

        $this->assertEquals('web_fetch_result', $block->type);
        $this->assertEquals('https://example.com', $block->url);
        $this->assertEquals('2026-02-18T12:00:00Z', $block->retrieved_at);
    }

    public function testWebFetchToolResultErrorCodeConstants(): void
    {
        $this->assertEquals('invalid_tool_input', \ClaudePhp\Types\WebFetchToolResultErrorCode::INVALID_TOOL_INPUT);
        $this->assertEquals('url_too_long', \ClaudePhp\Types\WebFetchToolResultErrorCode::URL_TOO_LONG);
        $this->assertEquals('url_not_allowed', \ClaudePhp\Types\WebFetchToolResultErrorCode::URL_NOT_ALLOWED);
        $this->assertEquals('url_not_accessible', \ClaudePhp\Types\WebFetchToolResultErrorCode::URL_NOT_ACCESSIBLE);
        $this->assertEquals('max_uses_exceeded', \ClaudePhp\Types\WebFetchToolResultErrorCode::MAX_USES_EXCEEDED);
        $this->assertEquals('unavailable', \ClaudePhp\Types\WebFetchToolResultErrorCode::UNAVAILABLE);
    }

    public function testWebFetchToolSentInApiRequest(): void
    {
        $responseBody = $this->createMessageResponse('Fetched content');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $this->testClient->messages()->create([
            'model'      => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'tools'      => [
                [
                    'name'           => 'web_fetch',
                    'type'           => 'web_fetch_20250910',
                    'max_uses'       => 3,
                ],
            ],
            'messages'   => [
                ['role' => 'user', 'content' => 'Fetch https://example.com'],
            ],
        ]);

        $lastRequest = $this->getLastRequest();
        $body        = json_decode((string) $lastRequest->getBody(), true);

        $this->assertEquals('web_fetch_20250910', $body['tools'][0]['type']);
        $this->assertEquals(3, $body['tools'][0]['max_uses']);
    }

    // -------------------------------------------------------------------------
    // Beta Web Search v2
    // -------------------------------------------------------------------------

    public function testBetaWebSearchTool20260209ParamProperties(): void
    {
        $tool = new \ClaudePhp\Types\Beta\BetaWebSearchTool20260209Param(
            name:             'web_search',
            type:             'web_search_20260209',
            allowed_callers:  ['direct'],
            allowed_domains:  ['news.ycombinator.com'],
            max_uses:         10,
        );

        $this->assertEquals('web_search', $tool->name);
        $this->assertEquals('web_search_20260209', $tool->type);
        $this->assertEquals(['direct'], $tool->allowed_callers);
        $this->assertEquals(['news.ycombinator.com'], $tool->allowed_domains);
        $this->assertEquals(10, $tool->max_uses);
    }

    public function testBetaWebSearchV2SentInBetaApiRequest(): void
    {
        $responseBody = $this->createMessageResponse('Search result');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $this->testClient->beta()->messages()->create([
            'model'      => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'tools'      => [
                [
                    'name' => 'web_search',
                    'type' => 'web_search_20260209',
                ],
            ],
            'messages'   => [
                ['role' => 'user', 'content' => 'Search for PHP 8.3'],
            ],
        ]);

        $lastRequest = $this->getLastRequest();
        $body        = json_decode((string) $lastRequest->getBody(), true);

        $this->assertEquals('web_search_20260209', $body['tools'][0]['type']);
    }

    // -------------------------------------------------------------------------
    // Beta Code Execution result types
    // -------------------------------------------------------------------------

    public function testBetaCodeExecutionResultBlockProperties(): void
    {
        $output = new \ClaudePhp\Types\Beta\BetaCodeExecutionOutputBlock(file_id: 'beta_file_xyz');
        $result = new \ClaudePhp\Types\Beta\BetaCodeExecutionResultBlock(
            content:     [$output],
            return_code: 0,
            stderr:      '',
            stdout:      'beta output',
        );

        $this->assertEquals('code_execution_result', $result->type);
        $this->assertEquals('beta output', $result->stdout);
        $this->assertCount(1, $result->content);
    }

    // -------------------------------------------------------------------------
    // Response with code_execution_tool_result block
    // -------------------------------------------------------------------------

    public function testResponseWithCodeExecutionToolResultBlock(): void
    {
        $response = json_encode([
            'id'          => 'msg_test_code_exec',
            'type'        => 'message',
            'role'        => 'assistant',
            'content'     => [
                [
                    'type'        => 'tool_use',
                    'id'          => 'toolu_code_001',
                    'name'        => 'code_execution',
                    'input'       => ['code' => 'print("hello")'],
                ],
            ],
            'model'       => 'claude-sonnet-4-5-20250929',
            'stop_reason' => 'tool_use',
            'usage'       => [
                'input_tokens'  => 80,
                'output_tokens' => 30,
            ],
        ]);

        $this->addMockResponse(200, [], $response);

        $message = $this->testClient->messages()->create([
            'model'      => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 4096,
            'tools'      => [['name' => 'code_execution', 'type' => 'code_execution_20250825']],
            'messages'   => [['role' => 'user', 'content' => 'Run code']],
        ]);

        $this->assertEquals('tool_use', $message->stop_reason);

        $toolUseFound = false;
        foreach ($message->content as $block) {
            if (($block['type'] ?? '') === 'tool_use' && $block['name'] === 'code_execution') {
                $toolUseFound = true;
            }
        }

        $this->assertTrue($toolUseFound, 'code_execution tool_use block should be present');
    }

    // -------------------------------------------------------------------------
    // Combined features: adaptive thinking + speed + output_config
    // -------------------------------------------------------------------------

    public function testAllV060ParamsCombined(): void
    {
        $responseBody = $this->createMessageResponse('Combined params');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $this->testClient->beta()->messages()->create([
            'model'         => 'claude-opus-4-6',
            'max_tokens'    => 4096,
            'thinking'      => ['type' => 'adaptive'],
            'speed'         => 'fast',
            'output_config' => ['effort' => 'high'],
            'messages'      => [['role' => 'user', 'content' => 'All new features']],
        ]);

        $lastRequest = $this->getLastRequest();
        $body        = json_decode((string) $lastRequest->getBody(), true);

        $this->assertEquals('adaptive', $body['thinking']['type']);
        $this->assertEquals('fast', $body['speed']);
        $this->assertEquals('high', $body['output_config']['effort']);
    }
}
