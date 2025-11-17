<?php

declare(strict_types=1);

/**
 * Index of all resource classes.
 *
 * This file provides a central registry and documentation for all available
 * resources in the Claude PHP SDK.
 */

namespace ClaudePhp\Resources;

// Core Resources
use ClaudePhp\Resources\Messages\Messages;
use ClaudePhp\Resources\Messages\Batches as MessagesBatches;
use ClaudePhp\Resources\Models;
use ClaudePhp\Resources\Completions;

// Beta Resources
use ClaudePhp\Resources\Beta\Beta;
use ClaudePhp\Resources\Beta\Files;
use ClaudePhp\Resources\Beta\Messages as BetaMessages;
use ClaudePhp\Resources\Beta\Batches as BetaBatches;
use ClaudePhp\Resources\Beta\Models as BetaModels;
use ClaudePhp\Resources\Beta\Skills\Skills;
use ClaudePhp\Resources\Beta\Skills\Versions;

/**
 * Array of all available resource classes for registry/discovery.
 *
 * @return array<string, string> Resource name => fully qualified class name
 */
function getAllResources(): array
{
    return [
        // Core Resources
        'messages' => Messages::class,
        'messages.batches' => MessagesBatches::class,
        'models' => Models::class,
        'completions' => Completions::class,

        // Beta Resources
        'beta' => Beta::class,
        'beta.files' => Files::class,
        'beta.messages' => BetaMessages::class,
        'beta.messages.batches' => BetaBatches::class,
        'beta.models' => BetaModels::class,
        'beta.skills' => Skills::class,
        'beta.skills.versions' => Versions::class,
    ];
}
