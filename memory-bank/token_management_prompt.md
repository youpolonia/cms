# Token Management Agent Prompt (v1.0)

## Core Principles
1. **Proactive Monitoring**: Continuously track token usage at 50%, 75%, and 90% thresholds
2. **Preventive Actions**: Implement safeguards before reaching critical limits
3. **Context Preservation**: Save state at logical checkpoints using UMB protocol
4. **Efficient Communication**: Use technical language without conversational fluff

## Required Agent Behaviors
- **At 50% token usage**:
  - Begin summarizing non-critical context
  - Activate lightweight processing mode
  - Log current state to memory-bank/progress.md

- **At 75% token usage**:
  - Force checkpoint with UMB
  - Split responses into chunks
  - Switch to fallback model if available
  - Log warning to memory-bank/quota_warnings.md

- **At 90% token usage**:
  - Immediate emergency state activation
  - Pause all non-critical operations
  - Execute POST to /api/system/emergency-endpoint
  - Log critical state to memory-bank/quota_errors.md

## Optimization Techniques
1. **File Operations**:
   - Always specify line ranges in read_file
   - Use precise regex patterns in search_files
   - Compress diagrams and documentation

2. **Task Management**:
   - Break complex tasks into subtasks < 50 tokens each
   - Process one subtask per message
   - Verify completion before proceeding

3. **Memory Bank Usage**:
   - Update productContext.md after each logical unit
   - Append single-line entries to progress.md
   - Document decisions in decisionLog.md

## Emergency Recovery
1. If interrupted:
   - Restore from last UMB checkpoint
   - Verify system state
   - Continue from memory-bank/progress.md

2. If quota exceeded:
   - Wait 5 minutes before retrying
   - Reduce request rate by 75%
   - Use fallback configuration

**Note**: This prompt complements existing token rules in .roo/rules/gemini_token_rules.md and .roo/rules/rules.md - DO NOT overwrite those files.