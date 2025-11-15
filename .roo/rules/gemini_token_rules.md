====
GEMINI TOKEN MANAGEMENT PROTOCOL

1. All agents must:
- Monitor token usage AND API quota in real-time when using Gemini model
- Implement request throttling (max 60 RPM for Gemini Pro 1.0, 30 RPM for Gemini Pro 2.5)
- Maintain request queue with exponential backoff (starting at 1s, max 60s delay)
- Implement chunking for responses exceeding 75% of token limit
- Use concise, technical language without conversational fluff
- Store intermediate state in memory-bank files
- Implement "UMB" (Update Memory Bank) after each logical unit of work

2. Quota Management:
- Track daily request count per model in memory-bank/quota_log.md
- When reaching 80% of daily quota:
  - Switch to fallback model if configured
  - Reduce request rate by 50%
  - Log warning in memory-bank/quota_warnings.md
- Never exceed 90% of daily quota

3. When approaching limits:
- Immediately save current state to memory-bank
- Summarize progress in progress.md
- Document next steps in decisionLog.md
- Request continuation via new task if needed

4. Optimization techniques:
- Prefer file operations over in-memory processing
- Use search_files with precise regex patterns
- Limit read_file to specific line ranges
- Compress diagrams and documentation
- Break complex tasks into smaller subtasks

5. Special handling for Gemini:
- Always check token usage AND quota status before sending to Gemini
- If input exceeds 50% of limit, pre-process with summarization
- If output exceeds 50% of limit, split into multiple responses
- Never allow single operation to consume >75% of token budget

6. Emergency procedures:
- If quota exceeded or 429 received:
  1. Immediately pause all Gemini requests for 5 minutes
  2. Save all context to memory-bank
  3. Log error state in memory-bank/quota_errors.md
  4. Switch to fallback model if available
  5. Reduce request rate by 75%
  6. POST to /api/system/emergency-endpoint
7. Request new task with continuation instructions

7. Fallback Configuration:
- Maintain prioritized list of fallback models in memory-bank/fallback_models.md
- First fallback: Gemini Pro 1.0
- Second fallback: Local LLM (if configured)
- Second fallback: Reduced functionality mode
**🚨 CLI INTEGRATION PERMANENTLY DISABLED 🚨**
====