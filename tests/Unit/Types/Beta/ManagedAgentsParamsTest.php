<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Types\Beta;

use ClaudePhp\Types\Beta\ManagedAgents\Params\AgentCreateParams;
use ClaudePhp\Types\Beta\ManagedAgents\Params\SessionCreateParams;
use ClaudePhp\Types\Beta\ManagedAgents\Params\VaultCreateParams;
use PHPUnit\Framework\TestCase;

class ManagedAgentsParamsTest extends TestCase
{
    public function testAgentCreateParamsRequiredFields(): void
    {
        $params = new AgentCreateParams(
            model: 'claude-sonnet-4-6',
            name: 'test agent',
        );

        $arr = $params->toArray();
        $this->assertSame('claude-sonnet-4-6', $arr['model']);
        $this->assertSame('test agent', $arr['name']);
        $this->assertArrayNotHasKey('description', $arr);
    }

    public function testAgentCreateParamsOptionalFields(): void
    {
        $params = new AgentCreateParams(
            model: 'claude-opus-4-7',
            name: 'agent',
            description: 'A test',
            metadata: ['env' => 'prod'],
            system: 'Be helpful.',
        );

        $arr = $params->toArray();
        $this->assertSame('A test', $arr['description']);
        $this->assertSame(['env' => 'prod'], $arr['metadata']);
        $this->assertSame('Be helpful.', $arr['system']);
    }

    public function testSessionCreateParams(): void
    {
        $params = new SessionCreateParams(
            agent: 'agent_123',
            environment_id: 'env_456',
            title: 'My session',
            vault_ids: ['v_1', 'v_2'],
        );

        $arr = $params->toArray();
        $this->assertSame('agent_123', $arr['agent']);
        $this->assertSame('env_456', $arr['environment_id']);
        $this->assertSame('My session', $arr['title']);
        $this->assertSame(['v_1', 'v_2'], $arr['vault_ids']);
    }

    public function testVaultCreateParams(): void
    {
        $params = new VaultCreateParams(display_name: 'Production Vault');
        $arr = $params->toArray();
        $this->assertSame('Production Vault', $arr['display_name']);
        $this->assertArrayNotHasKey('metadata', $arr);
    }
}
