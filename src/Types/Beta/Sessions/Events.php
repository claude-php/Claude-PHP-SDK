<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\Sessions;

/**
 * Session event type constants.
 *
 * Mirrors Python's discriminated union of session event types.
 * Each constant matches the `type` discriminator from the API.
 */
final class Events
{
    // Agent events
    public const AGENT_MESSAGE = 'agent_message';
    public const AGENT_THINKING = 'agent_thinking';
    public const AGENT_TOOL_USE = 'agent_tool_use';
    public const AGENT_TOOL_RESULT = 'agent_tool_result';
    public const AGENT_CUSTOM_TOOL_USE = 'agent_custom_tool_use';
    public const AGENT_MCP_TOOL_USE = 'agent_mcp_tool_use';
    public const AGENT_MCP_TOOL_RESULT = 'agent_mcp_tool_result';
    public const AGENT_THREAD_CONTEXT_COMPACTED = 'agent_thread_context_compacted';

    // User events
    public const USER_MESSAGE = 'user_message';
    public const USER_INTERRUPT = 'user_interrupt';
    public const USER_CUSTOM_TOOL_RESULT = 'user_custom_tool_result';
    public const USER_TOOL_CONFIRMATION = 'user_tool_confirmation';

    // Session lifecycle events
    public const SESSION_DELETED = 'session_deleted';
    public const SESSION_END_TURN = 'session_end_turn';
    public const SESSION_ERROR = 'session_error';
    public const SESSION_REQUIRES_ACTION = 'session_requires_action';
    public const SESSION_RETRIES_EXHAUSTED = 'session_retries_exhausted';
    public const SESSION_STATUS_IDLE = 'session_status_idle';
    public const SESSION_STATUS_RESCHEDULED = 'session_status_rescheduled';
    public const SESSION_STATUS_RUNNING = 'session_status_running';
    public const SESSION_STATUS_TERMINATED = 'session_status_terminated';

    // Span events
    public const SPAN_MODEL_REQUEST_START = 'span_model_request_start';
    public const SPAN_MODEL_REQUEST_END = 'span_model_request_end';

    // Errors
    public const BILLING_ERROR = 'billing_error';
    public const MCP_AUTHENTICATION_FAILED = 'mcp_authentication_failed';
    public const MCP_CONNECTION_FAILED = 'mcp_connection_failed';
    public const MODEL_OVERLOADED = 'model_overloaded';
    public const MODEL_RATE_LIMITED = 'model_rate_limited';
    public const MODEL_REQUEST_FAILED = 'model_request_failed';
    public const UNKNOWN_ERROR = 'unknown_error';
}
