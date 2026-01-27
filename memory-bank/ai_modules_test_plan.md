# AI Modules Manual Test Plan

## 1. Introduction

This document provides a comprehensive manual test plan for all AI-related modules and endpoints in the CMS. It covers frontend admin interfaces, backend core services, logging infrastructure, and configuration management. The plan assumes DEV_MODE is enabled and at least one AI provider (OpenAI, HuggingFace, or local) is properly configured via the AI Settings interface.

All tests are designed to be executed manually by QA personnel or developers with admin access. Each test case specifies clear steps and expected outcomes to ensure deterministic, repeatable validation.

## 2. Global Pre-requisites

Before executing any test cases in this plan, ensure the following conditions are met:

- **DEV_MODE Enabled**: Set `define('DEV_MODE', true);` in `config.php` to access admin panels.
- **Admin Account**: Valid admin credentials with full access to AI Tools and Settings.
- **AI Provider Configured**: At least one AI provider (OpenAI, HuggingFace, or local endpoint) configured via `/admin/ai-settings.php` with:
  - Valid API key or endpoint URL
  - Selected model appropriate for the provider
  - Timeout and rate limit settings
- **Browser Requirements**: Modern browser with JavaScript enabled, cookies allowed for session management.
- **Network Access**: If using cloud AI providers, ensure outbound HTTPS connectivity to provider APIs.
- **Log Directory Writable**: Confirm `logs/` directory exists and is writable by the web server user.
- **Config Directory Writable**: Confirm `config/` directory is writable for `ai_settings.json` persistence.

**Important**: Never use production API keys or real customer data during testing. Use dedicated test/sandbox keys where available.

## 3. Test Cases per Module

### 3.1 AI Content Creator (/admin/ai-content-creator.php)

**Purpose**: Generate complete articles in multiple languages (Polish and English) with customizable parameters like tone, length, and target audience. This module provides a standalone content generation interface.

**TC-CREATOR-01: Generate Article in Polish**
Steps:
1. Navigate to `/admin/ai-content-creator.php`.
2. Enter a topic in the "Topic" field (e.g., "Sztuczna inteligencja w medycynie").
3. Select language: "Polski".
4. Choose tone: "Professional".
5. Set length: "Medium".
6. Click "Generate Content".
7. Wait for response (up to 30 seconds).

Expected result:
- Progress indicator or loading state appears during generation.
- Article content displays in Polish with appropriate formatting.
- Content includes title, introduction, body paragraphs, and conclusion.
- No PHP errors or warnings visible in UI or browser console.

**TC-CREATOR-02: Generate Article in English**
Steps:
1. Navigate to `/admin/ai-content-creator.php`.
2. Enter topic: "The Future of Renewable Energy".
3. Select language: "English".
4. Choose tone: "Casual".
5. Set length: "Long".
6. Click "Generate Content".

Expected result:
- Article generated in English with casual tone.
- Content length noticeably longer than "Medium" setting.
- Formatting includes proper paragraphs and structure.
- Generated content can be copied to clipboard or saved.

**TC-CREATOR-03: Error Handling - No AI Provider**
Steps:
1. Go to `/admin/ai-settings.php` and disable all AI providers or remove API key.
2. Navigate to `/admin/ai-content-creator.php`.
3. Attempt to generate content with any parameters.

Expected result:
- User-friendly error message displayed (e.g., "AI provider not configured").
- No stack traces or internal error details exposed.
- Page remains functional, allowing user to navigate away or retry after configuration.
- Error logged to `logs/ai_requests.log` with appropriate context.

**TC-CREATOR-04: Performance with Long Content**
Steps:
1. Navigate to `/admin/ai-content-creator.php`.
2. Enter a complex topic requiring research.
3. Set length to "Long" or maximum available.
4. Click "Generate Content" and observe timeout behavior.

Expected result:
- Request completes within configured timeout (typically 30-60 seconds).
- If timeout occurs, graceful error message appears (no white screen).
- Loading indicator remains visible until response or timeout.
- Partial content (if any) is either displayed or discarded with explanation.

**TC-CREATOR-05: Special Characters in Topic**
Steps:
1. Navigate to `/admin/ai-content-creator.php`.
2. Enter topic with special characters: "AI & Machine Learning: What's Next? (2025)".
3. Generate content in any language.

Expected result:
- Special characters handled correctly in API request.
- Generated content references the topic accurately.
- No encoding issues (proper UTF-8 handling).
- Ampersands, quotes, and parentheses don't cause errors.

---

