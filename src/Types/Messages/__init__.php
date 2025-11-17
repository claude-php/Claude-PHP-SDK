<?php

declare(strict_types=1);

// Message batch types
require_once __DIR__ . '/MessageBatch.php';
require_once __DIR__ . '/MessageBatchRequestCounts.php';
require_once __DIR__ . '/MessageBatchIndividualResponse.php';
require_once __DIR__ . '/MessageBatchResult.php';
require_once __DIR__ . '/MessageBatchSucceededResult.php';
require_once __DIR__ . '/MessageBatchErroredResult.php';
require_once __DIR__ . '/MessageBatchCanceledResult.php';
require_once __DIR__ . '/MessageBatchExpiredResult.php';
require_once __DIR__ . '/DeletedMessageBatch.php';
require_once __DIR__ . '/BatchCreateParams.php';
require_once __DIR__ . '/BatchListParams.php';