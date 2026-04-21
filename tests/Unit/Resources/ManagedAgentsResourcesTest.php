<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Resources;

use ClaudePhp\ClaudePhp;
use ClaudePhp\Resources\Beta\Agents\Agents;
use ClaudePhp\Resources\Beta\Agents\Versions as AgentVersions;
use ClaudePhp\Resources\Beta\Beta;
use ClaudePhp\Resources\Beta\Environments;
use ClaudePhp\Resources\Beta\Sessions\Events as SessionEventsResource;
use ClaudePhp\Resources\Beta\Sessions\Resources as SessionResources;
use ClaudePhp\Resources\Beta\Sessions\Sessions;
use ClaudePhp\Resources\Beta\UserProfiles;
use ClaudePhp\Resources\Beta\Vaults\Credentials as VaultCredentials;
use ClaudePhp\Resources\Beta\Vaults\Vaults;
use PHPUnit\Framework\TestCase;

class ManagedAgentsResourcesTest extends TestCase
{
    public function testBetaExposesAllManagedAgentsResources(): void
    {
        $client = new ClaudePhp(apiKey: 'sk-ant-test');
        $beta = $client->beta();

        $this->assertInstanceOf(Beta::class, $beta);
        $this->assertInstanceOf(Agents::class, $beta->agents());
        $this->assertInstanceOf(Sessions::class, $beta->sessions());
        $this->assertInstanceOf(Vaults::class, $beta->vaults());
        $this->assertInstanceOf(Environments::class, $beta->environments());
        $this->assertInstanceOf(UserProfiles::class, $beta->userProfiles());
    }

    public function testNestedResources(): void
    {
        $client = new ClaudePhp(apiKey: 'sk-ant-test');

        $this->assertInstanceOf(AgentVersions::class, $client->beta()->agents()->versions());
        $this->assertInstanceOf(SessionEventsResource::class, $client->beta()->sessions()->events());
        $this->assertInstanceOf(SessionResources::class, $client->beta()->sessions()->resources());
        $this->assertInstanceOf(VaultCredentials::class, $client->beta()->vaults()->credentials());
    }

    public function testBetaHeaderConstantIsCorrect(): void
    {
        $reflection = new \ReflectionClass(Agents::class);
        $source = file_get_contents($reflection->getFileName());
        $this->assertStringContainsString('managed-agents-2026-04-01', $source);
    }
}