### 3.2 AI Content - Blog Draft Integration (/admin/ai-content.php)

**Purpose**: Generate article content and automatically save it as an unpublished draft in the blog/content management system, streamlining the editorial workflow.

**TC-CONTENT-01: Generate and Save Draft**
Steps:
1. Navigate to `/admin/ai-content.php`.
2. Enter article topic (e.g., "Top 10 Web Development Trends").
3. Select appropriate language and parameters.
4. Click "Generate and Save as Draft".
5. Wait for confirmation message.
6. Navigate to blog admin or content listing page.

Expected result:
- Success message confirms draft creation with draft ID or title.
- Draft appears in content listing with status "Draft" or "Unpublished".
- Draft is NOT visible on public-facing site.
- Draft content matches generated text from AI.
- Created timestamp reflects current time.

**TC-CONTENT-02: Verify Draft Persistence**
Steps:
1. Complete TC-CONTENT-01 to create a draft.
2. Note the draft ID or title.
3. Log out of admin panel.
4. Log back in.
5. Navigate to content listing and locate the draft.

Expected result:
- Draft persists across sessions.
- Content remains unchanged.
- Metadata (author, created date) accurately reflects original creation.
- Draft is editable for further refinement.

**TC-CONTENT-03: Duplicate Topic Handling**
Steps:
1. Generate a draft for topic "AI in Healthcare".
2. Immediately generate another draft for the same topic.

Expected result:
- System allows creation of multiple drafts with same/similar topics.
- Each draft receives unique ID.
- No conflicts or overwrites occur.
- Both drafts appear independently in listing.

**TC-CONTENT-04: Draft Metadata Population**
Steps:
1. Generate a draft via `/admin/ai-content.php`.
2. Open the draft for editing in content management system.
3. Inspect metadata fields (title, slug, author, created_at, etc.).

Expected result:
- Title auto-populated from AI-generated content or topic.
- Slug auto-generated from title (URL-safe format).
- Author field set to currently logged-in admin user.
- Created/updated timestamps accurate.
- No required fields left blank that would prevent publishing.

**TC-CONTENT-05: Error Recovery - Failed Save**
Steps:
1. Temporarily revoke write permissions on content table (or simulate DB error via dev tools).
2. Attempt to generate and save draft.

Expected result:
- Error message indicates save failure (not generation failure).
- Generated content is preserved in UI (not lost).
- User can copy content manually or retry save.
- No partial/corrupted draft created in database.

---

### 3.3 AI SEO Assistant (/admin/ai-seo.php)

**Purpose**: Analyze content or URLs and generate SEO-optimized meta titles, descriptions, and keyword recommendations based on AI analysis and industry best practices.

**TC-SEO-01: Generate Meta Title and Description**
Steps:
1. Navigate to `/admin/ai-seo.php`.
2. Enter a URL or paste sample content (e.g., existing blog post).
3. Click "Analyze and Generate SEO".
4. Wait for AI response.

Expected result:
- Meta title generated within 50-60 character limit (SEO best practice).
- Meta description generated within 150-160 character limit.
- Both outputs are relevant to input content.
- No generic/placeholder text like "lorem ipsum" or "example.com".

**TC-SEO-02: Keyword Extraction**
Steps:
1. Navigate to `/admin/ai-seo.php`.
2. Provide content-rich text (300+ words) about a specific topic.
3. Request SEO analysis.

Expected result:
- 5-10 relevant keywords extracted from content.
- Keywords ranked by relevance or frequency.
- Multi-word phrases included (not just single words).
- Keywords useful for search engine optimization.

**TC-SEO-03: URL Analysis with Live Fetch**
Steps:
1. Navigate to `/admin/ai-seo.php`.
2. Enter a publicly accessible URL (e.g., Wikipedia article).
3. Click "Analyze URL".

Expected result:
- System fetches content from URL (or uses cached version).
- SEO suggestions based on actual page content.
- Timeout handling if URL is slow/unresponsive (graceful degradation).
- Security: System validates URL format (no `file://` or internal IPs).

**TC-SEO-04: Content Length Constraints**
Steps:
1. Navigate to `/admin/ai-seo.php`.
2. Paste very short content (e.g., 50 words).
3. Generate SEO suggestions.
4. Repeat with very long content (e.g., 5000 words).

Expected result:
- Short content: Warning message about insufficient content for robust analysis.
- Long content: System truncates or summarizes for API efficiency.
- Both cases return usable (if limited) SEO suggestions.
- No crashes or memory errors.

