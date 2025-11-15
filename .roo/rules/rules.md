====
TOKEN MANAGEMENT PROTOCOL

1. All agents must:
- Monitor token usage in real-time
- Split large tasks into smaller chunks when approaching 75% of limit
- Store intermediate state in memory-bank files
- Use concise, technical language without conversational fluff
- Implement "UMB" (Update Memory Bank) after each logical unit of work

2. When approaching limits:
- Immediately save current state to memory-bank
- Summarize progress in progress.md
- Document next steps in decisionLog.md
- Request continuation via new task if needed

3. Optimization techniques:
- Prefer file operations over in-memory processing
- Use search_files with precise regex patterns
- Limit read_file to specific line ranges
- Compress diagrams and documentation

====