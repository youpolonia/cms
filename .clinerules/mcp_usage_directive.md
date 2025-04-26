# MCP Server Usage Directive (Updated)

## Selective Caching Rules
1. **Cache Priority Files**:
   - Core components (app/View/Components/*)
   - Services (app/Services/*)
   - Models (app/Models/*)
   - Controllers (app/Http/Controllers/*)
   - Analytics data (app/Http/Controllers/*Analytics*)

2. **Skip Caching For**:
   - View templates (resources/views/*)
   - Temporary files (storage/tmp/*, *.tmp)
   - Files larger than 5MB
   - Test files (tests/*)

3. **Smart Caching Behavior**:
   - Only cache files that are accessed multiple times in a session
   - Implement LRU cache eviction after 100 items
   - Cache duration: 
     - 1 hour for most files
     - 24 hours for core components
     - Configurable TTL for analytics data (see config/cache.php)

## Standard Workflow
```xml
<!-- Check cache first for priority files -->
<use_mcp_tool>
<server_name>cms-knowledge-server</server_name>
<tool_name>get_cached_file</tool_name>
<arguments>{"path":"file_path_here"}</arguments>
</use_mcp_tool>

<!-- For non-priority files, read directly -->
<read_file>
<path>file_path_here</path>
</read_file>

<!-- Cache only if meets criteria -->
<use_mcp_tool>
<server_name>cms-knowledge-server</server_name>
<tool_name>cache_file</tool_name>
<arguments>{"path":"file_path_here"}</arguments>
</use_mcp_tool>
```

## Performance Tracking
- Log cache hit/miss ratios
- Track most frequently accessed files
- Monitor cache size and eviction rates

## Enforcement
- New rules enforced immediately
- System will log caching decisions
- Performance metrics will track optimized cache usage