**TC-SEO-05: Multi-language SEO**
Steps:
1. Navigate to `/admin/ai-seo.php`.
2. Provide content in Polish.
3. Generate SEO suggestions.

Expected result:
- Meta title and description generated in Polish (not translated to English).
- Keywords extracted in source language.
- Character limits respect UTF-8 multi-byte characters correctly.
- No encoding corruption in output.

---

### 3.4 AI Toolkit (/admin/ai-toolkit.php)

**Purpose**: Provide a suite of AI-powered text transformation tools including summarize, expand, rewrite, bullet points, and translation (English/Polish).

**TC-TOOLKIT-01: Summarize Mode**
Steps:
1. Navigate to `/admin/ai-toolkit.php`.
2. Paste a long article or blog post (500+ words).
3. Select mode: "Summarize".
4. Click "Process".

Expected result:
- Output is significantly shorter than input (20-30% of original length).
- Summary captures main points and key information.
- No critical information lost.
- Processing completes within 10-20 seconds.

**TC-TOOLKIT-02: Expand Mode**
Steps:
1. Navigate to `/admin/ai-toolkit.php`.
2. Enter brief bullet points or short paragraph (50-100 words).
3. Select mode: "Expand".
4. Click "Process".

Expected result:
- Output is 2-3x longer than input.
- Expanded content adds context, examples, or elaboration.
- Original meaning preserved and enhanced.
- No repetitive or filler text.

**TC-TOOLKIT-03: Rewrite Mode**
Steps:
1. Navigate to `/admin/ai-toolkit.php`.
2. Paste a paragraph with awkward phrasing or repetition.
3. Select mode: "Rewrite".
4. Click "Process".

Expected result:
- Output maintains original meaning but with improved phrasing.
- Grammar and readability enhanced.
- Tone consistent with input (or as specified).
- No plagiarism concerns (sufficiently transformed).

**TC-TOOLKIT-04: Bullet Points Mode**
Steps:
1. Navigate to `/admin/ai-toolkit.php`.
2. Paste a dense paragraph of text.
3. Select mode: "Bullet Points".
4. Click "Process".

Expected result:
- Output formatted as bulleted or numbered list.
- Each bullet represents a discrete idea or fact.
- 3-7 bullets generated (depending on input length).
- Bullets are concise and scannable.

**TC-TOOLKIT-05: Translate EN to PL**
Steps:
1. Navigate to `/admin/ai-toolkit.php`.
2. Enter English text (e.g., "The quick brown fox jumps over the lazy dog.").
3. Select mode: "Translate to Polish".
4. Click "Process".

Expected result:
- Output is accurate Polish translation.
- Idiomatic expressions handled appropriately (not literal word-for-word).
- Special characters (ą, ć, ę, ł, ń, ó, ś, ź, ż) render correctly.
- No encoding issues.

**TC-TOOLKIT-06: Translate PL to EN**
Steps:
1. Navigate to `/admin/ai-toolkit.php`.
2. Enter Polish text.
3. Select mode: "Translate to English".
4. Click "Process".

Expected result:
- Output is accurate English translation.
- Grammar and syntax correct.
- Proper handling of Polish-specific linguistic features.

**TC-TOOLKIT-07: Fallback Behavior - AI Disabled**
Steps:
1. Disable AI provider in settings.
2. Navigate to `/admin/ai-toolkit.php`.
3. Attempt to use any mode.

Expected result:
- User-friendly error message or fallback text displayed.
- No stack traces or technical error codes.
- UI remains functional (not broken).
- Option to configure AI provider presented.

---

### 3.5 AI Theme Builder (/admin/ai-theme-builder.php)

**Purpose**: Generate HTML/CSS layout templates for common page types (Home, Blog Listing, Contact, etc.) using AI to accelerate theme development.

**TC-THEME-01: Generate Home Page Layout**
Steps:
1. Navigate to `/admin/ai-theme-builder.php`.
2. Select page type: "Home".
3. Optionally specify style preferences (modern, minimalist, corporate).
4. Click "Generate Layout".

Expected result:
- HTML structure generated with semantic tags (header, nav, main, footer).
- CSS classes or inline styles included.
- Layout is syntactically valid HTML (no unclosed tags).
- Copyable via button or text selection.
- Preview (if available) renders without JavaScript errors.

**TC-THEME-02: Generate Blog Listing Layout**
Steps:
1. Navigate to `/admin/ai-theme-builder.php`.
2. Select page type: "Blog Listing".
3. Click "Generate Layout".

