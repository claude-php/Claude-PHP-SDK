# JSONL Decoder Implementation

## Overview

The JSONL (JSON Lines) decoder implementation provides streaming support for parsing newline-delimited JSON responses, which is essential for the Batch API results endpoint.

## Files

- `src/Responses/Decoders/JSONLDecoder.php` - Synchronous JSONL decoder implementing the PHP `Iterator` interface
- `src/Responses/Decoders/AsyncJSONLDecoder.php` - Asynchronous JSONL decoder for async contexts
- `tests/Unit/Responses/Decoders/JSONLDecoderTest.php` - Unit tests for synchronous decoder
- `tests/Unit/Responses/Decoders/AsyncJSONLDecoderTest.php` - Unit tests for asynchronous decoder

## Usage

### Synchronous Decoder

```php
use ClaudePhp\Responses\Decoders\JSONLDecoder;

// Create a decoder with a byte stream iterator
$decoder = new JSONLDecoder(
    rawIterator: $streamIterator,
    lineType: 'array', // or class name like Message::class
    response: $httpResponse
);

// Use as a standard PHP iterator
foreach ($decoder as $item) {
    echo $item['id'];
}

// Or convert to array
$items = iterator_to_array($decoder);
```

### Asynchronous Decoder

```php
use ClaudePhp\Responses\Decoders\AsyncJSONLDecoder;

// Create an async decoder
$decoder = new AsyncJSONLDecoder(
    rawIterator: $asyncIterator,
    lineType: 'array',
    response: $httpResponse
);

// Use with generators (works in both sync and async contexts)
foreach ($decoder->decode() as $item) {
    echo $item['id'];
}
```

## Implementation Details

### Line Ending Handling

The decoders handle all standard line ending styles:
- `\r\n` (Windows)
- `\n` (Unix/Linux)
- `\r` (Mac - legacy)

Detection prioritizes `\r\n` to avoid treating it as two separate lines.

### Chunked Streaming

The decoders buffer incoming data and only emit complete lines. This handles scenarios where JSON data arrives in arbitrary-sized chunks from the HTTP stream.

Example:
```php
// Data arrives in chunks:
// Chunk 1: '{"id": "msg_1"}'
// Chunk 2: '\n{"id":'
// Chunk 3: ' "msg_2"}\n'

// Decoder correctly parses both lines despite chunking
```

### Empty Line Skipping

Empty lines (after trimming whitespace) are automatically skipped, allowing for gracefully handling extra newlines in the JSONL stream.

### JSON Parsing

Each line is parsed using `json_decode()` with `JSON_THROW_ON_ERROR` flag, which raises `\JsonException` on invalid JSON.

### Type Deserialization

The decoder can deserialize JSON into typed objects:
```php
// Deserialize to array (default)
$decoder = new JSONLDecoder($iterator, 'array', $response);

// Deserialize to typed object (requires class constructor to accept array)
$decoder = new JSONLDecoder($iterator, Message::class, $response);
```

### Stream Lifecycle

Both decoders provide a `close()` method to explicitly close the underlying HTTP response stream. This is called automatically by `AsyncJSONLDecoder::decode()` when finished.

## Batch API Integration

The primary use case is retrieving batch results:

```php
// Get batch results as JSONL stream
$response = $client->messages->batches->results($batchId);

// Decode results
$decoder = new JSONLDecoder(
    rawIterator: $response->getBody(),
    lineType: 'array',
    response: $response
);

foreach ($decoder as $result) {
    echo "Result ID: " . $result['custom_id'] . "\n";
    // Handle each batch result
}
```

## Testing

Both decoders include comprehensive test coverage:

```bash
# Run decoder tests
php vendor/bin/phpunit tests/Unit/Responses/Decoders/

# Run full test suite
php vendor/bin/phpunit tests/
```

Test scenarios cover:
- Single and multiple lines
- Different line ending styles
- Chunked data arrival
- Empty lines
- Batch result format
- Iterator interface compliance
- Invalid JSON error handling

## Performance Considerations

1. **Memory Efficient**: Uses generators and streaming, avoiding loading entire responses into memory
2. **Line Buffering**: Only stores incomplete lines in memory, not entire stream
3. **Lazy Evaluation**: Lines are parsed only when accessed, not upfront
4. **No Array Reallocation**: Uses single-pass iteration with numeric key tracking

## Future Enhancements

- Support for custom JSON deserializers
- Configurable error handling strategies (skip, throw, callback)
- Metrics collection (lines parsed, bytes processed)
- Compatibility with Amphp async iterator protocol
