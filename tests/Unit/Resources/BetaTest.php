<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Resources\Beta;

use ClaudePhp\Tests\TestCase;
use ClaudePhp\Resources\Beta\Beta;
use ClaudePhp\Resources\Beta\Files;
use ClaudePhp\Resources\Beta\Messages;
use ClaudePhp\Resources\Beta\Models;
use ClaudePhp\Resources\Beta\Skills;
use ClaudePhp\ClaudePhp;

class BetaTest extends TestCase
{
    private Beta $beta;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new ClaudePhp(apiKey: 'test-key');
        $this->beta = new Beta($client);
    }

    public function test_can_instantiate_beta_resource(): void
    {
        $this->assertInstanceOf(Beta::class, $this->beta);
    }

    public function test_files_returns_files_resource(): void
    {
        $files = $this->beta->files();
        $this->assertInstanceOf(Files::class, $files);
    }

    public function test_messages_returns_messages_resource(): void
    {
        $messages = $this->beta->messages();
        $this->assertInstanceOf(Messages::class, $messages);
    }

    public function test_models_returns_models_resource(): void
    {
        $models = $this->beta->models();
        $this->assertInstanceOf(Models::class, $models);
    }

    public function test_skills_returns_skills_resource(): void
    {
        $skills = $this->beta->skills();
        $this->assertInstanceOf(Skills::class, $skills);
    }
}

class BetaFilesTest extends TestCase
{
    private Files $files;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new ClaudePhp(apiKey: 'test-key');
        $this->files = new Files($client);
    }

    public function test_upload_validates_file_parameter(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('file parameter is required');

        $this->files->upload([]);
    }

    public function test_retrieve_validates_file_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('file_id is required');

        $this->files->retrieve('');
    }

    public function test_content_validates_file_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('file_id is required');

        $this->files->content('');
    }

    public function test_delete_validates_file_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('file_id is required');

        $this->files->delete('');
    }
}

class BetaMessagesTest extends TestCase
{
    private Messages $messages;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertNotNull($this->testClient);
        $this->messages = $this->testClient->beta()->messages();
    }

    public function test_create_validates_required_parameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->messages->create([]);
    }

    public function test_batches_returns_batches_resource(): void
    {
        $batches = $this->messages->batches();
        $this->assertInstanceOf(\ClaudePhp\Resources\Beta\Batches::class, $batches);
    }

    public function test_parse_requires_output_format(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->messages->parse([
            'model' => 'claude-sonnet',
            'max_tokens' => 100,
            'messages' => [],
        ]);
    }

    public function test_parse_returns_structured_data(): void
    {
        $schema = [
            'type' => 'object',
            'properties' => ['order_id' => ['type' => 'string']],
            'required' => ['order_id'],
        ];

        $this->addMockResponse(200, [], json_encode([
            'id' => 'msg_test',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                ['type' => 'text', 'text' => '{"order_id":"123"}'],
            ],
            'model' => 'claude-sonnet',
            'usage' => ['input_tokens' => 1, 'output_tokens' => 1],
        ]));

        $result = $this->messages->parse([
            'model' => 'claude-sonnet',
            'max_tokens' => 100,
            'messages' => [],
            'output_format' => $schema,
        ]);

        $this->assertSame(['order_id' => '123'], $result);
    }

    public function test_stream_structured_returns_wrapped_stream(): void
    {
        $schema = [
            'type' => 'object',
            'properties' => ['answer' => ['type' => 'string']],
            'required' => ['answer'],
        ];

        $streamData = "";
        $streamData .= $this->createStreamingEvent('message_start', [
            'type' => 'message_start',
            'message' => [
                'id' => 'msg_beta',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [],
                'model' => 'claude',
                'usage' => ['input_tokens' => 0],
            ],
        ]);
        $streamData .= $this->createStreamingEvent('content_block_start', [
            'type' => 'content_block_start',
            'index' => 0,
            'content_block' => ['type' => 'text', 'text' => ''],
        ]);
        $streamData .= $this->createStreamingEvent('content_block_delta', [
            'type' => 'content_block_delta',
            'index' => 0,
            'delta' => ['type' => 'text_delta', 'text' => '{"answer": "Hi"}'],
        ]);
        $streamData .= "event: message_stop\ndata: [DONE]\n\n";

        $this->addMockResponse(200, ['Content-Type' => 'text/event-stream'], $streamData);

        $stream = $this->messages->streamStructured([
            'model' => 'claude-sonnet',
            'max_tokens' => 100,
            'messages' => [],
            'output_format' => $schema,
        ]);

        $events = iterator_to_array($stream);
        $this->assertSame(['answer' => 'Hi'], $events[1]['parsed_output']);
    }
}

class BetaModelsTest extends TestCase
{
    private Models $models;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new ClaudePhp(apiKey: 'test-key');
        $this->models = new Models($client);
    }

    public function test_retrieve_validates_model_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('model_id is required');

        $this->models->retrieve('');
    }
}

class BetaSkillsTest extends TestCase
{
    private Skills $skills;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new ClaudePhp(apiKey: 'test-key');
        $this->skills = new Skills($client);
    }

    public function test_create_validates_required_parameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->skills->create([]);
    }

    public function test_retrieve_validates_skill_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('skill_id is required');

        $this->skills->retrieve('');
    }

    public function test_delete_validates_skill_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('skill_id is required');

        $this->skills->delete('');
    }

    public function test_versions_returns_versions_resource(): void
    {
        $versions = $this->skills->versions();
        $this->assertInstanceOf(\ClaudePhp\Resources\Beta\Versions::class, $versions);
    }
}

class BetaVersionsTest extends TestCase
{
    private \ClaudePhp\Resources\Beta\Versions $versions;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new ClaudePhp(apiKey: 'test-key');
        $this->versions = new \ClaudePhp\Resources\Beta\Versions($client);
    }

    public function test_create_validates_skill_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->versions->create([]);
    }

    public function test_list_validates_skill_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('skill_id is required');

        $this->versions->list('');
    }

    public function test_retrieve_validates_skill_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('skill_id and version_id are required');

        $this->versions->retrieve('', 'v1');
    }

    public function test_retrieve_validates_version_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('skill_id and version_id are required');

        $this->versions->retrieve('skill-1', '');
    }

    public function test_delete_validates_ids(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('skill_id and version_id are required');

        $this->versions->delete('', '');
    }
}