Expected result:
- Layout includes article loop structure (post title, excerpt, thumbnail).
- Pagination or "Load More" placeholder present.
- Sidebar or widget areas included (if typical for blog layouts).
- Responsive design hints present (media queries or flex/grid).

**TC-THEME-03: Generate Contact Page Layout**
Steps:
1. Navigate to `/admin/ai-theme-builder.php`.
2. Select page type: "Contact".
3. Click "Generate Layout".

Expected result:
- Form elements included (name, email, message textarea, submit button).
- Map embed placeholder or address block present.
- CSRF token placeholder commented in form (security consideration).
- Layout is accessible (labels for form fields).

**TC-THEME-04: Copy and Export Layout**
Steps:
1. Generate any layout via AI Theme Builder.
2. Click "Copy to Clipboard" button (if present).
3. Paste into text editor and save as HTML file.
4. Open HTML file in browser.

Expected result:
- HTML renders without errors.
- Layout structure visible and functional.
- No broken references to external resources (or placeholders clearly marked).
- User can iterate on generated code.

**TC-THEME-05: Consistency Across Regenerations**
Steps:
1. Generate a "Home" layout.
2. Note key structural elements.
3. Regenerate same page type immediately.
4. Compare outputs.

Expected result:
- Core structure similar (header, main content, footer).
- Variations acceptable (AI creativity) but not wildly inconsistent.
- Quality remains high across regenerations.
- No degradation with repeated use.

**TC-THEME-06: Invalid or Unsupported Page Type**
Steps:
1. Navigate to `/admin/ai-theme-builder.php`.
2. Attempt to request a highly unusual page type (e.g., "404 Error Page with Animated GIF").

Expected result:
- System either generates a reasonable attempt or returns error.
- No crashes or hung requests.
- If unsupported, clear message guides user to standard page types.

---

### 3.6 Editor AI (/admin/editor-ai.php)

**Purpose**: Provide inline AI assistance for content editors, including improve_style, fix_spelling, shorten, expand, and seo_optimize operations on selected text.

**TC-EDITOR-01: Improve Style**
Steps:
1. Navigate to `/admin/editor-ai.php` or embedded editor interface.
2. Select a paragraph with awkward phrasing.
3. Choose action: "Improve Style".
4. Click "Apply".

Expected result:
- Text rewritten with better flow and readability.
- Meaning preserved.
- Changes highlighted or diff view shown (if feature exists).
- User can accept or reject changes.

**TC-EDITOR-02: Fix Spelling**
Steps:
1. Enter text with deliberate typos: "Ths is a tst of speling corection."
2. Choose action: "Fix Spelling".
3. Click "Apply".

Expected result:
- Typos corrected: "This is a test of spelling correction."
- Grammar errors also addressed (if tool supports).
- No false positives (correct words unchanged).
- Proper nouns and technical terms preserved.

**TC-EDITOR-03: Shorten Text**
Steps:
1. Select a verbose paragraph (200+ words).
2. Choose action: "Shorten".
3. Click "Apply".

Expected result:
- Output is 50-70% of original length.
- Key information retained.
- Readability maintained or improved.
- No critical details lost.

**TC-EDITOR-04: Expand Text**
Steps:
1. Select a brief sentence or bullet point.
2. Choose action: "Expand".
3. Click "Apply".

Expected result:
- Output is 2-3x longer.
- Additional context, examples, or explanations added.
- Expansion is relevant and useful (not just filler).
- Tone matches original.

**TC-EDITOR-05: SEO Optimize**
Steps:
1. Select a paragraph that is poorly optimized for search engines.
2. Specify target keyword (e.g., "sustainable energy").
3. Choose action: "SEO Optimize".
4. Click "Apply".

Expected result:
- Target keyword integrated naturally into text.
- Keyword density appropriate (not keyword stuffing).
- Readability maintained (not sacrificed for SEO).
- Related terms and synonyms included.

**TC-EDITOR-06: Fallback Behavior - AI Disabled**
Steps:
1. Disable AI provider in settings.
2. Navigate to `/admin/editor-ai.php`.
3. Attempt to use any editor action.

Expected result:
- Fallback text or error message displayed.
- Editor remains functional for manual editing.
- No JavaScript errors or broken UI.
- Option to enable AI presented with link to settings.

**TC-EDITOR-07: Large Text Selection**
Steps:
1. Select entire article (2000+ words).
2. Choose any AI action.
3. Submit request.

Expected result:
- System handles large input gracefully (may truncate or chunk).
- Request completes within timeout (or fails gracefully).
- User warned if text is too long for processing.
- No memory errors or server crashes.

