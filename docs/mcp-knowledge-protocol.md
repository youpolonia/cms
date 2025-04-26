# MCP Knowledge Server Protocol

## Overview
Standardized protocol for interacting with MCP knowledge servers across all agents.

## Verification Process
1. All agents must verify MCP connectivity at task start using:
```xml
<use_mcp_tool>
<server_name>cms-knowledge-server</server_name>
<tool_name>get_cached_file</tool_name>
<arguments>{"path":"app/Models/ApprovalWorkflow.php"}</arguments>
</use_mcp_tool>
```

2. Expected response: Successful retrieval of cached file content

## Persistent Storage Requirements
- Cache verification results in agent memory
- Log all MCP interactions for audit purposes
- Maintain connection state between tasks

## Protocol Propagation
- Include this protocol documentation in all task handoffs
- Verify protocol version compatibility between agents
- Update documentation when protocol changes

## New Server Integration
1. Add server details to protocol documentation
2. Implement verification command
3. Update tool usage patterns as needed
4. Propagate changes to all agents

## Standardized Tool Usage
- Always verify server connectivity first
- Cache frequently accessed files when possible
- Implement error handling for server unavailability
- Document all custom tools/resources provided by servers

## Batch Caching Tool

The `cache_files_batch` tool allows bulk caching of multiple files in a single operation:

```xml
<use_mcp_tool>
<server_name>cms-knowledge-server</server_name>
<tool_name>cache_files_batch</tool_name>
<arguments>
{
  "files": [
    {"key": "path/to/file1", "value": "file content 1"},
    {"key": "path/to/file2", "value": "file content 2"}
  ]
}
</arguments>
</use_mcp_tool>
```

**Parameters:**
- `files`: Array of objects with `key` (file path) and `value` (file content)

**Returns:** Array of results for each file operation

**Performance Notes:**
- More efficient than individual cache operations
- Recommended for batches of 10+ files
- Automatically validates file objects before processing

**Error Handling:**
- Throws InvalidArgumentException for malformed input
- Throws RuntimeException for processing failures
- Logs detailed error information

## Version History
- 1.1.0 (2025-04-22): Added batch caching support
- 1.0.0 (2025-04-15): Initial protocol implementation