---

### 3.7 AI Logs (/admin/ai-logs.php)

**Purpose**: Display audit log of all AI API requests, including timestamps, user, operation type, provider, model, token usage, and success/error status.

**TC-LOGS-01: View Recent Entries**
Steps:
1. Perform 2-3 AI operations (e.g., generate content, SEO analysis).
2. Navigate to `/admin/ai-logs.php`.
3. Observe log entries.

Expected result:
- New entries appear for recent operations.
- Each entry includes: timestamp, user ID/name, operation type, provider, model.
- Success/error status clearly indicated (color-coded or icon).
- Entries sorted by timestamp (newest first).

**TC-LOGS-02: Token Usage Display**
Steps:
1. Navigate to `/admin/ai-logs.php`.
2. Locate entries from OpenAI or similar provider that reports token usage.

Expected result:
- Prompt tokens and completion tokens displayed.
- Total tokens calculated correctly.
- Cost estimation shown (if pricing data available).
- No sensitive data exposed in token preview.

**TC-LOGS-03: Error Entry Details**
Steps:
1. Trigger an AI error (invalid API key, timeout, rate limit).
2. Navigate to `/admin/ai-logs.php`.
3. Click on error entry to expand details.

Expected result:
- Error message or code displayed.
- HTTP status code shown (if applicable).
- Timestamp of error accurate.
- Stack traces or internal paths NOT visible (security).
- User can understand what went wrong from description.

**TC-LOGS-04: Pagination and Search**
Steps:
1. Generate 50+ AI requests to populate log.
2. Navigate to `/admin/ai-logs.php`.
3. Use pagination controls to browse pages.
4. Use search/filter to find specific operation type or user.

Expected result:
- Pagination works smoothly (page numbers, next/prev links).
- Search/filter narrows results accurately.
- No duplicate entries across pages.
- Performance remains acceptable with large log files.

**TC-LOGS-05: Security - No Exposed Secrets**
Steps:
1. Navigate to `/admin/ai-logs.php`.
2. Inspect all visible log entries and details.
3. Check browser DevTools Network tab for API responses.

Expected result:
- No API keys visible in UI (masked or omitted).
- No full API URLs with embedded credentials.
- No raw request/response bodies with sensitive data.
- Only safe metadata displayed (provider name, model name, status).

**TC-LOGS-06: Log Rotation and Limits**
Steps:
1. Check current size of `logs/ai_requests.log`.
2. Generate many AI requests (100+).
3. Re-check log file size and structure.

Expected result:
- Log file grows but doesn't exceed configured limit (e.g., 10MB).
- Old entries rotated/archived if limit reached.
- No disk space exhaustion.
- JSONL format remains valid (each line is valid JSON).

---

### 3.8 AI Insights (/admin/ai-insights.php)

**Purpose**: Aggregate and display analytics on AI usage, including request counts by provider/model, success/error rates, token consumption trends, and cost estimates.

**TC-INSIGHTS-01: Dashboard Overview**
Steps:
1. Navigate to `/admin/ai-insights.php` after several days of AI usage.

Expected result:
- Total requests counter displayed.
- Success vs. error rate shown (percentage or pie chart).
- Most-used provider and model highlighted.
- Time-based graph or sparkline showing usage trends.

**TC-INSIGHTS-02: Provider Breakdown**
Steps:
1. Use multiple providers (OpenAI, HuggingFace, local) over time.
2. Navigate to `/admin/ai-insights.php`.
3. Locate provider breakdown section.

Expected result:
- Each provider listed with request count.
- Percentage of total requests calculated.
- Color-coded or tabular display for clarity.
- Drill-down option to see model-level detail (if available).

**TC-INSIGHTS-03: Token Usage and Cost**
Steps:
1. Navigate to `/admin/ai-insights.php`.
2. Locate token usage statistics.

Expected result:
- Total tokens consumed (prompt + completion) displayed.
- Average tokens per request calculated.
- Cost estimation shown (if pricing configured for providers).
- Trend over time visible (increasing/decreasing usage).

**TC-INSIGHTS-04: Error Analysis**
Steps:
1. Deliberately trigger several errors (bad config, rate limits).
2. Navigate to `/admin/ai-insights.php`.
3. View error breakdown section.

Expected result:
- Error count and percentage displayed.
- Common error types listed (e.g., "Rate Limit", "Invalid API Key", "Timeout").
- Suggestions or links to resolve common issues.
- No sensitive details exposed.

**TC-INSIGHTS-05: Date Range Filtering**
Steps:
1. Navigate to `/admin/ai-insights.php`.
2. Select date range filter (e.g., "Last 7 Days", "Last Month").
3. Apply filter.

Expected result:
- All statistics update to reflect selected date range.
- Total counts, success rates, and charts adjust accordingly.
- No data from outside date range included.
- Reset option returns to default view.

**TC-INSIGHTS-06: Real-Time Update**
Steps:
1. Open `/admin/ai-insights.php` in one browser tab.
2. In another tab, generate AI content.
3. Refresh insights page.

Expected result:
- New request reflected in totals (after cache expiry or manual refresh).
- Statistics increment correctly.
- No stale data displayed.
- If auto-refresh enabled, updates appear without manual refresh.

---

### 3.9 AI Settings (/admin/ai-settings.php)

**Purpose**: Configure AI providers, API keys, models, timeouts, rate limits, and feature toggles. Persist settings to `config/ai_settings.json`.

**TC-SETTINGS-01: Add OpenAI Provider**
Steps:
1. Navigate to `/admin/ai-settings.php`.
2. Select "OpenAI" from provider dropdown.
3. Enter API key (use test/sandbox key if available).
4. Select model (e.g., "gpt-3.5-turbo").
5. Set timeout: 30 seconds.
6. Click "Save Settings".

Expected result:
- Success message confirms save.
- Settings persist to `config/ai_settings.json`.
- API key is NOT displayed in plain text on reload (masked or hidden).
- Provider becomes available for AI operations immediately.

**TC-SETTINGS-02: Test Connection**
Steps:
1. Configure an AI provider with valid credentials.
2. Click "Test Connection" button (if available).

Expected result:
- System sends test request to provider API.
- Success message if connection works ("Connected successfully").
- Error message if connection fails (with reason: invalid key, network error, etc.).
- No sensitive data leaked in error messages.

**TC-SETTINGS-03: Disable Provider**
Steps:
1. Configure and enable an AI provider.
2. Toggle "Enable" switch to OFF.
3. Save settings.
4. Attempt to use AI features.

Expected result:
- Provider disabled in settings.
- AI features return error or fallback when attempting to use disabled provider.
- Other enabled providers (if any) still functional.
- Setting persists across page reloads.

**TC-SETTINGS-04: Multiple Providers**
Steps:
1. Configure OpenAI with valid key.
2. Add HuggingFace with valid key.
3. Set OpenAI as default provider.
4. Save settings.
5. Use AI feature without specifying provider.

Expected result:
- Default provider (OpenAI) is used automatically.
- Option to switch provider on-demand in some interfaces.
- Both providers listed in settings with enable/disable toggles.
- No conflicts between providers.

**TC-SETTINGS-05: Invalid API Key Handling**
Steps:
1. Navigate to `/admin/ai-settings.php`.
2. Enter intentionally invalid API key (e.g., "invalid_key_123").
3. Save settings.
4. Attempt to use AI feature.

Expected result:
- Settings save successfully (no validation on save, only on use).
- AI operation fails with clear error: "Invalid API key" or "Authentication failed".
- Error logged to AI logs.
- User prompted to check settings.

**TC-SETTINGS-06: Timeout and Rate Limit Configuration**
Steps:
1. Navigate to `/admin/ai-settings.php`.
2. Set timeout to 10 seconds (very short).
3. Set rate limit to 5 requests per minute.
4. Save settings.
5. Rapidly trigger 6 AI requests in under a minute.

Expected result:
- First 5 requests succeed (within rate limit).
- 6th request fails with "Rate limit exceeded" error.
- Timeout setting enforced (requests exceeding 10s return timeout error).
- Settings accurately control behavior.

**TC-SETTINGS-07: Configuration Persistence Across Sessions**
Steps:
1. Configure AI settings with specific provider and model.
2. Log out of admin panel.
3. Log back in.
4. Navigate to `/admin/ai-settings.php`.

Expected result:
- All settings retained exactly as saved.
- API keys remain associated with correct providers.
- No settings reset to defaults.
- `config/ai_settings.json` file contains expected JSON structure.

---

### 3.10 AI Core & Logging (/core/ai_content.php, logs/ai_requests.log)

**Purpose**: Backend infrastructure for AI operations, including request handling, response parsing, error recovery, and detailed logging to JSONL format.

**TC-CORE-01: Successful Request Lifecycle**
Steps:
1. Trigger any AI operation that uses `core/ai_content.php`.
2. Monitor `logs/ai_requests.log` during request.
3. Verify log entry after completion.

Expected result:
- Log entry created with all required fields: timestamp, user, operation, provider, model, status.
- Request ID generated and logged.
- Response time recorded.
- Token usage logged (if provider supports).
- Entry is valid JSONL (one JSON object per line).

**TC-CORE-02: Error Logging**
Steps:
1. Trigger AI operation with invalid config (bad key, unreachable endpoint).
2. Wait for error to occur.
3. Check `logs/ai_requests.log`.

Expected result:
- Error entry created with status "error" or "failed".
- Error message recorded (sanitized, no secrets).
- HTTP status code logged (if applicable).
- Stack trace NOT included in log (kept in separate debug log if needed).
- User ID and operation context preserved for debugging.

**TC-CORE-03: Request Queueing and Throttling**
Steps:
1. Configure rate limit in AI settings (e.g., 10 requests/minute).
2. Rapidly send 15 AI requests in quick succession.
3. Observe behavior and check logs.

Expected result:
- First 10 requests processed immediately.
- Remaining 5 requests either queued or rejected with rate limit error.
- If queued, requests processed after rate limit window resets.
- All requests logged (including rejected ones) with appropriate status.

**TC-CORE-04: Response Caching (if implemented)**
Steps:
1. Send identical AI request twice (same prompt, provider, model).
2. Compare response times.
3. Check logs for cache hit indicator.

Expected result:
- Second request returns faster (if caching enabled).
- Log entry for second request marked as "cache hit" or similar.
- Cached response identical to original.
- Cache respects TTL settings.

**TC-CORE-05: JSONL Format Validation**
Steps:
1. Generate several AI requests (mix of success and errors).
2. Open `logs/ai_requests.log` in text editor.
3. Parse each line as JSON using external tool (e.g., `jq`, Python).

Expected result:
- Every line is valid JSON (no parse errors).
- No multi-line entries (each entry is single line).
- All entries contain required fields (even if null).
- No trailing commas or malformed arrays/objects.
- File encoding is UTF-8 without BOM.

**TC-CORE-06: Log Rotation**
Steps:
1. Check size of `logs/ai_requests.log`.
2. Generate enough requests to exceed rotation threshold (e.g., 10MB).
3. Verify log rotation behavior.

Expected result:
- When threshold reached, log rotated to timestamped backup (e.g., `ai_requests.log.2025-11-28`).
- New `ai_requests.log` file created and continues logging.
- No data loss during rotation.
- Old logs archived and optionally compressed.

**TC-CORE-07: Concurrent Request Handling**
Steps:
1. Use multiple browser tabs/sessions to trigger AI operations simultaneously.
2. Monitor system load and log entries.

Expected result:
- All requests handled without conflicts.
- No race conditions in logging (entries not garbled).
- System remains responsive under concurrent load.
- Rate limiting applied per-user or globally (as configured).
- No duplicate request IDs.

---

## 4. Negative / Security Tests

This section covers adversarial scenarios, security validation, and abuse prevention.

**TC-SEC-01: CSRF Token Validation**
Steps:
1. Navigate to any AI form (e.g., AI Content Creator).
2. Use browser DevTools to remove or modify CSRF token in form.
3. Submit form.

Expected result:
- Request rejected with 403 Forbidden or similar error.
- Error message indicates CSRF validation failure (in dev mode).
- No operation performed.
- No data saved or AI request sent.

**TC-SEC-02: Unauthenticated Access**
Steps:
1. Log out of admin panel.
2. Attempt to access `/admin/ai-content-creator.php` directly via URL.

Expected result:
- Redirect to login page.
- Or 403 Forbidden error if not redirecting.
- No AI operations accessible without authentication.
- Session properly invalidated on logout.

**TC-SEC-03: Authorization Checks**
Steps:
1. Log in as user with limited permissions (not admin).
2. Attempt to access AI Settings or other privileged AI pages.

Expected result:
- Access denied with 403 or redirect to dashboard.
- Error message explains insufficient permissions.
- No sensitive data exposed even if page partially loads.
- Audit log records unauthorized access attempt (optional).

**TC-SEC-04: Path Traversal in Parameters**
Steps:
1. Navigate to AI log viewer or similar page with file/path parameters.
2. Modify URL parameter to attempt path traversal: `?file=../../../etc/passwd`.
3. Submit request.

Expected result:
- Parameter sanitized or rejected.
- Error returned (invalid parameter).
- No file system access outside intended directories.
- Attempted attack logged as suspicious activity.

**TC-SEC-05: No Secrets in UI or Logs**
Steps:
1. Review all AI-related pages, logs, and API responses.
2. Search for substrings of actual API keys or secrets.
3. Check browser DevTools Network/Console tabs.

Expected result:
- API keys never displayed in plain text (masked as "sk-...****").
- No full API keys in HTML source code.
- No secrets in JavaScript variables or console output.
- Log files contain no credentials or sensitive tokens.
- DevTools Network tab shows sanitized request/response bodies.

**TC-SEC-06: SQL Injection in AI Parameters**
Steps:
1. In any AI form, enter SQL injection payload in text field: `' OR '1'='1`.
2. Submit form.

Expected result:
- Input treated as literal text, not interpreted as SQL.
- No database errors or unexpected behavior.
- Prepared statements/parameterized queries prevent injection.
- Input sanitized or escaped before any database operation.

**TC-SEC-07: XSS in Generated Content**
Steps:
1. Use AI to generate content with prompt designed to inject script: "Generate article about <script>alert('XSS')</script>".
2. View generated content in browser.

Expected result:
- Generated content does not execute JavaScript.
- Script tags escaped or removed (HTML entities: `&lt;script&gt;`).
- Content-Security-Policy headers prevent inline script execution.
- No alerts or malicious JavaScript executed.

**TC-SEC-08: Rate Limiting Bypass Attempt**
Steps:
1. Configure rate limit in AI settings.
2. Attempt to bypass by rotating IP addresses or using multiple sessions.

Expected result:
- Rate limiting enforced per user account (not just IP).
- Session-based limits prevent bypass via new tabs.
- Aggressive abuse triggers additional restrictions or CAPTCHA.
- Bypass attempts logged for review.

**TC-SEC-09: Oversized Payload Handling**
Steps:
1. Attempt to submit extremely large text input (e.g., 1MB+ of text) to AI endpoint.

Expected result:
- Request rejected with 413 Payload Too Large or similar.
- Server does not hang or crash processing oversized input.
- Client-side validation prevents submission (if implemented).
- Error message guides user to input size limits.

**TC-SEC-10: API Key Leakage via Error Messages**
Steps:
1. Trigger various error conditions (timeout, invalid model, network error).
2. Inspect all error messages in UI and logs.

Expected result:
- Error messages contain no API keys or partial keys.
- No internal URLs or endpoints with embedded credentials.
- Generic error messages in production (detailed only in dev mode).
- Logs sanitize sensitive data before writing.

---

## 5. Completion Criteria

The following conditions must be satisfied before AI modules are considered fully validated:

- [ ] **All AI Pages Load Without PHP Errors**: No warnings, notices, or fatal errors in any AI-related admin page.
- [ ] **At Least One Successful AI Request**: Each module (Content Creator, SEO, Toolkit, etc.) successfully generates output via configured AI provider.
- [ ] **At Least One Deliberate Failure Handled Gracefully**: Invalid API key, timeout, or disabled provider results in user-friendly error message, not stack trace.
- [ ] **AI Logs Populated and Readable**: `logs/ai_requests.log` contains valid JSONL entries for all AI operations.
- [ ] **AI Insights Show Consistent Data**: Aggregated statistics match log entries; no discrepancies in counts or success rates.
- [ ] **No Secrets Exposed**: API keys never visible in UI, logs, HTML source, or browser DevTools.
- [ ] **No Stack Traces in Browser**: Production mode shows generic errors; detailed traces only in dev mode.
- [ ] **CSRF Protection Verified**: All AI forms reject submissions with missing or invalid CSRF tokens.
- [ ] **Authentication Required**: Unauthenticated users cannot access AI pages or perform operations.
- [ ] **Rate Limiting Functional**: Rapid requests respect configured limits; excess requests rejected or queued.
- [ ] **Multi-Language Support**: AI operations work correctly for both English and Polish (and other configured languages).
- [ ] **Generated Content Safe**: No XSS, SQL injection, or other malicious code in AI-generated output.
- [ ] **Performance Acceptable**: AI requests complete within configured timeouts; no indefinite hangs.
- [ ] **Settings Persist**: AI configuration survives server restart, logout/login, and page refreshes.
- [ ] **Log Rotation Works**: Logs do not grow unbounded; old entries archived per retention policy.
- [ ] **Documentation Accurate**: This test plan covers all active AI modules; any new modules added to plan.

---

**Version**: 1.0
**Last Updated**: 2025-11-28
**Maintainer**: QA Team / DevOps
**Review Cycle**: Quarterly or after major AI feature releases
