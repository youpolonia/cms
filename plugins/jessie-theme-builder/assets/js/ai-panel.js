/**
 * JTB AI Panel JavaScript
 * Handles AI-powered layout and content generation UI
 * Including Compositional Pattern System
 *
 * @package JessieThemeBuilder
 */

(function() {
    'use strict';

    // ========================================
    // State
    // ========================================

    const JTB_AI = {
        panel: null,
        previewModal: null,
        patternModal: null,
        currentTab: 'compose',
        isGenerating: false,
        generatedLayout: null,
        selectedSection: null,
        selectedContentType: null,
        pageId: 0,
        csrfToken: '',
        // Template mode support (for Theme Builder)
        mode: 'page',           // 'page' or 'template'
        templateType: null,     // 'header', 'footer', 'body', etc.
        templateId: null,
        // Event listener tracking for memory leak prevention
        _trackedListeners: [],
        // Compositional system state
        patterns: {},
        patternInfo: {},
        compositionSequences: {},  // Sequences loaded from API
        selectedIntent: null,
        selectedStyle: 'modern',
        selectedPattern: null,
        selectedVariant: null,
        compositionPreview: [],
        // DEV_MODE Visual Debug
        debugMode: false,
        lastQuality: null,
        // Stage 25: Single source of truth
        currentLayout: null,
        lastMeta: null,
        // NEW: AST mode - enables AI-driven layout generation
        // When true, AI actually decides page structure
        // When false, uses legacy hardcoded patterns
        useASTMode: true,  // DEFAULT TO TRUE - new architecture!
        // NEW: AI provider and model selection
        // Default: Anthropic Claude Opus 4.5 (best for theme generation)
        aiProvider: 'anthropic',
        aiModel: 'claude-opus-4-5-20251101',
        // Available providers and models
        availableProviders: {
            'anthropic': {
                name: 'Anthropic Claude',
                default: 'claude-opus-4-5-20251101',
                models: {
                    'claude-opus-4-5-20251101': 'Claude Opus 4.5 (Best for Themes)',
                    'claude-sonnet-4-5-20250929': 'Claude Sonnet 4.5 (Best Balance)',
                    'claude-haiku-4-5-20251001': 'Claude Haiku 4.5 (Fastest)'
                }
            },
            'openai': {
                name: 'OpenAI',
                default: 'gpt-4o',
                models: {
                    'gpt-5.2': 'GPT-5.2 (Flagship Thinking)',
                    'gpt-5': 'GPT-5 (General/Agentic)',
                    'gpt-4o': 'GPT-4o (Legacy)',
                    'gpt-4o-mini': 'GPT-4o Mini (Fast)'
                }
            },
            'google': {
                name: 'Google Gemini',
                default: 'gemini-2.0-flash',
                models: {
                    'gemini-2.5-pro': 'Gemini 2.5 Pro (Best Quality)',
                    'gemini-2.5-flash': 'Gemini 2.5 Flash (Latest)',
                    'gemini-2.0-flash': 'Gemini 2.0 Flash (Free Tier)'
                }
            },
            'deepseek': {
                name: 'DeepSeek',
                default: 'deepseek-chat',
                models: {
                    'deepseek-r1': 'DeepSeek R1 (Reasoning)',
                    'deepseek-v3': 'DeepSeek V3'
                }
            }
        }
    };

    // ========================================
    // STAGE 25: Single Source of Truth
    // ========================================

    /**
     * Stage 25: Set AI result - single entry point for layout/quality/meta storage
     * MUST be called before showPreview() in every code path
     * @param {Object} layout - Layout data from API
     * @param {Object} quality - Quality metrics from API
     * @param {Object} meta - Additional metadata
     */
    function setAiResult(layout, quality, meta) {
        JTB_AI.currentLayout = layout || null;
        JTB_AI.generatedLayout = layout || null; // Legacy compatibility
        JTB_AI.lastQuality = quality || {};
        JTB_AI.lastMeta = meta || {};

        if (window.JTB_DEV_MODE) {
            // console.log removed
                hasLayout: !!layout,
                hasQuality: !!quality,
                sectionsCount: (layout?.sections ?? layout?.content)?.length || 0,
                qualityScore: quality?.score,
                qualityDecision: quality?.decision
            });
        }
    }

    /**
     * Stage 25: Get current layout from single source
     * @returns {Object} Current layout or empty object
     */
    function getCurrentLayout() {
        return JTB_AI.currentLayout || JTB_AI.generatedLayout || {};
    }

    /**
     * Stage 25: Get current quality from single source
     * @returns {Object} Current quality or empty object
     */
    function getCurrentQuality() {
        const layout = getCurrentLayout();
        return JTB_AI.lastQuality || layout?._quality || {};
    }

    // ========================================
    // STAGE 26: Baseline Diff Mode + Why-Changed
    // ========================================

    const BASELINE_STORAGE_KEY = 'jtb_baseline_v1';
    const DIFF_MODE_STORAGE_KEY = 'jtb_diff_mode_v1';

    /**
     * Stage 26: Build SAFE baseline snapshot - metrics only, NO content
     * @param {Object} quality - Quality metrics from API
     * @param {Object} meta - Additional metadata
     * @returns {Object} Safe snapshot with metrics only
     */
    function buildSafeBaselineSnapshot(quality, meta) {
        if (!window.JTB_DEV_MODE) return null;

        const q = quality || {};
        const m = meta || {};

        // Extract ONLY numeric metrics and status strings - NO content
        return {
            ts: Date.now(),
            // Core quality
            score: typeof q.score === 'number' ? q.score : 0,
            status: typeof q.status === 'string' ? q.status : 'unknown',
            attempt: typeof q.attempt === 'number' ? q.attempt : 0,
            confidence: typeof q.confidence === 'number' ? q.confidence : 0,
            decision: typeof q.decision === 'string' ? q.decision : '',
            stop_reason: typeof q.stop_reason === 'string' ? q.stop_reason : '',
            // Stage 22: Consistency
            consistency_score: typeof q.consistency_score === 'number' ? q.consistency_score : 0,
            // Stage 23: Stability
            stability_score: typeof q.stability_score === 'number' ? q.stability_score : 0,
            stability_level: typeof q.stability_level === 'string' ? q.stability_level : '',
            // Section counts
            total_sections: typeof q.total_sections === 'number' ? q.total_sections : 0,
            dark_sections: typeof q.dark_sections === 'number' ? q.dark_sections : 0,
            // Narrative
            narrative_signature: typeof q.narrative_signature === 'string' ? q.narrative_signature : '',
            narrative_score: typeof q.narrative_score === 'number' ? q.narrative_score : 0,
            // Scale
            hero_scale: typeof q.hero_scale === 'string' ? q.hero_scale : '',
            cta_scale: typeof q.cta_scale === 'string' ? q.cta_scale : '',
            // Counts only (no arrays)
            warnings_count: Array.isArray(q.warnings) ? q.warnings.length : (typeof q.warnings_count === 'number' ? q.warnings_count : 0),
            violations_count: Array.isArray(q.violations) ? q.violations.length : (typeof q.violations_count === 'number' ? q.violations_count : 0),
            has_critical: !!q.has_critical,
            autofix_count: typeof q.autofix_count === 'number' ? q.autofix_count : 0,
            // Stage 24: Gate
            gate_state: typeof q.gate_state === 'string' ? q.gate_state : '',
            // Drift counts
            drift_high_count: typeof q.drift_high_count === 'number' ? q.drift_high_count : 0,
            drift_total_count: typeof q.drift_total_count === 'number' ? q.drift_total_count : 0
        };
    }

    /**
     * Stage 26: Save baseline to sessionStorage
     * @param {Object} quality - Quality metrics
     * @param {Object} meta - Metadata
     */
    function freezeBaseline(quality, meta) {
        if (!window.JTB_DEV_MODE) return;

        const snapshot = buildSafeBaselineSnapshot(quality, meta);
        if (snapshot) {
            try {
                sessionStorage.setItem(BASELINE_STORAGE_KEY, JSON.stringify(snapshot));
                // console.log removed
            } catch (e) {
                console.warn('[Stage 26] Failed to save baseline:', e);
            }
        }
    }

    /**
     * Stage 26: Get baseline from sessionStorage
     * @returns {Object|null} Baseline snapshot or null
     */
    function getBaseline() {
        if (!window.JTB_DEV_MODE) return null;

        try {
            const data = sessionStorage.getItem(BASELINE_STORAGE_KEY);
            return data ? JSON.parse(data) : null;
        } catch (e) {
            return null;
        }
    }

    /**
     * Stage 26: Reset baseline
     */
    function resetBaseline() {
        if (!window.JTB_DEV_MODE) return;

        try {
            sessionStorage.removeItem(BASELINE_STORAGE_KEY);
            // console.log removed
        } catch (e) {
            // Ignore
        }
    }

    /**
     * Stage 26: Get diff mode state
     * @returns {boolean} Whether diff mode is enabled
     */
    function isDiffModeEnabled() {
        if (!window.JTB_DEV_MODE) return false;

        try {
            return sessionStorage.getItem(DIFF_MODE_STORAGE_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    /**
     * Stage 26: Toggle diff mode
     * @param {boolean} enabled - Enable or disable
     */
    function setDiffMode(enabled) {
        if (!window.JTB_DEV_MODE) return;

        try {
            sessionStorage.setItem(DIFF_MODE_STORAGE_KEY, enabled ? '1' : '0');
        } catch (e) {
            // Ignore
        }
    }

    /**
     * Stage 26: Compute baseline diff
     * @param {Object} current - Current quality metrics (safe snapshot)
     * @param {Object} base - Baseline snapshot
     * @returns {Object} Diff result with deltas and flags
     */
    function computeBaselineDiff(current, base) {
        if (!window.JTB_DEV_MODE) return { ok: false };
        if (!current || !base) return { ok: false };

        const deltas = {
            score_delta: (current.score || 0) - (base.score || 0),
            confidence_delta: (current.confidence || 0) - (base.confidence || 0),
            consistency_delta: (current.consistency_score || 0) - (base.consistency_score || 0),
            stability_delta: (current.stability_score || 0) - (base.stability_score || 0),
            narrative_delta: (current.narrative_score || 0) - (base.narrative_score || 0),
            sections_delta: (current.total_sections || 0) - (base.total_sections || 0),
            dark_delta: (current.dark_sections || 0) - (base.dark_sections || 0),
            warnings_delta: (current.warnings_count || 0) - (base.warnings_count || 0),
            violations_delta: (current.violations_count || 0) - (base.violations_count || 0)
        };

        const flags = {
            improved: deltas.score_delta > 0,
            regressed: deltas.score_delta < 0,
            narrative_changed: current.narrative_signature !== base.narrative_signature,
            gate_changed: current.gate_state !== base.gate_state,
            stability_changed: current.stability_level !== base.stability_level
        };

        return { ok: true, deltas, flags };
    }

    /**
     * Stage 26: Build deterministic "why changed" explanations
     * NO content, NO rule names, only metric-based reasons
     * @param {Object} current - Current snapshot
     * @param {Object} base - Baseline snapshot
     * @param {Object} diff - Diff result from computeBaselineDiff
     * @returns {Array<string>} Up to 4 short reasons
     */
    function buildWhyChanged(current, base, diff) {
        if (!window.JTB_DEV_MODE) return [];
        if (!diff || !diff.ok) return [];

        const reasons = [];
        const d = diff.deltas;
        const f = diff.flags;

        // Narrative signature changed
        if (f.narrative_changed) {
            reasons.push('Story flow changed (signature).');
        }

        // Stability worsened
        if (f.stability_changed && current.stability_level === 'chaotic') {
            reasons.push('Temporal stability worsened (drift).');
        } else if (f.stability_changed && d.stability_delta < 0) {
            reasons.push('Stability decreased.');
        }

        // Consistency dropped significantly
        if (d.consistency_delta <= -12) {
            reasons.push('Consistency dropped (style drift).');
        } else if (d.consistency_delta <= -5) {
            reasons.push('Consistency decreased.');
        }

        // Dark sections changed
        if (d.dark_delta > 0) {
            reasons.push('More DARK sections (contrast shift).');
        } else if (d.dark_delta < 0) {
            reasons.push('Fewer DARK sections.');
        }

        // Violations increased
        if (d.violations_delta > 0) {
            reasons.push('More violations (hard rules hit).');
        }

        // Warnings increased
        if (d.warnings_delta > 0 && reasons.length < 4) {
            reasons.push('More warnings (soft regressions).');
        }

        // Scale changed (hero or cta)
        if (current.hero_scale !== base.hero_scale && reasons.length < 4) {
            reasons.push('Hero scale changed.');
        }
        if (current.cta_scale !== base.cta_scale && reasons.length < 4) {
            reasons.push('CTA scale changed.');
        }

        // Gate state changed
        if (f.gate_changed && reasons.length < 4) {
            reasons.push('Gate state changed (' + base.gate_state + ' → ' + current.gate_state + ').');
        }

        // Score improved/regressed
        if (f.improved && reasons.length < 4) {
            reasons.push('Quality score improved (+' + d.score_delta + ').');
        } else if (f.regressed && reasons.length < 4) {
            reasons.push('Quality score regressed (' + d.score_delta + ').');
        }

        // Return max 4
        return reasons.slice(0, 4);
    }

    /**
     * Stage 26: Format time from timestamp
     * @param {number} ts - Timestamp
     * @returns {string} HH:MM:SS format
     */
    function formatBaselineTime(ts) {
        if (!ts) return '--:--:--';
        const d = new Date(ts);
        return d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    /**
     * Stage 26: Build baseline controls HTML
     * @returns {string} HTML for baseline buttons
     */
    function buildBaselineControlsHtml() {
        if (!window.JTB_DEV_MODE) return '';

        const baseline = getBaseline();
        const diffEnabled = isDiffModeEnabled();

        let html = '<div class="jtb-debug-baseline-controls">';

        // Freeze button
        html += '<button class="jtb-debug-baseline-btn jtb-debug-freeze-btn" title="Freeze current generation as baseline">🧊 Freeze</button>';

        // Reset button (only if baseline exists)
        if (baseline) {
            html += '<button class="jtb-debug-baseline-btn jtb-debug-reset-btn" title="Clear baseline">♻ Reset</button>';
        }

        // Diff toggle
        const diffState = diffEnabled ? 'ON' : 'OFF';
        const diffClass = diffEnabled ? 'jtb-debug-diff-on' : 'jtb-debug-diff-off';
        html += '<button class="jtb-debug-baseline-btn jtb-debug-diff-toggle ' + diffClass + '" title="Toggle diff comparison mode">Δ Diff: ' + diffState + '</button>';

        html += '</div>';

        return html;
    }

    /**
     * Stage 26: Build baseline status badge HTML
     * @returns {string} HTML for baseline status
     */
    function buildBaselineStatusHtml() {
        if (!window.JTB_DEV_MODE) return '';

        const baseline = getBaseline();
        const diffEnabled = isDiffModeEnabled();

        if (!baseline) {
            if (diffEnabled) {
                return '<span class="jtb-debug-no-baseline" title="Freeze a baseline first to compare">NO BASELINE</span>';
            }
            return '';
        }

        const timeStr = formatBaselineTime(baseline.ts);
        return '<span class="jtb-debug-baseline-badge" title="Baseline frozen at ' + timeStr + '\nScore: ' + baseline.score + '\nConsistency: ' + baseline.consistency_score + '\nStability: ' + baseline.stability_level + '">BASELINE ✓ (' + timeStr + ')</span>';
    }

    /**
     * Stage 26: Build diff chips HTML
     * @param {Object} currentSnapshot - Current safe snapshot
     * @returns {string} HTML for diff chips
     */
    function buildDiffChipsHtml(currentSnapshot) {
        if (!window.JTB_DEV_MODE) return '';
        if (!isDiffModeEnabled()) return '';

        const baseline = getBaseline();
        if (!baseline) return '';

        const diff = computeBaselineDiff(currentSnapshot, baseline);
        if (!diff.ok) return '';

        const d = diff.deltas;

        // Helper to format delta chip
        const chip = (label, value, invert) => {
            const v = value || 0;
            let cls = 'neutral';
            if (invert) {
                // For warnings/violations: less is better
                if (v < 0) cls = 'positive';
                else if (v > 0) cls = 'negative';
            } else {
                // For scores: more is better
                if (v > 0) cls = 'positive';
                else if (v < 0) cls = 'negative';
            }
            const sign = v > 0 ? '+' : '';
            return '<span class="jtb-debug-diff-chip ' + cls + '" title="' + label + ' delta: ' + sign + v + '">Δ' + label.toLowerCase().substring(0, 4) + ' ' + sign + v + '</span>';
        };

        let html = '<div class="jtb-debug-diff-chips">';
        html += chip('Score', d.score_delta, false);
        html += chip('Cons', d.consistency_delta, false);
        html += chip('Stab', d.stability_delta, false);
        html += chip('Warn', d.warnings_delta, true);
        html += chip('Viol', d.violations_delta, true);
        html += '</div>';

        return html;
    }

    /**
     * Stage 26: Build why-changed HTML
     * @param {Object} currentSnapshot - Current safe snapshot
     * @returns {string} HTML for why-changed reasons
     */
    function buildWhyChangedHtml(currentSnapshot) {
        if (!window.JTB_DEV_MODE) return '';
        if (!isDiffModeEnabled()) return '';

        const baseline = getBaseline();
        if (!baseline) return '';

        const diff = computeBaselineDiff(currentSnapshot, baseline);
        const reasons = buildWhyChanged(currentSnapshot, baseline, diff);

        if (reasons.length === 0) return '';

        let html = '<div class="jtb-debug-why-container">';
        html += '<span class="jtb-debug-why-label">WHY:</span>';
        reasons.forEach(r => {
            html += '<span class="jtb-debug-why-chip" title="' + r + '">' + r + '</span>';
        });
        html += '</div>';

        return html;
    }

    /**
     * Stage 26: Bind baseline control events
     * Must be called after banner is rendered
     */
    function bindBaselineEvents() {
        if (!window.JTB_DEV_MODE) return;

        // Freeze button
        const freezeBtn = document.querySelector('.jtb-debug-freeze-btn');
        if (freezeBtn) {
            freezeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const quality = getCurrentQuality();
                const meta = JTB_AI.lastMeta || {};
                freezeBaseline(quality, meta);
                // Re-render banner
                refreshDebugBanner();
            });
        }

        // Reset button
        const resetBtn = document.querySelector('.jtb-debug-reset-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                resetBaseline();
                refreshDebugBanner();
            });
        }

        // Diff toggle
        const diffToggle = document.querySelector('.jtb-debug-diff-toggle');
        if (diffToggle) {
            diffToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const current = isDiffModeEnabled();
                setDiffMode(!current);
                refreshDebugBanner();
            });
        }
    }

    /**
     * Stage 26: Refresh debug banner (re-render baseline section)
     */
    function refreshDebugBanner() {
        if (!window.JTB_DEV_MODE) return;

        const banner = document.querySelector('.jtb-debug-banner-main');
        if (!banner) return;

        // Find or create baseline row
        let baselineRow = banner.querySelector('.jtb-debug-baseline-row');
        if (!baselineRow) {
            baselineRow = document.createElement('div');
            baselineRow.className = 'jtb-debug-baseline-row';
            banner.appendChild(baselineRow);
        }

        // Build current snapshot for diff
        const quality = getCurrentQuality();
        const meta = JTB_AI.lastMeta || {};
        const currentSnapshot = buildSafeBaselineSnapshot(quality, meta);

        // Render baseline row content
        let rowHtml = buildBaselineControlsHtml();
        rowHtml += buildBaselineStatusHtml();
        rowHtml += buildDiffChipsHtml(currentSnapshot);
        rowHtml += buildWhyChangedHtml(currentSnapshot);

        baselineRow.innerHTML = rowHtml;

        // Re-bind events
        bindBaselineEvents();
    }

    // ========================================
    // Pattern Visual Previews (Mini wireframes)
    // ========================================

    const PATTERN_ICONS = {
        // Hero patterns
        'hero_asymmetric': `<svg viewBox="0 0 80 50"><rect x="2" y="2" width="35" height="46" fill="#e5e7eb"/><rect x="42" y="8" width="36" height="6" fill="#3b82f6"/><rect x="42" y="18" width="30" height="3" fill="#9ca3af"/><rect x="42" y="24" width="28" height="3" fill="#9ca3af"/><rect x="42" y="32" width="20" height="8" rx="2" fill="#3b82f6"/></svg>`,
        'hero_centered': `<svg viewBox="0 0 80 50"><rect x="15" y="6" width="50" height="6" fill="#3b82f6"/><rect x="20" y="16" width="40" height="3" fill="#9ca3af"/><rect x="25" y="22" width="30" height="3" fill="#9ca3af"/><rect x="28" y="32" width="24" height="8" rx="2" fill="#3b82f6"/></svg>`,
        'hero_split': `<svg viewBox="0 0 80 50"><rect x="2" y="2" width="37" height="46" fill="#f3f4f6"/><rect x="6" y="10" width="28" height="5" fill="#3b82f6"/><rect x="6" y="20" width="24" height="3" fill="#9ca3af"/><rect x="6" y="26" width="20" height="3" fill="#9ca3af"/><rect x="6" y="36" width="16" height="6" rx="2" fill="#3b82f6"/><rect x="42" y="2" width="36" height="46" fill="#e5e7eb"/></svg>`,

        // Content Flow - PHP pattern names
        'grid_density': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="22" height="20" rx="2" fill="#e5e7eb"/><rect x="29" y="4" width="22" height="20" rx="2" fill="#e5e7eb"/><rect x="54" y="4" width="22" height="20" rx="2" fill="#e5e7eb"/><rect x="4" y="28" width="22" height="20" rx="2" fill="#e5e7eb"/><rect x="29" y="28" width="22" height="20" rx="2" fill="#e5e7eb"/><rect x="54" y="28" width="22" height="20" rx="2" fill="#e5e7eb"/></svg>`,
        'grid_featured': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="35" height="42" rx="2" fill="#3b82f6" opacity="0.3"/><rect x="42" y="4" width="17" height="20" rx="2" fill="#e5e7eb"/><rect x="61" y="4" width="15" height="20" rx="2" fill="#e5e7eb"/><rect x="42" y="28" width="17" height="18" rx="2" fill="#e5e7eb"/><rect x="61" y="28" width="15" height="18" rx="2" fill="#e5e7eb"/></svg>`,
        'zigzag_narrative': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="30" height="18" fill="#e5e7eb"/><rect x="38" y="6" width="38" height="4" fill="#3b82f6"/><rect x="38" y="12" width="30" height="3" fill="#9ca3af"/><rect x="4" y="30" width="38" height="4" fill="#3b82f6"/><rect x="4" y="36" width="30" height="3" fill="#9ca3af"/><rect x="46" y="28" width="30" height="18" fill="#e5e7eb"/></svg>`,
        'progressive_disclosure': `<svg viewBox="0 0 80 50"><circle cx="15" cy="12" r="8" fill="#3b82f6"/><text x="15" y="15" font-size="8" fill="#fff" text-anchor="middle">1</text><rect x="28" y="8" width="48" height="8" fill="#e5e7eb"/><circle cx="15" cy="32" r="8" fill="#3b82f6"/><text x="15" y="35" font-size="8" fill="#fff" text-anchor="middle">2</text><rect x="28" y="28" width="48" height="8" fill="#e5e7eb"/></svg>`,

        // Legacy names (aliases)
        'features_grid': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="22" height="20" rx="2" fill="#e5e7eb"/><rect x="29" y="4" width="22" height="20" rx="2" fill="#e5e7eb"/><rect x="54" y="4" width="22" height="20" rx="2" fill="#e5e7eb"/><rect x="4" y="28" width="22" height="20" rx="2" fill="#e5e7eb"/><rect x="29" y="28" width="22" height="20" rx="2" fill="#e5e7eb"/><rect x="54" y="28" width="22" height="20" rx="2" fill="#e5e7eb"/></svg>`,
        'features_alternating': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="30" height="18" fill="#e5e7eb"/><rect x="38" y="6" width="38" height="4" fill="#3b82f6"/><rect x="38" y="12" width="30" height="3" fill="#9ca3af"/><rect x="4" y="30" width="38" height="4" fill="#3b82f6"/><rect x="4" y="36" width="30" height="3" fill="#9ca3af"/><rect x="46" y="28" width="30" height="18" fill="#e5e7eb"/></svg>`,
        'benefits_list': `<svg viewBox="0 0 80 50"><circle cx="10" cy="10" r="4" fill="#3b82f6"/><rect x="20" y="8" width="56" height="4" fill="#e5e7eb"/><circle cx="10" cy="25" r="4" fill="#3b82f6"/><rect x="20" y="23" width="56" height="4" fill="#e5e7eb"/><circle cx="10" cy="40" r="4" fill="#3b82f6"/><rect x="20" y="38" width="56" height="4" fill="#e5e7eb"/></svg>`,
        'content_showcase': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="45" height="42" rx="2" fill="#e5e7eb"/><rect x="54" y="4" width="22" height="8" fill="#3b82f6"/><rect x="54" y="16" width="22" height="3" fill="#9ca3af"/><rect x="54" y="22" width="18" height="3" fill="#9ca3af"/><rect x="54" y="28" width="20" height="3" fill="#9ca3af"/><rect x="54" y="38" width="16" height="6" rx="2" fill="#3b82f6"/></svg>`,

        // Social Proof - PHP pattern names
        'testimonial_spotlight': `<svg viewBox="0 0 80 50"><rect x="8" y="6" width="64" height="32" rx="4" fill="#f3f4f6"/><circle cx="40" cy="16" r="6" fill="#e5e7eb"/><rect x="25" y="26" width="30" height="3" fill="#9ca3af"/><rect x="28" y="32" width="24" height="2" fill="#d1d5db"/><circle cx="32" cy="44" r="2" fill="#3b82f6"/><circle cx="40" cy="44" r="2" fill="#d1d5db"/><circle cx="48" cy="44" r="2" fill="#d1d5db"/></svg>`,
        'trust_metrics': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="72" height="42" fill="#3b82f6" opacity="0.1"/><text x="15" y="28" font-size="14" font-weight="bold" fill="#3b82f6">99%</text><text x="40" y="28" font-size="14" font-weight="bold" fill="#3b82f6">50K</text><text x="64" y="28" font-size="14" font-weight="bold" fill="#3b82f6">24/7</text></svg>`,

        // Legacy names (aliases)
        'testimonial_carousel': `<svg viewBox="0 0 80 50"><rect x="8" y="6" width="64" height="32" rx="4" fill="#f3f4f6"/><circle cx="40" cy="16" r="6" fill="#e5e7eb"/><rect x="25" y="26" width="30" height="3" fill="#9ca3af"/><rect x="28" y="32" width="24" height="2" fill="#d1d5db"/><circle cx="32" cy="44" r="2" fill="#3b82f6"/><circle cx="40" cy="44" r="2" fill="#d1d5db"/><circle cx="48" cy="44" r="2" fill="#d1d5db"/></svg>`,
        'testimonial_grid': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="22" height="42" rx="2" fill="#f3f4f6"/><rect x="29" y="4" width="22" height="42" rx="2" fill="#f3f4f6"/><rect x="54" y="4" width="22" height="42" rx="2" fill="#f3f4f6"/><circle cx="15" cy="12" r="4" fill="#e5e7eb"/><circle cx="40" cy="12" r="4" fill="#e5e7eb"/><circle cx="65" cy="12" r="4" fill="#e5e7eb"/></svg>`,
        'logos_strip': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="72" height="8" fill="#f3f4f6"/><rect x="4" y="20" width="14" height="14" rx="2" fill="#e5e7eb"/><rect x="22" y="20" width="14" height="14" rx="2" fill="#e5e7eb"/><rect x="40" y="20" width="14" height="14" rx="2" fill="#e5e7eb"/><rect x="58" y="20" width="14" height="14" rx="2" fill="#e5e7eb"/></svg>`,
        'stats_bar': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="72" height="42" fill="#3b82f6" opacity="0.1"/><text x="15" y="28" font-size="14" font-weight="bold" fill="#3b82f6">99%</text><text x="40" y="28" font-size="14" font-weight="bold" fill="#3b82f6">50K</text><text x="64" y="28" font-size="14" font-weight="bold" fill="#3b82f6">24/7</text></svg>`,

        // Pricing - PHP pattern names
        'pricing_tiered': `<svg viewBox="0 0 80 50"><rect x="4" y="8" width="22" height="38" rx="2" fill="#f3f4f6"/><rect x="29" y="2" width="22" height="46" rx="2" fill="#3b82f6" opacity="0.2"/><rect x="31" y="0" width="18" height="4" fill="#3b82f6"/><rect x="54" y="8" width="22" height="38" rx="2" fill="#f3f4f6"/></svg>`,
        'pricing_comparison': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="72" height="42" fill="#f3f4f6"/><rect x="8" y="8" width="64" height="6" fill="#e5e7eb"/><rect x="8" y="18" width="64" height="4" fill="#e5e7eb"/><rect x="8" y="26" width="64" height="4" fill="#e5e7eb"/><rect x="8" y="34" width="64" height="4" fill="#e5e7eb"/></svg>`,

        // Legacy names
        'pricing_single': `<svg viewBox="0 0 80 50"><rect x="20" y="4" width="40" height="42" rx="4" fill="#f3f4f6"/><text x="40" y="20" font-size="12" font-weight="bold" fill="#3b82f6" text-anchor="middle">$99</text><rect x="28" y="28" width="24" height="3" fill="#e5e7eb"/><rect x="28" y="34" width="24" height="3" fill="#e5e7eb"/><rect x="30" y="40" width="20" height="4" rx="1" fill="#3b82f6"/></svg>`,

        // Interaction - PHP pattern names
        'faq_expandable': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="72" height="12" rx="2" fill="#f3f4f6"/><text x="70" y="12" font-size="10" fill="#3b82f6">+</text><rect x="4" y="20" width="72" height="12" rx="2" fill="#e5e7eb"/><text x="70" y="28" font-size="10" fill="#3b82f6">−</text><rect x="4" y="36" width="72" height="12" rx="2" fill="#f3f4f6"/><text x="70" y="44" font-size="10" fill="#3b82f6">+</text></svg>`,
        'tabbed_content': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="20" height="8" fill="#3b82f6"/><rect x="26" y="4" width="20" height="8" fill="#e5e7eb"/><rect x="48" y="4" width="20" height="8" fill="#e5e7eb"/><rect x="4" y="14" width="72" height="32" rx="2" fill="#f3f4f6"/></svg>`,

        // Legacy names
        'faq_accordion': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="72" height="12" rx="2" fill="#f3f4f6"/><text x="70" y="12" font-size="10" fill="#3b82f6">+</text><rect x="4" y="20" width="72" height="12" rx="2" fill="#e5e7eb"/><text x="70" y="28" font-size="10" fill="#3b82f6">−</text><rect x="4" y="36" width="72" height="12" rx="2" fill="#f3f4f6"/><text x="70" y="44" font-size="10" fill="#3b82f6">+</text></svg>`,
        'process_steps': `<svg viewBox="0 0 80 50"><circle cx="15" cy="25" r="8" fill="#3b82f6"/><text x="15" y="28" font-size="8" fill="#fff" text-anchor="middle">1</text><line x1="23" y1="25" x2="32" y2="25" stroke="#3b82f6" stroke-width="2"/><circle cx="40" cy="25" r="8" fill="#3b82f6"/><text x="40" y="28" font-size="8" fill="#fff" text-anchor="middle">2</text><line x1="48" y1="25" x2="57" y2="25" stroke="#3b82f6" stroke-width="2"/><circle cx="65" cy="25" r="8" fill="#3b82f6"/><text x="65" y="28" font-size="8" fill="#fff" text-anchor="middle">3</text></svg>`,

        // Transitional - PHP pattern names
        'breathing_space': `<svg viewBox="0 0 80 50"><rect x="20" y="20" width="40" height="10" fill="#f3f4f6"/></svg>`,
        'visual_bridge': `<svg viewBox="0 0 80 50"><line x1="10" y1="25" x2="70" y2="25" stroke="#3b82f6" stroke-width="2"/><polygon points="60,20 70,25 60,30" fill="#3b82f6"/></svg>`,

        // Legacy names
        'section_divider': `<svg viewBox="0 0 80 50"><line x1="10" y1="25" x2="70" y2="25" stroke="#e5e7eb" stroke-width="2"/><circle cx="40" cy="25" r="4" fill="#3b82f6"/></svg>`,
        'quote_break': `<svg viewBox="0 0 80 50"><text x="40" y="20" font-size="24" fill="#3b82f6" text-anchor="middle">"</text><rect x="15" y="28" width="50" height="3" fill="#9ca3af"/><rect x="20" y="35" width="40" height="3" fill="#9ca3af"/></svg>`,

        // Closure - PHP pattern names
        'final_cta': `<svg viewBox="0 0 80 50"><rect x="4" y="8" width="72" height="34" rx="4" fill="#3b82f6"/><rect x="15" y="16" width="50" height="5" fill="#fff"/><rect x="20" y="26" width="40" height="3" fill="rgba(255,255,255,0.7)"/><rect x="30" y="34" width="20" height="6" rx="2" fill="#fff"/></svg>`,
        'contact_gateway': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="35" height="42" fill="#f3f4f6"/><rect x="8" y="12" width="27" height="4" fill="#e5e7eb"/><rect x="8" y="20" width="27" height="4" fill="#e5e7eb"/><rect x="8" y="28" width="27" height="10" fill="#e5e7eb"/><rect x="8" y="40" width="20" height="4" rx="1" fill="#3b82f6"/><rect x="44" y="4" width="32" height="42" fill="#e5e7eb"/></svg>`,

        // Legacy names
        'cta_banner': `<svg viewBox="0 0 80 50"><rect x="4" y="8" width="72" height="34" rx="4" fill="#3b82f6"/><rect x="15" y="16" width="50" height="5" fill="#fff"/><rect x="20" y="26" width="40" height="3" fill="rgba(255,255,255,0.7)"/><rect x="30" y="34" width="20" height="6" rx="2" fill="#fff"/></svg>`,
        'contact_split': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="35" height="42" fill="#f3f4f6"/><rect x="8" y="12" width="27" height="4" fill="#e5e7eb"/><rect x="8" y="20" width="27" height="4" fill="#e5e7eb"/><rect x="8" y="28" width="27" height="10" fill="#e5e7eb"/><rect x="8" y="40" width="20" height="4" rx="1" fill="#3b82f6"/><rect x="44" y="4" width="32" height="42" fill="#e5e7eb"/></svg>`,
        'footer_comprehensive': `<svg viewBox="0 0 80 50"><rect x="4" y="4" width="72" height="36" fill="#1e293b"/><rect x="8" y="10" width="14" height="20" fill="#334155"/><rect x="26" y="10" width="14" height="20" fill="#334155"/><rect x="44" y="10" width="14" height="20" fill="#334155"/><rect x="62" y="10" width="14" height="20" fill="#334155"/><rect x="4" y="42" width="72" height="6" fill="#0f172a"/></svg>`,
        'newsletter_inline': `<svg viewBox="0 0 80 50"><rect x="4" y="15" width="40" height="5" fill="#3b82f6"/><rect x="4" y="24" width="32" height="3" fill="#9ca3af"/><rect x="48" y="16" width="24" height="8" rx="2" fill="#e5e7eb"/><rect x="50" y="18" width="12" height="4" fill="#9ca3af"/><rect x="64" y="17" width="6" height="6" rx="1" fill="#3b82f6"/></svg>`
    };

    // Tension level indicators
    const TENSION_COLORS = {
        3: '#ef4444', // HIGH - red
        2: '#f59e0b', // MEDIUM - amber
        1: '#10b981'  // LOW - green
    };

    // ========================================
    // Initialization
    // ========================================

    let initialized = false;

    function init(options = {}) {
        if (initialized) return;

        JTB_AI.panel = document.getElementById('jtb-ai-panel');
        JTB_AI.previewModal = document.getElementById('jtb-ai-preview-modal');
        JTB_AI.patternModal = document.getElementById('jtb-ai-pattern-modal');
        JTB_AI.toggleBtn = document.getElementById('jtb-ai-toggle');

        // CSRF token: prefer passed option, then hidden input, then JTB config
        JTB_AI.csrfToken = options.csrfToken || document.getElementById('jtb-ai-csrf')?.value || JTB?.config?.csrfToken || '';
        JTB_AI.pageId = options.pageId || parseInt(document.body.dataset.pageId || 0);

        // Template mode support (for Theme Builder)
        JTB_AI.mode = options.mode || 'page';
        JTB_AI.templateType = options.templateType || null;
        JTB_AI.templateId = options.templateId || null;

        // Update UI for template mode
        if (JTB_AI.mode === 'template' && JTB_AI.templateType) {
            updateUIForTemplateMode();
        }

        // Store API URL if provided
        if (options.apiUrl) {
            JTB_AI.apiUrl = options.apiUrl;
        }

        if (!JTB_AI.panel) {
            // Panel element not found - this is OK for website-builder which uses MultiAgent modal
            // Only warn if we're in page-builder context
            if (document.getElementById('jtb-page-builder')) {
                console.warn('JTB AI Panel: Panel element not found');
            }
            return;
        }

        bindEvents();
        loadPatterns();
        initStyleLock(); // Stage 22: Initialize Style Lock state
        initProviderSelection(); // Initialize AI provider/model selection
        initialized = true;
        // console.log removed
    }

    // ========================================
    // Template Mode Support
    // ========================================

    function updateUIForTemplateMode() {
        // Update panel title
        const titleEl = JTB_AI.panel?.querySelector('.jtb-ai-panel-title span');
        if (titleEl) {
            titleEl.textContent = 'AI ' + capitalizeFirst(JTB_AI.templateType) + ' Generator';
        }

        // Update prompt placeholder based on template type
        const promptEl = document.getElementById('jtb-ai-prompt');
        if (promptEl) {
            const placeholders = {
                'header': 'e.g., Modern header with logo on left, centered navigation, and search icon on right',
                'footer': 'e.g., Multi-column footer with logo, links, newsletter signup, and social icons',
                'body': 'e.g., Blog post layout with featured image, title, content, author box, and related posts',
                'single': 'e.g., Product page with gallery, description, specs, reviews, and add to cart',
                'archive': 'e.g., Grid layout for blog posts with filters, pagination, and sidebar',
                '404': 'e.g., Creative 404 page with illustration, message, and navigation back home',
                'search': 'e.g., Search results page with filters, result cards, and pagination'
            };
            promptEl.placeholder = placeholders[JTB_AI.templateType] || 'Describe your template...';
        }

        // Hide "Page Type" section in compose tab (not relevant for templates)
        const pageTypeSection = document.querySelector('.jtb-ai-field-group:has(#jtb-ai-page-type)');
        if (pageTypeSection) {
            pageTypeSection.style.display = 'none';
        }

        // Update generate button text
        const generateBtnText = document.getElementById('jtb-ai-generate-btn-text');
        if (generateBtnText) {
            generateBtnText.textContent = 'Generate ' + capitalizeFirst(JTB_AI.templateType);
        }
    }

    function capitalizeFirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    /**
     * Collect template-specific options from the AI panel form
     * Used by ai-panel-template.php
     */
    function collectTemplateOptions() {
        const options = {
            prompt: document.getElementById('jtb-ai-template-prompt')?.value?.trim() || '',
            style: document.getElementById('jtb-ai-template-style')?.value || 'modern',
            industry: document.getElementById('jtb-ai-template-industry')?.value || 'general',
            templateStyle: getSelectedIntent() || 'classic'
        };

        // Get template type specific options
        if (JTB_AI.templateType === 'header') {
            options.header = {
                logoPosition: document.getElementById('jtb-ai-header-logo')?.value || 'left',
                navStyle: document.getElementById('jtb-ai-header-nav')?.value || 'horizontal',
                sticky: document.getElementById('jtb-ai-header-sticky')?.checked || false,
                search: document.getElementById('jtb-ai-header-search')?.checked || false,
                cta: document.getElementById('jtb-ai-header-cta')?.checked || false,
                social: document.getElementById('jtb-ai-header-social')?.checked || false,
                topbar: document.getElementById('jtb-ai-header-topbar')?.checked || false
            };
        } else if (JTB_AI.templateType === 'footer') {
            options.footer = {
                columns: document.getElementById('jtb-ai-footer-columns')?.value || '4',
                background: document.getElementById('jtb-ai-footer-bg')?.value || 'dark',
                logo: document.getElementById('jtb-ai-footer-logo')?.checked || false,
                menu: document.getElementById('jtb-ai-footer-menu')?.checked || false,
                social: document.getElementById('jtb-ai-footer-social')?.checked || false,
                newsletter: document.getElementById('jtb-ai-footer-newsletter')?.checked || false,
                contact: document.getElementById('jtb-ai-footer-contact')?.checked || false,
                copyright: document.getElementById('jtb-ai-footer-copyright')?.checked || false
            };
        } else if (JTB_AI.templateType === 'body') {
            options.body = {
                layout: document.getElementById('jtb-ai-body-layout')?.value || 'sidebar_right',
                featuredImage: document.getElementById('jtb-ai-body-featured')?.value || 'large',
                title: document.getElementById('jtb-ai-body-title')?.checked || false,
                meta: document.getElementById('jtb-ai-body-meta')?.checked || false,
                content: document.getElementById('jtb-ai-body-content')?.checked || false,
                author: document.getElementById('jtb-ai-body-author')?.checked || false,
                related: document.getElementById('jtb-ai-body-related')?.checked || false,
                comments: document.getElementById('jtb-ai-body-comments')?.checked || false,
                breadcrumbs: document.getElementById('jtb-ai-body-breadcrumbs')?.checked || false,
                navigation: document.getElementById('jtb-ai-body-navigation')?.checked || false
            };
        }

        return options;
    }

    /**
     * Build enhanced prompt for template generation
     */
    function buildTemplatePrompt(options) {
        let prompt = options.prompt;

        if (JTB_AI.templateType === 'header' && options.header) {
            const h = options.header;
            prompt += `\n\nHEADER REQUIREMENTS:
- Style: ${options.templateStyle}
- Logo position: ${h.logoPosition}
- Navigation: ${h.navStyle}
- Features: ${[
    h.sticky ? 'sticky' : '',
    h.search ? 'search icon' : '',
    h.cta ? 'CTA button' : '',
    h.social ? 'social icons' : '',
    h.topbar ? 'top bar with contact' : ''
].filter(Boolean).join(', ') || 'none'}`;
        } else if (JTB_AI.templateType === 'footer' && options.footer) {
            const f = options.footer;
            prompt += `\n\nFOOTER REQUIREMENTS:
- Style: ${options.templateStyle}
- Columns: ${f.columns}
- Background: ${f.background}
- Elements: ${[
    f.logo ? 'logo' : '',
    f.menu ? 'navigation links' : '',
    f.social ? 'social icons' : '',
    f.newsletter ? 'newsletter signup' : '',
    f.contact ? 'contact info' : '',
    f.copyright ? 'copyright bar' : ''
].filter(Boolean).join(', ') || 'none'}`;
        } else if (JTB_AI.templateType === 'body' && options.body) {
            const b = options.body;
            prompt += `\n\nBODY TEMPLATE REQUIREMENTS:
- Template type: ${options.templateStyle}
- Layout: ${b.layout}
- Featured image: ${b.featuredImage}
- Elements: ${[
    b.title ? 'post title' : '',
    b.meta ? 'post meta' : '',
    b.content ? 'post content' : '',
    b.author ? 'author box' : '',
    b.related ? 'related posts' : '',
    b.comments ? 'comments' : '',
    b.breadcrumbs ? 'breadcrumbs' : '',
    b.navigation ? 'post navigation' : ''
].filter(Boolean).join(', ') || 'none'}`;
        }

        prompt += `\n\nVISUAL STYLE: ${options.style}`;
        prompt += `\nINDUSTRY: ${options.industry}`;

        return prompt;
    }

    /**
     * Get selected intent from intent buttons (for template styles)
     */
    function getSelectedIntent() {
        const activeBtn = document.querySelector('.jtb-ai-intent-btn.active');
        return activeBtn?.dataset?.intent || null;
    }

    // ========================================
    // AI Provider/Model Selection
    // ========================================

    function initProviderSelection() {
        const providerSelect = document.getElementById('jtb-ai-provider');
        const modelSelect = document.getElementById('jtb-ai-model');

        if (!providerSelect || !modelSelect) return;

        // Set initial values from state
        providerSelect.value = JTB_AI.aiProvider;
        updateModelOptions(JTB_AI.aiProvider);
        modelSelect.value = JTB_AI.aiModel;

        // Sync state when model changes
        modelSelect.addEventListener('change', function() {
            JTB_AI.aiModel = this.value;
            // console.log removed
        });
    }

    function handleProviderChange(provider) {
        JTB_AI.aiProvider = provider;
        updateModelOptions(provider);

        // Set to provider's default model
        const providerConfig = JTB_AI.availableProviders[provider];
        if (providerConfig) {
            JTB_AI.aiModel = providerConfig.default;
            const modelSelect = document.getElementById('jtb-ai-model');
            if (modelSelect) {
                modelSelect.value = JTB_AI.aiModel;
            }
        }

        // Show/hide access mode field (only for Anthropic)
        const accessModeField = document.getElementById('jtb-ai-access-mode-field');
        if (accessModeField) {
            accessModeField.style.display = (provider === 'anthropic') ? 'block' : 'none';
        }

        // console.log removed
    }

    /**
     * Handle access mode change (API vs Max Pro)
     */
    function handleAccessModeChange(mode) {
        JTB_AI.accessMode = mode;

        const hintEl = document.getElementById('jtb-ai-access-mode-hint');
        if (hintEl) {
            if (mode === 'max_pro') {
                hintEl.innerHTML = '⚡ Max Pro mode uses your claude.ai subscription. Make sure Claude Code CLI is configured with Max Pro.';
            } else {
                hintEl.innerHTML = 'API mode uses your Anthropic API key. Pay-per-use pricing applies.';
            }
        }

        // console.log removed
    }

    function updateModelOptions(provider) {
        const modelSelect = document.getElementById('jtb-ai-model');
        if (!modelSelect) return;

        const providerConfig = JTB_AI.availableProviders[provider];
        if (!providerConfig) return;

        // Clear existing options
        modelSelect.innerHTML = '';

        // Add new options
        Object.entries(providerConfig.models).forEach(([value, label]) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            if (value === providerConfig.default) {
                option.selected = true;
            }
            modelSelect.appendChild(option);
        });
    }

    // ========================================
    // Event Listener Tracking (Memory Leak Prevention)
    // ========================================

    /**
     * Add event listener with tracking for cleanup
     * @param {Element} element - DOM element
     * @param {string} event - Event name
     * @param {Function} handler - Event handler
     * @param {Object} options - Event options
     */
    function addTrackedListener(element, event, handler, options) {
        if (!element) return;
        element.addEventListener(event, handler, options);
        JTB_AI._trackedListeners.push({ element, event, handler, options });
    }

    /**
     * Remove all tracked event listeners
     * Call this when panel is closed/destroyed
     */
    function removeAllTrackedListeners() {
        JTB_AI._trackedListeners.forEach(({ element, event, handler, options }) => {
            if (element) {
                element.removeEventListener(event, handler, options);
            }
        });
        JTB_AI._trackedListeners = [];
    }

    // ========================================
    // Event Binding
    // ========================================

    function bindEvents() {
        // Clear previous listeners first (in case of re-init)
        removeAllTrackedListeners();

        // Toggle button (FAB)
        addTrackedListener(JTB_AI.toggleBtn, 'click', togglePanel);

        // Panel controls
        addTrackedListener(JTB_AI.panel.querySelector('.jtb-ai-panel-close'), 'click', closePanel);
        addTrackedListener(JTB_AI.panel.querySelector('.jtb-ai-panel-overlay'), 'click', closePanel);
        addTrackedListener(document.getElementById('jtb-ai-cancel-btn'), 'click', closePanel);

        // Generate button (Page Builder)
        addTrackedListener(document.getElementById('jtb-ai-generate-btn'), 'click', handleGenerate);

        // Generate button (Template Builder - from ai-panel-template.php)
        addTrackedListener(document.getElementById('jtb-ai-generate-template-btn'), 'click', handleGenerateTemplate);

        // Template preview modal buttons
        addTrackedListener(document.getElementById('jtb-ai-regenerate-btn'), 'click', () => {
            closePreviewModal();
            handleGenerateTemplate();
        });
        addTrackedListener(document.getElementById('jtb-ai-apply-btn'), 'click', insertLayout);

        // Tabs
        JTB_AI.panel.querySelectorAll('.jtb-ai-tab').forEach(tab => {
            const handler = () => switchTab(tab.dataset.tab);
            addTrackedListener(tab, 'click', handler);
        });

        // Compose tab - Intent buttons
        JTB_AI.panel.querySelectorAll('.jtb-ai-intent-btn').forEach(btn => {
            const handler = () => selectIntent(btn.dataset.intent);
            addTrackedListener(btn, 'click', handler);
        });

        // Compose tab - Style buttons
        JTB_AI.panel.querySelectorAll('.jtb-ai-style-btn').forEach(btn => {
            const handler = () => selectStyle(btn.dataset.style);
            addTrackedListener(btn, 'click', handler);
        });

        // Patterns tab - Category filter
        JTB_AI.panel.querySelectorAll('.jtb-ai-category-btn').forEach(btn => {
            const handler = () => filterPatterns(btn.dataset.category);
            addTrackedListener(btn, 'click', handler);
        });

        // Section buttons (legacy)
        JTB_AI.panel.querySelectorAll('.jtb-ai-section-btn').forEach(btn => {
            const handler = () => selectSection(btn.dataset.section);
            addTrackedListener(btn, 'click', handler);
        });

        // Advanced Options toggle
        const advancedBtn = document.getElementById('jtb-ai-advanced-btn');
        const advancedOptions = document.getElementById('jtb-ai-advanced-options');
        if (advancedBtn && advancedOptions) {
            const advancedHandler = () => {
                const isVisible = advancedOptions.style.display !== 'none';
                advancedOptions.style.display = isVisible ? 'none' : 'block';
                advancedBtn.classList.toggle('expanded', !isVisible);
            };
            addTrackedListener(advancedBtn, 'click', advancedHandler);
        }

        // Preview modal
        addTrackedListener(JTB_AI.previewModal?.querySelector('.jtb-ai-modal-close'), 'click', closePreviewModal);
        addTrackedListener(JTB_AI.previewModal?.querySelector('.jtb-ai-modal-overlay'), 'click', closePreviewModal);
        addTrackedListener(document.getElementById('jtb-ai-preview-cancel'), 'click', closePreviewModal);
        const regenerateHandler = () => {
            closePreviewModal();
            handleGenerate();
        };
        addTrackedListener(document.getElementById('jtb-ai-preview-regenerate'), 'click', regenerateHandler);
        addTrackedListener(document.getElementById('jtb-ai-preview-insert'), 'click', insertLayout);

        // Pattern modal
        addTrackedListener(JTB_AI.patternModal?.querySelector('.jtb-ai-modal-close'), 'click', closePatternModal);
        addTrackedListener(JTB_AI.patternModal?.querySelector('.jtb-ai-modal-overlay'), 'click', closePatternModal);
        addTrackedListener(JTB_AI.patternModal?.querySelector('.jtb-ai-pattern-modal-close'), 'click', closePatternModal);
        addTrackedListener(document.getElementById('jtb-ai-pattern-modal-insert'), 'click', insertSinglePattern);

        // DEV_MODE: Debug toggle (only exists if DEV_MODE is true)
        const debugToggle = document.getElementById('jtb-ai-debug-mode');
        if (debugToggle && window.JTB_DEV_MODE) {
            const debugHandler = (e) => {
                JTB_AI.debugMode = e.target.checked;
                applyDebugOverlays();
            };
            addTrackedListener(debugToggle, 'change', debugHandler);
        }

        // Keyboard shortcuts (tracked for cleanup)
        const escapeHandler = (e) => {
            if (e.key === 'Escape') {
                if (JTB_AI.patternModal?.classList.contains('is-open')) {
                    closePatternModal();
                } else if (JTB_AI.previewModal?.style.display !== 'none') {
                    closePreviewModal();
                } else if (JTB_AI.panel?.classList.contains('is-open')) {
                    closePanel();
                }
            }
        };
        addTrackedListener(document, 'keydown', escapeHandler);
    }

    // ========================================
    // Load Patterns from API
    // ========================================

    async function loadPatterns() {
        try {
            const response = await fetch('/api/jtb/ai/get-patterns', {
                headers: {
                    'X-CSRF-Token': JTB_AI.csrfToken
                }
            });

            const data = await response.json();

            if (data.ok) {
                JTB_AI.patterns = data.patterns;
                JTB_AI.patternInfo = data.pattern_info;
                JTB_AI.compositionSequences = data.composition_sequences || {};
                renderPatternGrid();
            }
        } catch (error) {
            console.error('Failed to load patterns:', error);
        }
    }

    // ========================================
    // Panel Controls
    // ========================================

    function togglePanel() {
        if (JTB_AI.panel?.classList.contains('is-open')) {
            closePanel();
        } else {
            openPanel();
        }
    }

    function openPanel(options = {}) {
        if (!JTB_AI.panel) return;

        JTB_AI.panel.classList.add('is-open');
        JTB_AI.toggleBtn?.classList.add('is-active');
        document.body.classList.add('jtb-ai-panel-open');

        if (options.pageId) {
            JTB_AI.pageId = options.pageId;
        }

        if (options.tab) {
            switchTab(options.tab);
        }

        // Auto-select first intent button if none selected
        if (!JTB_AI.selectedIntent) {
            const firstIntent = JTB_AI.panel.querySelector('.jtb-ai-intent-btn');
            if (firstIntent && firstIntent.dataset.intent) {
                selectIntent(firstIntent.dataset.intent);
                // console.log removed
            }
        }
    }

    function closePanel() {
        if (!JTB_AI.panel) return;

        JTB_AI.panel.classList.remove('is-open');
        JTB_AI.toggleBtn?.classList.remove('is-active');
        document.body.classList.remove('jtb-ai-panel-open');
        JTB_AI.isGenerating = false;
        hideProgress();

        // Cleanup tracked event listeners to prevent memory leaks
        // Note: We don't call removeAllTrackedListeners() here because
        // panel might be reopened. Listeners are cleaned up on re-init
        // or page unload.
    }

    function switchTab(tabName) {
        JTB_AI.currentTab = tabName;

        // Update tab buttons
        JTB_AI.panel.querySelectorAll('.jtb-ai-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.tab === tabName);
        });

        // Update tab content
        JTB_AI.panel.querySelectorAll('.jtb-ai-tab-content').forEach(content => {
            content.classList.toggle('active', content.dataset.tab === tabName);
        });

        // Update generate button text
        const btnText = document.getElementById('jtb-ai-generate-btn-text');
        if (btnText) {
            const labels = {
                'compose': 'Compose Page',
                'patterns': 'Insert Pattern',
                'section': 'Add Section',
                'generate': 'Generate Layout'
            };
            btnText.textContent = labels[tabName] || 'Generate';
        }
    }

    // ========================================
    // Compose Tab - Intent & Style Selection
    // ========================================

    function selectIntent(intent) {
        JTB_AI.selectedIntent = intent;

        // Visual feedback - toggle 'active' class (not 'selected')
        JTB_AI.panel.querySelectorAll('.jtb-ai-intent-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.intent === intent);
        });

        // Update composition preview
        updateCompositionPreview();
    }

    function selectStyle(style) {
        JTB_AI.selectedStyle = style;

        // Visual feedback
        JTB_AI.panel.querySelectorAll('.jtb-ai-style-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.style === style);
        });
    }

    function updateCompositionPreview() {
        const previewEl = document.getElementById('jtb-ai-composition-preview');
        if (!previewEl) return;

        if (!JTB_AI.selectedIntent) {
            previewEl.innerHTML = `
                <div class="jtb-ai-composition-empty">
                    Select a page type to see the composition
                </div>
            `;
            return;
        }

        // Get pattern sequence from API data
        const sequenceData = JTB_AI.compositionSequences[JTB_AI.selectedIntent] || [];

        // Extract just pattern names for preview
        const sequence = sequenceData.map(item => item.pattern);
        JTB_AI.compositionPreview = sequenceData;

        let html = '<div class="jtb-ai-composition-flow">';

        sequenceData.forEach((item, index) => {
            const patternName = item.pattern;
            const variant = item.variant || 'default';
            const purpose = item.purpose || '';
            const info = JTB_AI.patternInfo[patternName] || {};
            const tension = info.tension || 2;
            const icon = PATTERN_ICONS[patternName] || '';

            html += `
                <div class="jtb-ai-composition-item" data-pattern="${patternName}" data-variant="${variant}" title="${purpose ? 'Purpose: ' + purpose : ''}">
                    <div class="jtb-ai-composition-icon">${icon}</div>
                    <div class="jtb-ai-composition-label">${formatPatternName(patternName)}</div>
                    <div class="jtb-ai-composition-tension" style="background:${TENSION_COLORS[tension]}" title="Tension: ${['', 'Low', 'Medium', 'High'][tension]}"></div>
                </div>
            `;

            // Add connector except for last item
            if (index < sequenceData.length - 1) {
                html += '<div class="jtb-ai-composition-connector"></div>';
            }
        });

        html += '</div>';
        html += `<div class="jtb-ai-composition-summary">${sequenceData.length} patterns selected</div>`;

        previewEl.innerHTML = html;
    }

    // ========================================
    // Patterns Tab - Grid & Selection
    // ========================================

    function renderPatternGrid(filterCategory = 'all') {
        const gridEl = document.getElementById('jtb-ai-pattern-grid');
        if (!gridEl) return;

        let html = '';

        for (const [category, patterns] of Object.entries(JTB_AI.patterns)) {
            if (filterCategory !== 'all' && category !== filterCategory) continue;

            for (const [patternName, variants] of Object.entries(patterns)) {
                const info = JTB_AI.patternInfo[patternName] || {};
                const icon = PATTERN_ICONS[patternName] || '';
                const tension = info.tension || 2;

                html += `
                    <div class="jtb-ai-pattern-card" data-pattern="${patternName}" data-category="${category}">
                        <div class="jtb-ai-pattern-preview">${icon}</div>
                        <div class="jtb-ai-pattern-info">
                            <div class="jtb-ai-pattern-name">${formatPatternName(patternName)}</div>
                            <div class="jtb-ai-pattern-meta">
                                <span class="jtb-ai-pattern-variants">${variants.length} variants</span>
                                <span class="jtb-ai-pattern-tension" style="background:${TENSION_COLORS[tension]}"></span>
                            </div>
                        </div>
                    </div>
                `;
            }
        }

        if (!html) {
            html = '<div class="jtb-ai-pattern-empty">No patterns found</div>';
        }

        gridEl.innerHTML = html;

        // Bind click events
        gridEl.querySelectorAll('.jtb-ai-pattern-card').forEach(card => {
            card.addEventListener('click', () => selectPattern(card.dataset.pattern));
        });
    }

    function filterPatterns(category) {
        // Update category buttons
        JTB_AI.panel.querySelectorAll('.jtb-ai-category-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.category === category);
        });

        renderPatternGrid(category);
    }

    function selectPattern(patternName) {
        JTB_AI.selectedPattern = patternName;
        JTB_AI.selectedVariant = null;

        // Visual feedback
        JTB_AI.panel.querySelectorAll('.jtb-ai-pattern-card').forEach(card => {
            card.classList.toggle('selected', card.dataset.pattern === patternName);
        });

        // Show variant selection
        showVariantSelection(patternName);

        // Show pattern info modal
        showPatternInfoModal(patternName);
    }

    function showVariantSelection(patternName) {
        const section = document.getElementById('jtb-ai-variant-section');
        const grid = document.getElementById('jtb-ai-variant-grid');
        if (!section || !grid) return;

        const info = JTB_AI.patternInfo[patternName];
        if (!info || !info.variants || info.variants.length === 0) {
            section.style.display = 'none';
            return;
        }

        section.style.display = 'block';

        let html = '';
        info.variants.forEach((variant, index) => {
            const isDefault = index === 0;
            html += `
                <button type="button" class="jtb-ai-variant-btn ${isDefault ? 'active' : ''}" data-variant="${variant}">
                    ${formatPatternName(variant)}
                </button>
            `;
        });

        grid.innerHTML = html;
        JTB_AI.selectedVariant = info.variants[0];

        // Bind events
        grid.querySelectorAll('.jtb-ai-variant-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                JTB_AI.selectedVariant = btn.dataset.variant;
                grid.querySelectorAll('.jtb-ai-variant-btn').forEach(b => {
                    b.classList.toggle('active', b.dataset.variant === btn.dataset.variant);
                });
            });
        });
    }

    function showPatternInfoModal(patternName) {
        if (!JTB_AI.patternModal) return;

        const info = JTB_AI.patternInfo[patternName] || {};
        const icon = PATTERN_ICONS[patternName] || '';
        const tension = info.tension || 2;
        const tensionLabel = ['', 'Low', 'Medium', 'High'][tension];

        document.getElementById('jtb-ai-pattern-modal-title').textContent = formatPatternName(patternName);

        const contentEl = document.getElementById('jtb-ai-pattern-modal-content');
        contentEl.innerHTML = `
            <div class="jtb-ai-pattern-modal-preview">${icon}</div>
            <div class="jtb-ai-pattern-modal-details">
                <div class="jtb-ai-pattern-modal-row">
                    <span class="jtb-ai-pattern-modal-label">Category:</span>
                    <span>${info.category || 'Unknown'}</span>
                </div>
                <div class="jtb-ai-pattern-modal-row">
                    <span class="jtb-ai-pattern-modal-label">Tension:</span>
                    <span class="jtb-ai-pattern-tension-badge" style="background:${TENSION_COLORS[tension]}">${tensionLabel}</span>
                </div>
                <div class="jtb-ai-pattern-modal-row">
                    <span class="jtb-ai-pattern-modal-label">Variants:</span>
                    <span>${(info.variants || []).length}</span>
                </div>
                ${info.description ? `<div class="jtb-ai-pattern-modal-desc">${info.description}</div>` : ''}
            </div>
            <div class="jtb-ai-pattern-modal-variants">
                <label>Select variant:</label>
                <div class="jtb-ai-variant-list">
                    ${(info.variants || ['default']).map((v, i) => `
                        <button type="button" class="jtb-ai-variant-chip ${i === 0 ? 'active' : ''}" data-variant="${v}">
                            ${formatPatternName(v)}
                        </button>
                    `).join('')}
                </div>
            </div>
        `;

        // Bind variant selection in modal
        contentEl.querySelectorAll('.jtb-ai-variant-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                JTB_AI.selectedVariant = chip.dataset.variant;
                contentEl.querySelectorAll('.jtb-ai-variant-chip').forEach(c => {
                    c.classList.toggle('active', c.dataset.variant === chip.dataset.variant);
                });
            });
        });

        JTB_AI.patternModal.classList.add('is-open');
        JTB_AI.patternModal.style.display = 'flex';
    }

    function closePatternModal() {
        if (!JTB_AI.patternModal) return;
        JTB_AI.patternModal.classList.remove('is-open');
        JTB_AI.patternModal.style.display = 'none';
    }

    // ========================================
    // Section Selection (Legacy)
    // ========================================

    function selectSection(sectionType) {
        JTB_AI.selectedSection = sectionType;

        JTB_AI.panel.querySelectorAll('.jtb-ai-section-btn').forEach(btn => {
            btn.classList.toggle('selected', btn.dataset.section === sectionType);
        });
    }

    // ========================================
    // Generation Handlers
    // ========================================

    async function handleGenerate() {
        switch (JTB_AI.currentTab) {
            case 'compose':
                await handleComposeLayout();
                break;
            case 'patterns':
                await handleGeneratePattern();
                break;
            case 'section':
                if (JTB_AI.selectedSection) {
                    await handleGenerateSection(JTB_AI.selectedSection);
                } else {
                    showError('Please select a section type first');
                }
                break;
            case 'generate':
                await handleGenerateLayout();
                break;
        }
    }

    async function handleComposeLayout() {
        JTB_AI.isGenerating = true;
        showProgress('Generating layout with AI...');

        // Get all form values
        const industry = document.getElementById('jtb-ai-compose-industry')?.value || 'technology';
        const styleDropdown = document.getElementById('jtb-ai-compose-style');
        const style = styleDropdown?.value || JTB_AI.selectedStyle || 'modern';
        const pageType = JTB_AI.selectedIntent || 'saas_landing';

        // Get business description from textarea (PRIMARY INPUT)
        const promptTextarea = document.getElementById('jtb-ai-compose-prompt');
        const businessDescription = promptTextarea?.value?.trim() || '';

        // DEBUG: Log all collected values
        // console.log removed
            businessDescription: businessDescription ? businessDescription.substring(0, 100) + '...' : '(empty)',
            industry: industry,
            style: style,
            pageType: pageType,
            selectedIntent: JTB_AI.selectedIntent,
            hasTextarea: !!promptTextarea,
            hasStyleDropdown: !!styleDropdown,
            hasIndustryDropdown: !!document.getElementById('jtb-ai-compose-industry')
        });

        // Validate: Business description is required for quality results
        if (!businessDescription) {
            console.warn('[AI Compose] No business description provided!');
            showError('Please describe your business to generate a personalized page.');
            JTB_AI.isGenerating = false;
            hideProgress();
            promptTextarea?.focus();
            return;
        }

        // Build rich prompt from business description + preferences
        const prompt = `
BUSINESS DESCRIPTION:
${businessDescription}

PAGE TYPE: ${pageType}
VISUAL STYLE: ${style}
INDUSTRY: ${industry}

Create a complete, professional landing page based on this business description.
The page should reflect the brand's unique value proposition, target audience, and services/products described above.
Use real, specific content based on the business description - not generic placeholder text.
`.trim();

        // DEBUG: Log the full prompt being sent
        // console.log removed
        // console.log removed

        try {
            // =============================================
            // UNIFIED AI ENDPOINT (2026-02-04)
            // Single endpoint for both templates and pages
            // Uses JTB_AI_Schema for REAL module schemas
            // =============================================
            const endpoint = '/api/jtb/ai/generate';

            // Unified request body - context determines template vs page
            const requestBody = {
                action: 'layout',
                prompt: prompt,
                context: {
                    type: JTB_AI.mode === 'template' ? 'template' : 'page',
                    template_type: JTB_AI.templateType || null,  // header, footer, body
                    id: JTB_AI.mode === 'template' ? JTB_AI.templateId : JTB_AI.pageId,
                    style: style,
                    industry: industry,
                    page_type: pageType
                },
                // Also send flat for backward compat
                style: style,
                industry: industry,
                page_type: pageType
            };

            // console.log removed

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': JTB_AI.csrfToken
                },
                body: JSON.stringify(requestBody)
            });

            // Parse JSON response
            const data = await response.json();

            if (!data.ok) {
                throw new Error(data.error || 'Generation failed');
            }

            // console.log removed
                sectionsCount: data.layout?.sections?.length || 0,
                provider: data.stats?.provider,
                timeMs: data.stats?.time_ms
            });

            // Build layout object for preview
            // Direct API returns { sections: [...] }
            const layout = {
                content: data.layout?.sections || [],
                sections: data.layout?.sections || []
            };

            // Stage 25: Use single source of truth
            const meta = {
                provider: data.stats?.provider,
                timeMs: data.stats?.time_ms,
                tokensUsed: data.stats?.tokens_used
            };
            setAiResult(layout, null, meta);
            showPreview(layout, meta);

        } catch (error) {
            console.error('Composition error:', error);
            showError(error.message || 'Failed to compose layout');
        } finally {
            JTB_AI.isGenerating = false;
            hideProgress();
        }
    }

    async function handleGeneratePattern() {
        if (!JTB_AI.selectedPattern) {
            showError('Please select a pattern first');
            return;
        }

        closePatternModal();
        JTB_AI.isGenerating = true;
        showProgress(`Generating ${formatPatternName(JTB_AI.selectedPattern)}...`);

        const style = document.getElementById('jtb-ai-pattern-style')?.value || 'modern';
        const variant = JTB_AI.selectedVariant || 'default';

        try {
            // Use dedicated generate-pattern endpoint with pattern_name and variant
            const response = await fetch('/api/jtb/ai/generate-pattern', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': JTB_AI.csrfToken
                },
                body: JSON.stringify({
                    pattern_name: JTB_AI.selectedPattern,
                    variant: variant,
                    style: style,
                    industry: JTB_AI.industry || 'technology'
                })
            });

            const data = await response.json();

            if (!data.ok) {
                throw new Error(data.error || 'Pattern generation failed');
            }

            // Insert the generated section directly
            if (data.section) {
                const section = ensureSectionStructure(data.section);
                insertSectionIntoBuilder(section);
                showSuccess(`${formatPatternName(JTB_AI.selectedPattern)} added!`);
                closePanel();
            } else {
                throw new Error('No section returned from pattern generation');
            }

        } catch (error) {
            console.error('Pattern generation error:', error);
            showError(error.message || 'Failed to generate pattern');
        } finally {
            JTB_AI.isGenerating = false;
            hideProgress();
        }
    }

    async function insertSinglePattern() {
        closePatternModal();
        await handleGeneratePattern();
    }

    async function handleGenerateSection(sectionType) {
        JTB_AI.isGenerating = true;
        showProgress(`Generating ${sectionType} section...`);

        const context = document.getElementById('jtb-ai-section-context')?.value || '';

        try {
            const response = await fetch('/api/jtb/ai/generate-section', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': JTB_AI.csrfToken
                },
                body: JSON.stringify({
                    section_type: sectionType,
                    prompt: context,
                    page_id: JTB_AI.pageId,
                    apply_branding: true
                })
            });

            const data = await response.json();

            if (!data.ok) {
                throw new Error(data.error || 'Section generation failed');
            }

            // Stage 25: Use single source of truth
            const layout = { sections: [data.section] };
            setAiResult(layout, data.quality || null, {});
            showPreview(layout);

        } catch (error) {
            console.error('Section generation error:', error);
            showError(error.message || 'Failed to generate section');
        } finally {
            JTB_AI.isGenerating = false;
            hideProgress();
        }
    }

    async function handleGenerateLayout() {
        const prompt = document.getElementById('jtb-ai-prompt')?.value?.trim();
        if (!prompt) {
            showError('Please describe the ' + (JTB_AI.mode === 'template' ? JTB_AI.templateType : 'page') + ' you want to create');
            return;
        }

        JTB_AI.isGenerating = true;
        const itemType = JTB_AI.mode === 'template' ? JTB_AI.templateType : 'layout';
        showProgress('Generating ' + itemType + '...');

        try {
            // UNIFIED AI ENDPOINT (2026-02-04)
            const endpoint = '/api/jtb/ai/generate';

            const requestBody = {
                action: 'layout',
                prompt,
                context: {
                    type: JTB_AI.mode === 'template' ? 'template' : 'page',
                    template_type: JTB_AI.templateType || null,
                    id: JTB_AI.mode === 'template' ? JTB_AI.templateId : JTB_AI.pageId,
                    style: JTB_AI.selectedStyle || 'modern',
                    industry: document.getElementById('jtb-ai-industry')?.value || ''
                },
                style: JTB_AI.selectedStyle || 'modern',
                industry: document.getElementById('jtb-ai-industry')?.value || ''
            };

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': JTB_AI.csrfToken
                },
                body: JSON.stringify(requestBody)
            });

            const data = await response.json();

            if (!data.ok && !data.success) {
                throw new Error(data.error || 'Generation failed');
            }

            // Handle template response format (may have 'content' instead of 'layout')
            const layout = data.layout || data.content || { sections: [] };

            // Normalize to expected format
            const normalizedLayout = layout.sections ? layout : { sections: Array.isArray(layout) ? layout : [layout] };

            // Stage 25: Use single source of truth
            const meta = { quality: data.quality };
            setAiResult(normalizedLayout, data.quality, meta);
            showPreview(normalizedLayout, meta);

        } catch (error) {
            console.error((JTB_AI.mode === 'template' ? 'Template' : 'Layout') + ' generation error:', error);
            showError(error.message || 'Failed to generate ' + itemType);
        } finally {
            JTB_AI.isGenerating = false;
            hideProgress();
        }
    }

    // ========================================
    // Template Generation (Theme Builder specific)
    // ========================================

    /**
     * Handle template generation from ai-panel-template.php
     * Uses template-specific options (header/footer/body)
     */
    async function handleGenerateTemplate() {
        // Collect all template options from the form
        const options = collectTemplateOptions();

        if (!options.prompt) {
            showError('Please describe the ' + JTB_AI.templateType + ' you want to create');
            return;
        }

        JTB_AI.isGenerating = true;
        showProgress('Generating ' + JTB_AI.templateType + '...');

        // Get template-specific options (header/footer/body)
        const templateOptions = options[JTB_AI.templateType] || {};

        // console.log removed
        // console.log removed
        // console.log removed
        // console.log removed
        // console.log removed
        // console.log removed

        try {
            // UNIFIED AI ENDPOINT (2026-02-04)
            const response = await fetch('/api/jtb/ai/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': JTB_AI.csrfToken
                },
                body: JSON.stringify({
                    action: 'layout',
                    prompt: options.prompt,
                    context: {
                        type: 'template',
                        template_type: JTB_AI.templateType,
                        id: JTB_AI.templateId,
                        style: options.style,
                        industry: options.industry
                    },
                    style: options.style,
                    industry: options.industry,
                    template_style: options.templateStyle,
                    template_options: templateOptions
                })
            });

            const data = await response.json();

            if (!data.ok && !data.success) {
                throw new Error(data.error || 'Generation failed');
            }

            // console.log removed
                sectionsCount: data.layout?.sections?.length || data.content?.length || 0,
                stats: data.stats
            });

            // Handle response format
            const layout = data.layout || { sections: data.content || [] };
            const normalizedLayout = layout.sections ? layout : { sections: Array.isArray(layout) ? layout : [layout] };

            // Show preview
            const meta = { stats: data.stats };
            setAiResult(normalizedLayout, null, meta);
            showPreview(normalizedLayout, meta);

        } catch (error) {
            console.error('[Template AI] Generation error:', error);
            showError(error.message || 'Failed to generate ' + JTB_AI.templateType);
        } finally {
            JTB_AI.isGenerating = false;
            hideProgress();
        }
    }

    // ========================================
    // Preview Modal
    // ========================================

    async function showPreview(layout, meta = {}) {
        if (!JTB_AI.previewModal) return;

        const previewFrame = JTB_AI.previewModal.querySelector('.jtb-ai-preview-frame');
        const metaEl = document.getElementById('jtb-ai-preview-meta');

        if (!previewFrame) return;

        // Show meta info if available
        if (metaEl) {
            let metaHtml = '';

            // Quality badge with Confidence (clickable in DEV_MODE)
            if (meta.quality) {
                const q = meta.quality;

                // Determine badge color based on decision
                let bgColor;
                if (q.decision === 'FAIL' || q.failed) {
                    bgColor = '#dc2626'; // Red for FAIL
                } else if (q.decision === 'ACCEPT' && q.confidence >= 70) {
                    bgColor = '#2563eb'; // Blue for high confidence ACCEPT
                } else {
                    const statusColors = {
                        'EXCELLENT': '#2563eb',
                        'GOOD': '#10b981',
                        'ACCEPTABLE': '#f59e0b',
                        'REJECT': '#ef4444'
                    };
                    bgColor = statusColors[q.status] || '#6b7280';
                }

                const forcedLabel = q.forced_accept ? ' FORCED' : '';
                const autofixLabel = q.autofix_applied ? ' +FIX' : '';
                const failedLabel = q.failed ? ' FAILED' : '';
                const scoreDisplay = q.score_before_fix
                    ? `${q.score_before_fix}→${q.score}`
                    : q.score;

                // Build tooltip with decision details
                const tooltipParts = [
                    `Decision: ${q.decision || 'N/A'}`,
                    q.stop_reason ? `Reason: ${q.stop_reason}` : null,
                    q.improvement ? `Score Δ: ${q.improvement.score_delta > 0 ? '+' : ''}${q.improvement.score_delta}` : null,
                    q.is_oscillation ? 'OSCILLATION DETECTED!' : null,
                    'Click to open Quality Dashboard (DEV_MODE)'
                ].filter(Boolean).join('\\n');

                const badgeStyle = `background:${bgColor};color:#fff;padding:4px 10px;border-radius:4px;font-size:11px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;`;

                metaHtml += `
                    <span class="jtb-ai-quality-badge" style="${badgeStyle}" title="${tooltipParts}" onclick="window.open('/admin/jtb/quality-dashboard','_blank')">
                        <span>${q.status} ${scoreDisplay}/25</span>
                        <span style="opacity:0.8;font-size:10px;">(#${q.attempt}${forcedLabel}${autofixLabel}${failedLabel})</span>
                    </span>
                `;

                // Confidence chip (DEV_MODE visible)
                if (typeof q.confidence === 'number') {
                    const confColor = q.confidence >= 70 ? '#10b981' : q.confidence >= 40 ? '#f59e0b' : '#ef4444';
                    metaHtml += `
                        <span class="jtb-ai-confidence-chip" style="background:${confColor};color:#fff;padding:2px 8px;border-radius:3px;font-size:10px;font-weight:700;">
                            CONF ${q.confidence}
                        </span>
                    `;
                }

                // Decision badge
                if (q.decision) {
                    const decisionColors = {
                        'ACCEPT': '#10b981',
                        'RETRY': '#f59e0b',
                        'FAIL': '#ef4444'
                    };
                    const decColor = decisionColors[q.decision] || '#6b7280';
                    metaHtml += `
                        <span class="jtb-ai-decision-badge" style="background:${decColor};color:#fff;padding:2px 8px;border-radius:3px;font-size:10px;font-weight:700;">
                            ${q.decision}
                        </span>
                    `;
                }

                // Show autofix rules count if applied
                if (q.autofix_applied && q.autofix_rules?.length > 0) {
                    metaHtml += `
                        <span class="jtb-ai-preview-meta-item" style="background:#f97316;color:#fff;padding:2px 8px;border-radius:3px;font-size:10px;">
                            <strong>${q.autofix_rules.length}</strong> fixes
                        </span>
                    `;
                }

                // Stop reason tooltip (if FAIL)
                if (q.stop_reason && (q.decision === 'FAIL' || q.failed)) {
                    metaHtml += `
                        <span class="jtb-ai-stop-reason" style="background:#991b1b;color:#fff;padding:2px 8px;border-radius:3px;font-size:9px;max-width:150px;overflow:hidden;text-overflow:ellipsis;" title="${q.stop_reason}">
                            STOP: ${q.stop_reason}
                        </span>
                    `;
                }
            }

            if (meta.patternsUsed && meta.patternsUsed.length > 0) {
                metaHtml += `
                    <span class="jtb-ai-preview-meta-item">
                        <strong>${meta.patternsUsed.length}</strong> patterns
                    </span>
                `;
            }

            metaHtml += `
                <span class="jtb-ai-preview-meta-item">
                    <strong>${(layout.sections ?? layout.content)?.length || 0}</strong> sections
                </span>
            `;

            metaEl.innerHTML = metaHtml;
        }

        JTB_AI.previewModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Show loading state
        previewFrame.innerHTML = '<div style="padding:60px 20px;text-align:center;color:#6b7280;">Rendering preview...</div>';

        // Use JTB_Renderer via /api/jtb/render for consistent preview
        // This ensures AI preview matches JTB builder preview exactly
        try {
            const sections = layout.sections ?? layout.content ?? [];

            // DEBUG: Log what we're rendering

            // Ensure all sections have proper structure with IDs
            const processedSections = sections.map(section => ensureSectionStructure(section));

            // Build JTB-compatible content structure
            const jtbContent = {
                version: '1.0',
                content: processedSections
            };



            const response = await fetch('/api/jtb/render', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': JTB_AI.csrfToken
                },
                body: 'content=' + encodeURIComponent(JSON.stringify(jtbContent))
            });

            const responseText = await response.text();

            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                throw new Error('Invalid JSON response');
            }

            // DEBUG: Log render response

            if (data.success && data.data?.html) {
                // Stage 25: DEV_MODE iframe hard reload - clear and set build flag
                if (window.JTB_DEV_MODE) {
                    previewFrame.innerHTML = '';
                    previewFrame.setAttribute('data-preview-build', String(Date.now()));

                    // Add build hash class for parity check
                    const assetV = window.JTB_ASSET_V || {};
                    const buildHash = String(assetV.js || '0').slice(-6) + String(assetV.css || '0').slice(-6);
                    previewFrame.className = previewFrame.className.replace(/jtb-preview-build-\w+/g, '');
                    previewFrame.classList.add(`jtb-preview-build-${buildHash}`);
                }

                // Inject rendered HTML from JTB_Renderer
                previewFrame.classList.add('jtb-ai-preview-rendered');

                // FIXED: Keep style tag inside the preview frame for proper scoping
                // This ensures AI-generated CSS overrides any base styles
                previewFrame.innerHTML = data.data.html;

                // Also add to document.head as backup (for computed style inspection)
                const cssMatch = data.data.html.match(/<style>([\s\S]*?)<\/style>/);
                if (cssMatch && cssMatch[1]) {
                    const styleId = 'jtb-ai-preview-styles';
                    let oldStyle = document.getElementById(styleId);
                    if (oldStyle) oldStyle.remove();

                    const styleEl = document.createElement('style');
                    styleEl.id = styleId;
                    styleEl.textContent = cssMatch[1];
                    document.head.appendChild(styleEl);
                }

                // console.log removed

                // DEBUG: Check what is actually in previewFrame
                const firstSection = previewFrame.querySelector('.jtb-section');
                if (firstSection) {
                    // console.log removed
                    // console.log removed

                    // Check first heading
                    const firstHeading = previewFrame.querySelector('h1, h2, h3');
                    if (firstHeading) {
                        // console.log removed
                        // console.log removed
                    }

                    // DEBUG: Check if jtb-ai-preview-rendered class is applied and styles work
                    // console.log removed

                    // Check computed styles on first module
                    const firstModule = previewFrame.querySelector('.jtb-module');
                    if (firstModule) {
                        const computed = window.getComputedStyle(firstModule);
                        // console.log removed
                            background: computed.background,
                            border: computed.border,
                            padding: computed.padding,
                            cursor: computed.cursor
                        });
                    }
                }

                // DEV_MODE: Apply debug overlays if enabled
                if (window.JTB_DEV_MODE && JTB_AI.debugMode) {
                    applyDebugOverlays();
                }

                // Stage 25: Preview Parity Guard (DEV_MODE only)
                if (window.JTB_DEV_MODE) {
                    checkPreviewParity(previewFrame);
                }
            } else {
                // Fallback to simple error display
                previewFrame.innerHTML = '<div style="padding:60px 20px;text-align:center;color:#ef4444;">Preview render failed: ' + (data.error || 'Unknown error') + '</div>';
            }
        } catch (error) {
            console.error('Preview render error:', error);
            previewFrame.innerHTML = '<div style="padding:60px 20px;text-align:center;color:#ef4444;">Preview render failed</div>';
        }
    }

    function closePreviewModal() {
        if (!JTB_AI.previewModal) return;
        JTB_AI.previewModal.style.display = 'none';
        if (!JTB_AI.panel?.classList.contains('is-open')) {
            document.body.style.overflow = '';
        }
    }

    // ========================================
    // DEV_MODE: Visual Debug Overlays
    // ========================================

    function applyDebugOverlays() {
        if (!window.JTB_DEV_MODE) return;

        const previewFrame = JTB_AI.previewModal?.querySelector('.jtb-ai-preview-frame');
        if (!previewFrame) return;

        // Stage 20: Lazy debug - fully cleanup when OFF
        if (!JTB_AI.debugMode) {
            cleanupDebugOverlays();
            return;
        }

        // Remove existing debug overlays before recreating
        previewFrame.querySelectorAll('.jtb-debug-overlay, .jtb-debug-filters, .jtb-debug-diff').forEach(el => el.remove());

        previewFrame.classList.add('jtb-debug-enabled');

        // Stage 25: Get layout and quality from single source of truth
        const layoutData = getCurrentLayout();
        const sections = layoutData?.sections ?? layoutData?.content ?? [];

        // Stage 25: Get quality from single source (fallback to layout._quality for legacy)
        const quality = getCurrentQuality();
        const autofixApplied = quality.autofix_applied || false;
        const autofixRules = quality.autofix_rules || [];

        // Add confidence/decision banner at top of preview (DEV_MODE)
        if (quality.decision || quality.confidence !== undefined) {
            const debugBanner = document.createElement('div');
            debugBanner.className = 'jtb-debug-overlay jtb-debug-banner';

            let bannerContent = '';

            // Confidence indicator
            if (typeof quality.confidence === 'number') {
                const confClass = quality.confidence >= 70 ? 'high' : quality.confidence >= 40 ? 'medium' : 'low';
                bannerContent += `<span class="jtb-debug-conf jtb-debug-conf-${confClass}">CONF: ${quality.confidence}</span>`;
            }

            // Decision indicator
            if (quality.decision) {
                const decClass = quality.decision.toLowerCase();
                bannerContent += `<span class="jtb-debug-decision jtb-debug-decision-${decClass}">${quality.decision}</span>`;
            }

            // Stop reason (if any)
            if (quality.stop_reason) {
                bannerContent += `<span class="jtb-debug-stop">STOP: ${quality.stop_reason}</span>`;
            }

            // Improvement delta
            if (quality.improvement) {
                const imp = quality.improvement;
                const scoreSign = imp.score_delta > 0 ? '+' : '';
                const violSign = imp.violations_delta > 0 ? '+' : '';
                bannerContent += `<span class="jtb-debug-improvement">Δscore:${scoreSign}${imp.score_delta} Δviol:${violSign}${imp.violations_delta}</span>`;
            }

            // Oscillation warning
            if (quality.is_oscillation) {
                bannerContent += `<span class="jtb-debug-oscillation">⚠ OSCILLATION</span>`;
            }

            // ============================================================
            // Stage 11: DARK_MISUSE warning and dark_sections count
            // ============================================================

            // Show dark_sections count from metrics
            const darkSections = quality.metrics?.dark_sections ?? quality.meta?.dark_sections ?? 0;
            const darkMisuseCount = quality.metrics?.dark_misuse_count ?? 0;

            if (darkSections > 0 || darkMisuseCount > 0) {
                const darkClass = darkSections > 2 ? 'jtb-debug-dark-overflow' : '';
                bannerContent += `<span class="jtb-debug-dark-count ${darkClass}">DARK: ${darkSections}</span>`;
            }

            // DARK_MISUSE specific warning (critical violation)
            const hasDarkMisuse = quality.has_dark_misuse ||
                (quality.violations || []).some(v => v.includes('DARK_MISUSE'));

            if (hasDarkMisuse) {
                const misusePatterns = quality.metrics?.dark_misuse_patterns || [];
                const tooltip = misusePatterns.length > 0
                    ? 'DARK on LIGHT-only: ' + misusePatterns.join(', ')
                    : 'DARK context used on LIGHT-only patterns';
                bannerContent += `<span class="jtb-debug-dark-misuse" title="${tooltip}">⚠ DARK_MISUSE</span>`;
            }

            // AutoFix regression warning
            if (quality.autofix_regression) {
                bannerContent += `<span class="jtb-debug-autofix-regression" title="AutoFix introduced: ${quality.autofix_regression_type || 'unknown'}">⚠ AUTOFIX REGRESSION</span>`;
            }

            // ============================================================
            // Stage 12: Visual Intent warnings
            // ============================================================

            // VI_CONFLICT warning (2+ DOMINANT sections)
            const hasViConflict = (quality.warnings || []).some(w => w.includes('VI_CONFLICT'));
            if (hasViConflict) {
                bannerContent += `<span class="jtb-debug-vi-conflict" title="Multiple DOMINANT sections detected - should have max 1-2">⚠ VI_CONFLICT</span>`;
            }

            // HERO_NOT_DOMINANT warning
            const hasHeroNotDom = (quality.warnings || []).some(w => w.includes('HERO_NOT_DOMINANT'));
            if (hasHeroNotDom) {
                bannerContent += `<span class="jtb-debug-hero-not-dominant" title="Hero section should have DOMINANT intent">⚠ HERO_NOT_DOMINANT</span>`;
            }

            // ============================================================
            // Stage 13: Visual Rhythm warnings
            // ============================================================
            const hasDenseChain = (quality.warnings || []).some(w => w.includes('DENSE_CHAIN'));
            if (hasDenseChain) {
                bannerContent += `<span class="jtb-debug-dense-chain" title="Too many consecutive DENSE sections">⚠ DENSE_CHAIN</span>`;
            }

            const hasNoClimax = (quality.warnings || []).some(w => w.includes('NO_CLIMAX'));
            if (hasNoClimax) {
                bannerContent += `<span class="jtb-debug-no-climax" title="final_cta missing climax spacing">⚠ NO_CLIMAX</span>`;
            }

            const hasSparseTight = (quality.warnings || []).some(w => w.includes('SPARSE_TOO_TIGHT'));
            if (hasSparseTight) {
                bannerContent += `<span class="jtb-debug-sparse-tight" title="SPARSE section needs more spacing">⚠ SPARSE_TIGHT</span>`;
            }

            // Stage 13: Show density distribution
            const vdDense = quality.metrics?.vd_dense_count ?? 0;
            const vdNormal = quality.metrics?.vd_normal_count ?? 0;
            const vdSparse = quality.metrics?.vd_sparse_count ?? 0;
            if (vdDense > 0 || vdSparse > 0) {
                bannerContent += `<span class="jtb-debug-vd-stats" title="Visual Density distribution">VD: ${vdDense}D/${vdNormal}N/${vdSparse}S</span>`;
            }

            // ============================================================
            // Stage 14: Visual Scale warnings and stats
            // ============================================================
            const heroScale = quality.metrics?.hero_scale ?? null;
            const ctaScale = quality.metrics?.cta_scale ?? null;

            if (heroScale) {
                const heroScaleClass = (heroScale === 'XL' || heroScale === 'LG') ? 'jtb-debug-scale-ok' : 'jtb-debug-scale-warn';
                bannerContent += `<span class="${heroScaleClass}" title="Hero Visual Scale">HERO:${heroScale}</span>`;
            }

            if (ctaScale) {
                const ctaScaleClass = (ctaScale === 'XL' || ctaScale === 'LG') ? 'jtb-debug-scale-ok' : 'jtb-debug-scale-warn';
                bannerContent += `<span class="${ctaScaleClass}" title="CTA Visual Scale">CTA:${ctaScale}</span>`;
            }

            const hasHeroUnderScaled = (quality.warnings || []).some(w => w.includes('HERO_UNDER_SCALED'));
            if (hasHeroUnderScaled) {
                bannerContent += `<span class="jtb-debug-hero-underscaled" title="Hero scale too small">⚠ HERO_UNDER_SCALED</span>`;
            }

            const hasCtaNotClimax = (quality.warnings || []).some(w => w.includes('CTA_NOT_CLIMAX'));
            if (hasCtaNotClimax) {
                bannerContent += `<span class="jtb-debug-cta-not-climax" title="CTA scale too small for climax">⚠ CTA_NOT_CLIMAX</span>`;
            }

            const hasMultiXl = (quality.warnings || []).some(w => w.includes('MULTI_XL'));
            if (hasMultiXl) {
                bannerContent += `<span class="jtb-debug-multi-xl" title="Too many XL sections">⚠ MULTI_XL</span>`;
            }

            // ============================================================
            // Stage 15: Typography warnings
            // ============================================================
            const hasHeroTypoWeak = (quality.warnings || []).some(w => w.includes('HERO_TYPO_TOO_WEAK'));
            if (hasHeroTypoWeak) {
                bannerContent += `<span class="jtb-debug-typo-warn" title="Hero typography too weak">⚠ HERO_TYPO_WEAK</span>`;
            }

            const hasCtaTypoWeak = (quality.warnings || []).some(w => w.includes('CTA_TYPO_NOT_CLIMAX'));
            if (hasCtaTypoWeak) {
                bannerContent += `<span class="jtb-debug-typo-warn" title="CTA typography not climax">⚠ CTA_TYPO_WEAK</span>`;
            }

            const heroTypo = quality.metrics?.hero_typography ?? null;
            const ctaTypo = quality.metrics?.cta_typography ?? null;
            if (heroTypo || ctaTypo) {
                let typoStr = '';
                if (heroTypo) typoStr += `H:${heroTypo}`;
                if (ctaTypo) typoStr += ` C:${ctaTypo}`;
                bannerContent += `<span class="jtb-debug-typo-stats" title="Typography scales">${typoStr.trim()}</span>`;
            }

            // ============================================================
            // Stage 16: Emotional Flow warnings and stats
            // ============================================================

            // ATTENTION_OVERLOAD warning (>2 consecutive HIGH attention)
            const hasAttOverload = (quality.warnings || []).some(w => w.includes('ATTENTION_OVERLOAD'));
            if (hasAttOverload) {
                bannerContent += `<span class="jtb-debug-att-overload" title="More than 2 consecutive HIGH attention sections">⚠ ATT_OVERLOAD</span>`;
            }

            // NO_TRUST_SECTION warning
            const hasNoTrust = (quality.warnings || []).some(w => w.includes('NO_TRUST_SECTION'));
            if (hasNoTrust) {
                bannerContent += `<span class="jtb-debug-no-trust" title="No trust-building sections in layout">⚠ NO_TRUST</span>`;
            }

            // NO_CALM_SECTION warning
            const hasNoCalm = (quality.warnings || []).some(w => w.includes('NO_CALM_SECTION'));
            if (hasNoCalm) {
                bannerContent += `<span class="jtb-debug-no-calm" title="No calm rhythm-break sections">⚠ NO_CALM</span>`;
            }

            // URGENCY_TOO_EARLY warning
            const hasUrgencyEarly = (quality.warnings || []).some(w => w.includes('URGENCY_TOO_EARLY'));
            if (hasUrgencyEarly) {
                const urgencyMatch = (quality.warnings || []).find(w => w.includes('URGENCY_TOO_EARLY'));
                const urgencyPercent = urgencyMatch ? urgencyMatch.match(/(\d+)%/) : null;
                const urgTitle = urgencyPercent ? `Urgency at ${urgencyPercent[1]}% (should be >60%)` : 'Urgency too early in page flow';
                bannerContent += `<span class="jtb-debug-urgency-early" title="${urgTitle}">⚠ URG_EARLY</span>`;
            }

            // FLAT_FLOW warning (no emotional variety)
            const hasFlatFlow = (quality.warnings || []).some(w => w.includes('FLAT_FLOW'));
            if (hasFlatFlow) {
                bannerContent += `<span class="jtb-debug-flat-flow" title="No emotional variety in layout">⚠ FLAT_FLOW</span>`;
            }

            // Show emotional flow signature (e.g., FOCUS → TRUST → FOCUS → URGENCY)
            const emotionalFlow = quality.metrics?.emotional_flow_signature ?? quality.meta?.emotional_flow_signature ?? null;
            if (emotionalFlow && emotionalFlow.length > 0) {
                const flowShort = emotionalFlow.replace(/focus/gi, 'F').replace(/trust/gi, 'T').replace(/calm/gi, 'C').replace(/urgency/gi, 'U');
                bannerContent += `<span class="jtb-debug-emo-flow" title="Emotional Flow: ${emotionalFlow}">FLOW: ${flowShort}</span>`;
            }

            // Show attention distribution
            const attHigh = quality.metrics?.attention_high_count ?? 0;
            const attMed = quality.metrics?.attention_medium_count ?? 0;
            const attLow = quality.metrics?.attention_low_count ?? 0;
            if (attHigh > 0 || attMed > 0 || attLow > 0) {
                bannerContent += `<span class="jtb-debug-att-stats" title="Attention distribution: High/Medium/Low">ATT: ${attHigh}H/${attMed}M/${attLow}L</span>`;
            }

            // Show if urgency position is good (>60%)
            const urgencyPos = quality.metrics?.urgency_position_percent ?? null;
            if (urgencyPos !== null) {
                const urgPosClass = urgencyPos >= 60 ? 'jtb-debug-urg-ok' : 'jtb-debug-urg-early';
                bannerContent += `<span class="${urgPosClass}" title="Urgency position in page flow">URG@${Math.round(urgencyPos)}%</span>`;
            }

            // ============================================================
            // Stage 17: Narrative Flow banner info
            // ============================================================

            // Narrative signature (e.g., H-PR-PF-D-RL-RS)
            const narrativeSignature = quality.metrics?.narrative_signature ?? null;
            if (narrativeSignature && narrativeSignature.length > 0) {
                bannerContent += `<span class="jtb-debug-narr-sig" title="Narrative Signature">STORY: ${narrativeSignature}</span>`;
            }

            // Narrative score
            const narrativeScore = quality.metrics?.narrative_score ?? null;
            if (narrativeScore !== null) {
                const scoreClass = narrativeScore >= 70 ? 'jtb-debug-narr-ok' : (narrativeScore >= 40 ? 'jtb-debug-narr-warn' : 'jtb-debug-narr-fail');
                bannerContent += `<span class="${scoreClass}" title="Narrative Score">NS:${narrativeScore}</span>`;
            }

            // Broken story flow warning
            const brokenStory = quality.metrics?.broken_story_flow ?? false;
            if (brokenStory) {
                bannerContent += `<span class="jtb-debug-broken-story" title="Critical narrative issues detected">⚠ BROKEN STORY</span>`;
            } else if (narrativeScore >= 70) {
                bannerContent += `<span class="jtb-debug-flow-ok" title="Narrative flow is good">✓ FLOW OK</span>`;
            }

            // Missing roles
            const missingRoles = quality.metrics?.missing_narrative_roles ?? [];
            if (missingRoles.length > 0) {
                bannerContent += `<span class="jtb-debug-missing-roles" title="Missing story beats: ${missingRoles.join(', ')}">MISSING: ${missingRoles.join(', ')}</span>`;
            }

            // CTA_BEFORE_PROMISE check (critical)
            const hasCtaBeforePromise = (quality.warnings || []).some(w => w.includes('CTA_BEFORE_PROMISE'));
            if (hasCtaBeforePromise) {
                bannerContent += `<span class="jtb-debug-cta-before-promise" title="CTA appears before value proposition">⚠ CTA→PROMISE</span>`;
            }

            // ============================================================
            // Stage 18: Narrative Auto-Correction banner info
            // ============================================================

            const narrativeAutofixApplied = quality.metrics?.narrative_autofix_applied ?? false;
            const narrativePlaceholders = quality.metrics?.narrative_placeholders_count ?? 0;
            const narrativeSwaps = quality.metrics?.narrative_swaps_count ?? 0;
            const narrativeAutofixBlocked = quality.metrics?.narrative_autofix_blocked ?? false;

            if (narrativeAutofixApplied) {
                bannerContent += `<span class="jtb-debug-nr-autofix" title="Narrative auto-correction applied">AUTO-NR</span>`;

                if (narrativePlaceholders > 0) {
                    bannerContent += `<span class="jtb-debug-nr-placeholders" title="${narrativePlaceholders} placeholder section(s) inserted">NR+${narrativePlaceholders}</span>`;
                }

                if (narrativeSwaps > 0) {
                    bannerContent += `<span class="jtb-debug-nr-swaps" title="${narrativeSwaps} section swap(s) performed">SWAP×${narrativeSwaps}</span>`;
                }
            }

            if (narrativeAutofixBlocked) {
                bannerContent += `<span class="jtb-debug-nr-blocked-banner" title="Narrative auto-fix blocked - score too low for safe correction">⚠ NR-BLOCKED</span>`;
            }

            // ============================================================
            // Stage 19: Copy Debug Summary button (DEV_MODE only)
            // ============================================================
            if (window.JTB_DEV_MODE === true) {
                bannerContent += `<button class="jtb-debug-copy-btn" title="Copy debug summary to clipboard">📋 Copy</button>`;
                // Stage 20: Compare Last button
                bannerContent += `<button class="jtb-debug-compare-btn" title="Compare with last snapshot">⚖️ Compare</button>`;

                // ============================================================
                // Stage 22: Consistency Score + Style Lock Button
                // ============================================================
                const consistency = calculateConsistencyScore(quality, sections);
                const consClass = consistency.score >= 85 ? 'high' : (consistency.score >= 60 ? 'medium' : 'low');
                const consTooltip = `Consistency Score: ${consistency.score}/100\nCue: ${consistency.details.cue}/25 (${consistency.details.cueCoverage}% coverage)\nAttention: ${consistency.details.attention}/25\nNarrative: ${consistency.details.narrative}/25\nScale: ${consistency.details.scale}/25`;
                bannerContent += `<span class="jtb-debug-consistency jtb-debug-consistency-${consClass}" title="${consTooltip}">CONSISTENCY: ${consistency.score}</span>`;

                // Low consistency warning
                if (consistency.score < 70 && consistency.weakest) {
                    const weakTooltip = `Weakest component: ${consistency.weakest.toUpperCase()}`;
                    bannerContent += `<span class="jtb-debug-low-consistency" title="${weakTooltip}">⚠ LOW_CONSISTENCY</span>`;
                    bannerContent += `<span class="jtb-debug-weak-component" title="Weakest: ${consistency.weakest}">(${consistency.weakest})</span>`;
                }

                // ============================================================
                // Stage 23: Temporal Stability (Cross-Stage Drift Detector)
                // ============================================================
                const currentSnapshot = buildTemporalSnapshot(quality, consistency.score);
                const prevSnapshots = getTemporalSnapshots();
                const prevSnapshot = prevSnapshots.length > 0 ? prevSnapshots[prevSnapshots.length - 1] : null;

                // Detect drifts from previous generation
                const drifts = detectTemporalDrift(currentSnapshot, prevSnapshot);
                const stability = calculateTemporalStability(drifts);

                // Save current snapshot to ring buffer
                saveTemporalSnapshot(currentSnapshot);

                // ============================================================
                // Stage 24: Decision Gates + Auto-Lock
                // ============================================================
                const healthSignal = buildHealthSignal(quality, consistency, stability, drifts);
                const gateResult = evaluateGates(healthSignal);
                const autoLockApplied = applyAutoLockPolicy(gateResult);

                // Stage 24: Health Strip (compact metrics bar)
                bannerContent += buildHealthStripHtml(healthSignal, gateResult);

                // Stage 24: Gate warning badges
                bannerContent += buildGateWarningsHtml(gateResult, autoLockApplied);

                // Timeline button
                bannerContent += `<button class="jtb-debug-timeline-btn" title="View generation timeline">🧭 Timeline</button>`;

                // Style Lock button (show auto-lock status)
                let lockBtnClass = styleLocked ? 'active' : '';
                let lockBtnText = styleLocked ? '🔒 Locked' : '🔓 Lock Style';
                if (autoLockApplied && !styleLocked) {
                    lockBtnClass = 'auto-applied';
                    lockBtnText = '🔐 Auto-Locked';
                }
                bannerContent += `<button class="jtb-debug-lock-btn ${lockBtnClass}" title="Lock visual tokens to prevent drift${autoLockApplied ? ' (auto-applied this generation)' : ''}">${lockBtnText}</button>`;
            }

            debugBanner.innerHTML = bannerContent;

            // Attach Copy Debug Summary handler
            const copyBtn = debugBanner.querySelector('.jtb-debug-copy-btn');
            if (copyBtn) {
                copyBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    copyDebugSummary(quality);
                });
            }

            // Stage 20: Attach Compare handler
            const compareBtn = debugBanner.querySelector('.jtb-debug-compare-btn');
            if (compareBtn) {
                compareBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    compareSnapshots(quality);
                });
            }

            // Stage 22: Attach Style Lock handler
            const lockBtn = debugBanner.querySelector('.jtb-debug-lock-btn');
            if (lockBtn) {
                lockBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleStyleLock();
                });
            }

            // Stage 23: Attach Timeline button handler
            const timelineBtn = debugBanner.querySelector('.jtb-debug-timeline-btn');
            if (timelineBtn) {
                timelineBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    showTemporalTimeline();
                });
            }

            // Stage 26: Add main class for baseline controls targeting
            debugBanner.classList.add('jtb-debug-banner-main');

            previewFrame.insertBefore(debugBanner, previewFrame.firstChild);

            // Stage 22: Apply Style Lock state if already locked
            if (styleLocked) {
                previewFrame.classList.add('jtb-style-locked');
            }

            // Stage 20: Add Quick Filters toolbar
            const filtersToolbar = createDebugFilters();
            if (filtersToolbar) {
                debugBanner.after(filtersToolbar);
            }

            // Stage 26: Add baseline controls and diff display
            refreshDebugBanner();

            // Stage 20: Initialize IntersectionObserver for section focus
            initSectionObserver();
        }

        // Process each section
        previewFrame.querySelectorAll('.jtb-section').forEach((sectionEl, sectionIndex) => {
            const sectionData = sections[sectionIndex] || {};
            const pattern = sectionData._pattern || sectionData.attrs?._pattern || 'unknown';
            const visualContext = sectionData._visual_context || sectionData.attrs?.visual_context || 'LIGHT';
            const bgType = detectBackgroundType(sectionData.attrs || {});

            // Check if this section was auto-fixed
            const sectionAutofix = sectionData._autofix || [];
            const wasAutofixed = sectionAutofix.length > 0;

            // Get specific autofix rules for this section
            const sectionFixRules = autofixRules.filter(r => r.section_index === sectionIndex);

            // ============================================================
            // Stage 11: Check for use_background_alt and validate completeness
            // ============================================================
            const attrs = sectionData.attrs || {};
            const useBackgroundAlt = attrs.use_background_alt === true;
            const hasBgTypeColor = attrs.background_type === 'color';
            const hasBgColor = !!attrs.background_color;
            const altIsComplete = useBackgroundAlt && hasBgTypeColor && hasBgColor;
            const altIsBuggy = useBackgroundAlt && (!hasBgTypeColor || !hasBgColor);

            // Create section overlay badge
            const sectionBadge = document.createElement('div');
            sectionBadge.className = 'jtb-debug-overlay jtb-debug-section-badge';

            let badgeHtml = `
                <span class="jtb-debug-index">#${sectionIndex + 1}</span>
                <span class="jtb-debug-pattern">${pattern}</span>
                <span class="jtb-debug-context jtb-debug-context-${visualContext.toLowerCase()}">${visualContext}</span>
                <span class="jtb-debug-bg">${bgType}</span>
            `;

            // Stage 11: Show ALT badge if use_background_alt is true
            if (altIsComplete) {
                badgeHtml += `<span class="jtb-debug-alt" title="Background alternation active (bg=${attrs.background_color})">ALT</span>`;
            } else if (altIsBuggy) {
                const missing = [];
                if (!hasBgTypeColor) missing.push('background_type!=color');
                if (!hasBgColor) missing.push('no background_color');
                badgeHtml += `<span class="jtb-debug-alt-bug" title="ALT incomplete: ${missing.join(', ')}">ALT_BUG</span>`;
            }

            // ============================================================
            // Stage 12: Visual Intent badge
            // ============================================================
            const visualIntent = sectionData._visual_intent || attrs.visual_intent || null;
            if (visualIntent) {
                const viLower = visualIntent.toLowerCase();
                badgeHtml += `<span class="jtb-debug-vi jtb-debug-vi-${viLower}" title="Visual Intent: ${visualIntent}">${visualIntent.substring(0, 3)}</span>`;
            }

            // ============================================================
            // Stage 13: Visual Density + Spacing badge
            // ============================================================
            const visualDensity = sectionData._visual_density || attrs.visual_density || null;
            if (visualDensity) {
                const vdLower = visualDensity.toLowerCase();
                const vdShort = visualDensity === 'DENSE' ? 'DNS' : (visualDensity === 'SPARSE' ? 'SPA' : 'NRM');
                badgeHtml += `<span class="jtb-debug-vd jtb-debug-vd-${vdLower}" title="Visual Density: ${visualDensity}">${vdShort}</span>`;
            }

            const beforeSpacing = attrs.before_spacing || null;
            const afterSpacing = attrs.after_spacing || null;
            if (beforeSpacing || afterSpacing) {
                const spacingStr = (beforeSpacing ? '↑' + beforeSpacing : '') + (afterSpacing ? ' ↓' + afterSpacing : '');
                badgeHtml += `<span class="jtb-debug-spacing" title="Rhythm spacing">${spacingStr.trim()}</span>`;
            }

            // ============================================================
            // Stage 14: Visual Scale badge
            // ============================================================
            const visualScale = sectionData._visual_scale || attrs.visual_scale || null;
            if (visualScale) {
                const vsLower = visualScale.toLowerCase();
                badgeHtml += `<span class="jtb-debug-scale jtb-debug-scale-${vsLower}" title="Visual Scale: ${visualScale}">${visualScale}</span>`;
            }

            // ============================================================
            // Stage 15: Typography Scale + Text Emphasis badge
            // ============================================================
            const typographyScale = sectionData._typography_scale || attrs.typography_scale || null;
            if (typographyScale) {
                const tsLower = typographyScale.toLowerCase();
                badgeHtml += `<span class="jtb-debug-ts jtb-debug-ts-${tsLower}" title="Typography Scale: ${typographyScale}">TS:${typographyScale}</span>`;
            }

            const textEmphasis = sectionData._text_emphasis || attrs.text_emphasis || null;
            if (textEmphasis) {
                const teLower = textEmphasis.toLowerCase();
                const teShort = textEmphasis === 'strong' ? 'STR' : (textEmphasis === 'soft' ? 'SOF' : 'NRM');
                badgeHtml += `<span class="jtb-debug-te jtb-debug-te-${teLower}" title="Text Emphasis: ${textEmphasis}">${teShort}</span>`;
            }

            // ============================================================
            // Stage 16: Emotional Tone + Attention Level badge
            // ============================================================
            const emotionalTone = sectionData._emotional_tone || attrs.emotional_tone || null;
            if (emotionalTone) {
                const etLower = emotionalTone.toLowerCase();
                const etShort = {
                    'calm': 'CALM',
                    'focus': 'FOCUS',
                    'trust': 'TRUST',
                    'urgency': 'URG'
                }[etLower] || emotionalTone.substring(0, 4).toUpperCase();
                badgeHtml += `<span class="jtb-debug-et jtb-debug-et-${etLower}" title="Emotional Tone: ${emotionalTone}">${etShort}</span>`;
            }

            const attentionLevel = sectionData._attention_level || attrs.attention_level || null;
            if (attentionLevel) {
                const attLower = attentionLevel.toLowerCase();
                const attShort = {
                    'low': 'LOW',
                    'medium': 'MED',
                    'high': 'HIGH'
                }[attLower] || attentionLevel.substring(0, 3).toUpperCase();
                badgeHtml += `<span class="jtb-debug-att jtb-debug-att-${attLower}" title="Attention Level: ${attentionLevel}">ATT:${attShort}</span>`;
            }

            // ============================================================
            // Stage 17: Narrative Role badge
            // ============================================================
            const narrativeRole = sectionData._narrative_role || attrs.narrative_role || null;
            if (narrativeRole) {
                const nrLower = narrativeRole.toLowerCase();
                const nrShort = {
                    'hook': 'HOOK',
                    'problem': 'PROB',
                    'promise': 'PROM',
                    'proof': 'PROOF',
                    'details': 'DTLS',
                    'relief': 'RLIF',
                    'resolution': 'RESOL'
                }[nrLower] || narrativeRole.substring(0, 4).toUpperCase();
                badgeHtml += `<span class="jtb-debug-nr jtb-debug-nr-${nrLower}" title="Narrative Role: ${narrativeRole}">NR:${nrShort}</span>`;
            }

            // Check for narrative hard fail
            const narrativeHardFail = sectionData._narrative_hard_fail || null;
            if (narrativeHardFail) {
                badgeHtml += `<span class="jtb-debug-nr-fail" title="Narrative Hard Fail: ${narrativeHardFail}">⚠ ${narrativeHardFail}</span>`;
            }

            // ============================================================
            // Stage 18: Narrative Auto-Correction badges
            // ============================================================

            // Check if section is a placeholder
            const isPlaceholder = sectionData._placeholder || attrs._placeholder || false;
            if (isPlaceholder) {
                badgeHtml += `<span class="jtb-debug-nr-placeholder" title="AI-inserted placeholder section">PLACEHOLDER</span>`;
            }

            // Check if section was swapped
            const wasSwapped = sectionData._narrative_swapped || attrs._narrative_swapped || false;
            if (wasSwapped) {
                badgeHtml += `<span class="jtb-debug-nr-swap" title="Section position swapped by auto-fix">SWAPPED</span>`;
            }

            // Check if autofix was blocked
            const autofixBlocked = sectionData._autofix_blocked || attrs._autofix_blocked || false;
            if (autofixBlocked) {
                badgeHtml += `<span class="jtb-debug-nr-blocked" title="Auto-fix blocked - score too low">⚠ BLOCKED</span>`;
            }

            // Add AUTO-FIXED badge if section was modified
            if (wasAutofixed || sectionFixRules.length > 0) {
                const fixNames = sectionAutofix.length > 0
                    ? sectionAutofix.map(f => f.split(':')[0]).join(', ')
                    : sectionFixRules.map(r => r.rule).join(', ');
                badgeHtml += `<span class="jtb-debug-autofix" title="${fixNames}">AUTO-FIXED</span>`;
            }

            sectionBadge.innerHTML = badgeHtml;
            sectionEl.style.position = 'relative';
            sectionEl.appendChild(sectionBadge);

            // Process rows
            const rowData = sectionData.children || [];
            sectionEl.querySelectorAll(':scope > .jtb-section-inner > .jtb-row, :scope > .jtb-row').forEach((rowEl, rowIndex) => {
                const rowInfo = rowData[rowIndex] || {};
                const columns = rowInfo.attrs?.columns || detectColumnsFromClass(rowEl);

                // Create row overlay badge
                const rowBadge = document.createElement('div');
                rowBadge.className = 'jtb-debug-overlay jtb-debug-row-badge';
                rowBadge.innerHTML = `<span class="jtb-debug-cols">${columns}</span>`;
                rowEl.style.position = 'relative';
                rowEl.appendChild(rowBadge);

                // Process columns
                const colData = rowInfo.children || [];
                rowEl.querySelectorAll(':scope > .jtb-column').forEach((colEl, colIndex) => {
                    const colInfo = colData[colIndex] || {};
                    const moduleCount = colEl.querySelectorAll('.jtb-module, [class*="jtb-module-"]').length;
                    const moduleChildren = colInfo.children || [];

                    // Create column overlay badge
                    const colBadge = document.createElement('div');
                    colBadge.className = 'jtb-debug-overlay jtb-debug-col-badge';
                    colBadge.innerHTML = `<span>${moduleCount} mod</span>`;
                    colEl.style.position = 'relative';
                    colEl.appendChild(colBadge);

                    // Process modules
                    colEl.querySelectorAll(':scope > div[id], :scope > .jtb-module').forEach((modEl, modIndex) => {
                        const modInfo = moduleChildren[modIndex] || {};
                        const modType = modInfo.type || detectModuleType(modEl);

                        // Create module overlay badge
                        const modBadge = document.createElement('div');
                        modBadge.className = 'jtb-debug-overlay jtb-debug-module-badge';
                        modBadge.innerHTML = `<span class="jtb-debug-type">${modType}</span><span class="jtb-debug-source">AI</span>`;
                        modEl.style.position = 'relative';
                        modEl.appendChild(modBadge);
                    });
                });
            });
        });
    }

    function detectBackgroundType(attrs) {
        if (attrs.background_image) return 'image';
        if (attrs.background_gradient || attrs.gradient_type) return 'gradient';
        if (attrs.background_color && attrs.background_color !== 'transparent') return 'color';
        return 'none';
    }

    function detectColumnsFromClass(rowEl) {
        const classList = rowEl.className;
        const match = classList.match(/jtb-row-cols-([0-9_-]+)/);
        if (match) {
            return match[1].replace(/-/g, '_');
        }
        const colCount = rowEl.querySelectorAll(':scope > .jtb-column').length;
        return `${colCount}col`;
    }

    function detectModuleType(modEl) {
        const id = modEl.id || '';
        const typeMatch = id.match(/^([a-z_]+)_/);
        if (typeMatch) return typeMatch[1];

        const classList = modEl.className;
        const classMatch = classList.match(/jtb-module-([a-z_-]+)/);
        if (classMatch) return classMatch[1];

        return 'unknown';
    }

    /**
     * Stage 19: Copy Debug Summary to clipboard (DEV_MODE only)
     * Outputs ONLY safe metadata - no content, prompts, or layout tree
     */
    function copyDebugSummary(quality) {
        if (!quality) {
            showToast('No quality data available', 'warning');
            return;
        }

        const metrics = quality.metrics || {};

        // Build safe summary object - NO content, NO prompts, NO layout data
        const summary = {
            timestamp: new Date().toISOString(),
            // Core quality metrics
            score: quality.score ?? null,
            status: quality.status ?? null,
            attempt: quality.attempt ?? null,
            confidence: quality.confidence ?? null,
            decision: quality.decision ?? null,
            stop_reason: quality.stop_reason ?? null,
            // Narrative metrics (Stage 17)
            narrative_signature: metrics.narrative_signature ?? null,
            narrative_score: metrics.narrative_score ?? null,
            // Stage 18 metrics
            narrative_autofix_applied: metrics.narrative_autofix_applied ?? false,
            narrative_placeholders: metrics.narrative_placeholders_count ?? 0,
            narrative_swaps: metrics.narrative_swaps_count ?? 0,
            narrative_autofix_blocked: metrics.narrative_autofix_blocked ?? false,
            // Section counts
            total_sections: metrics.total_sections ?? 0,
            dark_sections: metrics.dark_sections ?? 0,
            // Visual metrics
            scale_distribution: {
                xs: metrics.scale_xs ?? 0,
                sm: metrics.scale_sm ?? 0,
                md: metrics.scale_md ?? 0,
                lg: metrics.scale_lg ?? 0,
                xl: metrics.scale_xl ?? 0
            },
            hero_scale: metrics.hero_scale ?? null,
            cta_scale: metrics.cta_scale ?? null,
            // Autofix counts
            autofix_count: quality.autofix_rules?.length ?? 0,
            // Warnings/violations (only counts, not content)
            warnings_count: (quality.warnings || []).length,
            violations_count: (quality.violations || []).length
        };

        // Convert to single-line JSON
        const jsonStr = JSON.stringify(summary);

        // Copy to clipboard
        navigator.clipboard.writeText(jsonStr).then(() => {
            showToast('Debug summary copied!', 'success');
        }).catch(err => {
            console.error('Copy failed:', err);
            showToast('Copy failed', 'error');
        });
    }

    // ========================================
    // STAGE 20: UX & PERFORMANCE HARDENING
    // ========================================

    /**
     * Stage 20: Store for last debug snapshot (for comparison)
     */
    let lastDebugSnapshot = null;

    /**
     * Stage 20: Current active debug filter
     */
    let activeDebugFilter = null;

    /**
     * Stage 20: IntersectionObserver for section focus
     */
    let sectionObserver = null;

    // ========================================
    // STAGE 22: VISUAL REGRESSION GUARD + CONSISTENCY SCORING
    // ========================================

    /**
     * Stage 22: Style Lock state (persisted in sessionStorage)
     */
    let styleLocked = false;

    /**
     * Stage 22: Initialize Style Lock state from sessionStorage
     */
    function initStyleLock() {
        try {
            styleLocked = sessionStorage.getItem('jtb_style_locked') === 'true';
        } catch (e) {
            styleLocked = false;
        }
    }

    /**
     * Stage 22: Toggle Style Lock and persist state
     */
    function toggleStyleLock() {
        styleLocked = !styleLocked;

        const previewFrame = JTB_AI.previewModal?.querySelector('.jtb-ai-preview-frame');
        if (previewFrame) {
            if (styleLocked) {
                previewFrame.classList.add('jtb-style-locked');
            } else {
                previewFrame.classList.remove('jtb-style-locked');
            }
        }

        // Persist state
        try {
            sessionStorage.setItem('jtb_style_locked', styleLocked ? 'true' : 'false');
        } catch (e) {
            // Ignore storage errors
        }

        // Update button state
        const lockBtn = document.querySelector('.jtb-debug-lock-btn');
        if (lockBtn) {
            if (styleLocked) {
                lockBtn.classList.add('active');
                lockBtn.textContent = '🔒 Locked';
            } else {
                lockBtn.classList.remove('active');
                lockBtn.textContent = '🔓 Lock Style';
            }
        }

        showToast(styleLocked ? 'Style locked - visual tokens frozen' : 'Style unlocked', 'info');
    }

    /**
     * Stage 22: Calculate Visual Consistency Score (0-100)
     * Based on 4 components:
     * A) Cue Bar Coverage - % sections with active cue bar (NR/VI/ET)
     * B) Attention Balance - penalty for >2 HIGH attention in a row
     * C) Narrative Completeness - bonus for HOOK, PROMISE, PROOF, RESOLUTION
     * D) Scale Discipline - penalty for >2 XL or missing XL on HERO
     *
     * @param {Object} quality - Quality metrics from API
     * @param {Array} sections - Layout sections array
     * @returns {Object} { score, weakest, details }
     */
    function calculateConsistencyScore(quality, sections) {
        if (!window.JTB_DEV_MODE) return { score: 0, weakest: null, details: {} };

        const metrics = quality?.metrics || {};
        const warnings = quality?.warnings || [];
        const totalSections = sections?.length || metrics.total_sections || 0;

        if (totalSections === 0) {
            return { score: 0, weakest: 'no_sections', details: {} };
        }

        // A) Cue Bar Coverage (0-25 points)
        // Count sections with NR/VI/ET classes that would show cue bar
        let cueBarSections = 0;
        if (sections && sections.length) {
            sections.forEach(section => {
                const attrs = section.attrs || {};
                const hasNR = section._narrative_role || attrs.narrative_role;
                const hasVI = section._visual_intent || attrs.visual_intent;
                const hasET = section._emotional_tone || attrs.emotional_tone;
                if (hasNR || hasVI || hasET) {
                    cueBarSections++;
                }
            });
        }
        const cueCoverage = totalSections > 0 ? (cueBarSections / totalSections) : 0;
        const cueScore = Math.round(cueCoverage * 25);

        // B) Attention Balance (0-25 points)
        // Penalty for ATTENTION_OVERLOAD warning (>2 HIGH in a row)
        const hasAttOverload = warnings.some(w => w.includes('ATTENTION_OVERLOAD'));
        const attHigh = metrics.attention_high_count ?? 0;
        const attMed = metrics.attention_medium_count ?? 0;
        const attLow = metrics.attention_low_count ?? 0;

        let attentionScore = 25;
        if (hasAttOverload) {
            attentionScore -= 15; // Major penalty
        }
        // Additional penalty if >50% sections are HIGH
        if (totalSections > 0 && attHigh / totalSections > 0.5) {
            attentionScore -= 5;
        }
        // Bonus for having LOW sections (rhythm breaks)
        if (attLow > 0) {
            attentionScore = Math.min(25, attentionScore + 3);
        }
        attentionScore = Math.max(0, attentionScore);

        // C) Narrative Completeness (0-25 points)
        // Bonus for having HOOK, PROMISE, PROOF, RESOLUTION
        const narrativeSignature = metrics.narrative_signature || '';
        const narrativeScore = metrics.narrative_score ?? 0;

        let narrativePoints = 0;
        // Check for key story beats
        if (narrativeSignature.includes('H')) narrativePoints += 6; // HOOK
        if (narrativeSignature.includes('PR') || narrativeSignature.includes('PRM')) narrativePoints += 6; // PROMISE
        if (narrativeSignature.includes('PF') || narrativeSignature.includes('PRF')) narrativePoints += 6; // PROOF
        if (narrativeSignature.includes('RS') || narrativeSignature.includes('RES')) narrativePoints += 7; // RESOLUTION

        // Cap at 25
        narrativePoints = Math.min(25, narrativePoints);

        // Penalty if narrative score is very low
        if (narrativeScore < 40) {
            narrativePoints = Math.max(0, narrativePoints - 10);
        }

        // D) Scale Discipline (0-25 points)
        // Penalty for >2 XL sections or missing XL on HERO
        const hasMultiXL = warnings.some(w => w.includes('MULTI_XL'));
        const hasHeroUnderscaled = warnings.some(w => w.includes('HERO_UNDER_SCALED'));
        const heroScale = metrics.hero_scale || null;
        const ctaScale = metrics.cta_scale || null;

        let scaleScore = 25;
        if (hasMultiXL) {
            scaleScore -= 10;
        }
        if (hasHeroUnderscaled) {
            scaleScore -= 8;
        }
        // Bonus for correct hero scale
        if (heroScale === 'XL' || heroScale === 'LG') {
            scaleScore = Math.min(25, scaleScore + 3);
        }
        // Bonus for correct CTA scale
        if (ctaScale === 'XL' || ctaScale === 'LG') {
            scaleScore = Math.min(25, scaleScore + 2);
        }
        scaleScore = Math.max(0, scaleScore);

        // Calculate total
        const totalScore = cueScore + attentionScore + narrativePoints + scaleScore;
        const finalScore = Math.min(100, Math.max(0, totalScore));

        // Determine weakest component
        const components = [
            { name: 'cue', score: cueScore, max: 25 },
            { name: 'attention', score: attentionScore, max: 25 },
            { name: 'narrative', score: narrativePoints, max: 25 },
            { name: 'scale', score: scaleScore, max: 25 }
        ];
        components.sort((a, b) => (a.score / a.max) - (b.score / b.max));
        const weakest = components[0].name;

        return {
            score: finalScore,
            weakest: finalScore < 70 ? weakest : null,
            details: {
                cue: cueScore,
                attention: attentionScore,
                narrative: narrativePoints,
                scale: scaleScore,
                cueCoverage: Math.round(cueCoverage * 100)
            }
        };
    }

    // ========================================
    // STAGE 23: CROSS-STAGE DRIFT DETECTOR (Temporal Stability Engine)
    // ========================================

    const SNAPSHOT_STORAGE_KEY = 'jtb_layout_snapshots_v1';
    const MAX_SNAPSHOTS = 3;

    /**
     * Stage 23: Build temporal snapshot from current quality data
     * @param {Object} quality - Quality metrics from API
     * @param {number} consistencyScore - Calculated consistency score
     * @returns {Object} Snapshot with safe metadata only
     */
    function buildTemporalSnapshot(quality, consistencyScore) {
        if (!window.JTB_DEV_MODE) return null;

        const metrics = quality?.metrics || {};
        return {
            timestamp: Date.now(),
            score: quality?.score ?? 0,
            consistency_score: consistencyScore ?? 0,
            narrative_signature: metrics.narrative_signature || '',
            narrative_score: metrics.narrative_score ?? 0,
            total_sections: metrics.total_sections ?? 0,
            dark_sections: metrics.dark_sections ?? 0,
            scale_distribution: {
                xs: metrics.scale_xs_count ?? 0,
                sm: metrics.scale_sm_count ?? 0,
                md: metrics.scale_md_count ?? 0,
                lg: metrics.scale_lg_count ?? 0,
                xl: metrics.scale_xl_count ?? 0
            },
            hero_scale: metrics.hero_scale || null,
            cta_scale: metrics.cta_scale || null,
            attention_stats: {
                high: metrics.attention_high_count ?? 0,
                medium: metrics.attention_medium_count ?? 0,
                low: metrics.attention_low_count ?? 0
            },
            narrative_autofix_applied: metrics.narrative_autofix_applied ?? false
        };
    }

    /**
     * Stage 23: Get snapshots ring buffer from sessionStorage
     * @returns {Array} Array of up to 3 snapshots
     */
    function getTemporalSnapshots() {
        if (!window.JTB_DEV_MODE) return [];
        try {
            const data = sessionStorage.getItem(SNAPSHOT_STORAGE_KEY);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            return [];
        }
    }

    /**
     * Stage 23: Save snapshot to ring buffer (max 3)
     * @param {Object} snapshot - Snapshot to save
     */
    function saveTemporalSnapshot(snapshot) {
        if (!window.JTB_DEV_MODE || !snapshot) return;
        try {
            let snapshots = getTemporalSnapshots();
            snapshots.push(snapshot);
            // Keep only last 3
            if (snapshots.length > MAX_SNAPSHOTS) {
                snapshots = snapshots.slice(-MAX_SNAPSHOTS);
            }
            sessionStorage.setItem(SNAPSHOT_STORAGE_KEY, JSON.stringify(snapshots));
        } catch (e) {
            // Ignore storage errors
        }
    }

    /**
     * Stage 23: Detect temporal drift between two snapshots
     * @param {Object} current - Current snapshot
     * @param {Object} prev - Previous snapshot
     * @returns {Array} Array of drift objects { type, delta, severity }
     */
    function detectTemporalDrift(current, prev) {
        if (!window.JTB_DEV_MODE || !current || !prev) return [];

        const drifts = [];

        // SCORE_DRIFT: |score Δ| >= 10
        const scoreDelta = Math.abs((current.score ?? 0) - (prev.score ?? 0));
        if (scoreDelta >= 10) {
            drifts.push({
                type: 'SCORE',
                delta: scoreDelta,
                severity: scoreDelta >= 20 ? 'high' : (scoreDelta >= 15 ? 'medium' : 'low')
            });
        }

        // CONSISTENCY_DRIFT: |consistency Δ| >= 12
        const consDelta = Math.abs((current.consistency_score ?? 0) - (prev.consistency_score ?? 0));
        if (consDelta >= 12) {
            drifts.push({
                type: 'CONSISTENCY',
                delta: consDelta,
                severity: consDelta >= 20 ? 'high' : (consDelta >= 15 ? 'medium' : 'low')
            });
        }

        // STRUCTURE_DRIFT: narrative_signature differs
        const currSig = current.narrative_signature || '';
        const prevSig = prev.narrative_signature || '';
        if (currSig !== prevSig && (currSig.length > 0 || prevSig.length > 0)) {
            // Calculate how different they are
            const sigDiffLen = Math.abs(currSig.length - prevSig.length);
            const sigMatch = currSig === prevSig;
            drifts.push({
                type: 'STRUCTURE',
                delta: sigMatch ? 0 : Math.max(1, sigDiffLen),
                severity: sigDiffLen >= 3 ? 'high' : (sigDiffLen >= 2 ? 'medium' : 'low'),
                prev: prevSig,
                curr: currSig
            });
        }

        // SCALE_DRIFT: hero_scale or cta_scale changed
        const heroChanged = current.hero_scale !== prev.hero_scale && (current.hero_scale || prev.hero_scale);
        const ctaChanged = current.cta_scale !== prev.cta_scale && (current.cta_scale || prev.cta_scale);
        if (heroChanged || ctaChanged) {
            drifts.push({
                type: 'SCALE',
                delta: (heroChanged ? 1 : 0) + (ctaChanged ? 1 : 0),
                severity: (heroChanged && ctaChanged) ? 'high' : 'medium',
                details: { heroChanged, ctaChanged }
            });
        }

        // ATTENTION_DRIFT: HIGH count changed by >= 2
        const attHighDelta = Math.abs(
            (current.attention_stats?.high ?? 0) - (prev.attention_stats?.high ?? 0)
        );
        if (attHighDelta >= 2) {
            drifts.push({
                type: 'ATTENTION',
                delta: attHighDelta,
                severity: attHighDelta >= 4 ? 'high' : (attHighDelta >= 3 ? 'medium' : 'low')
            });
        }

        // DARK_DRIFT: dark_sections changed by >= 2
        const darkDelta = Math.abs((current.dark_sections ?? 0) - (prev.dark_sections ?? 0));
        if (darkDelta >= 2) {
            drifts.push({
                type: 'DARK',
                delta: darkDelta,
                severity: darkDelta >= 4 ? 'high' : (darkDelta >= 3 ? 'medium' : 'low')
            });
        }

        return drifts;
    }

    /**
     * Stage 23: Calculate Temporal Stability Score (0-100)
     * @param {Array} drifts - Array of detected drifts
     * @returns {Object} { score, level, driftCount, highCount }
     */
    function calculateTemporalStability(drifts) {
        if (!window.JTB_DEV_MODE) return { score: 100, level: 'stable', driftCount: 0, highCount: 0 };

        let stabilityScore = 100;
        let highCount = 0;

        (drifts || []).forEach(drift => {
            if (drift.severity === 'high') {
                stabilityScore -= 15;
                highCount++;
            } else if (drift.severity === 'medium') {
                stabilityScore -= 8;
            } else {
                stabilityScore -= 3;
            }
        });

        stabilityScore = Math.max(0, Math.min(100, stabilityScore));

        let level = 'stable';
        if (stabilityScore < 60) {
            level = 'chaotic';
        } else if (stabilityScore < 85) {
            level = 'unstable';
        }

        return {
            score: stabilityScore,
            level,
            driftCount: (drifts || []).length,
            highCount
        };
    }

    /**
     * Stage 23: Format timestamp for display
     * @param {number} ts - Timestamp
     * @returns {string} Formatted time string
     */
    function formatSnapshotTime(ts) {
        if (!ts) return '—';
        const d = new Date(ts);
        return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    /**
     * Stage 23: Show Timeline popup with last 3 snapshots
     */
    function showTemporalTimeline() {
        if (!window.JTB_DEV_MODE) return;

        const snapshots = getTemporalSnapshots();
        if (snapshots.length === 0) {
            showToast('No snapshots recorded yet', 'info');
            return;
        }

        // Remove existing timeline popup
        document.querySelector('.jtb-debug-timeline-popup')?.remove();

        const popup = document.createElement('div');
        popup.className = 'jtb-debug-timeline-popup';

        let html = `<div class="jtb-debug-timeline-header">
            <span>🧭 Generation Timeline</span>
            <button class="jtb-debug-timeline-close" title="Close">×</button>
        </div>
        <div class="jtb-debug-timeline-entries">`;

        // Show snapshots in reverse order (newest first)
        snapshots.slice().reverse().forEach((snap, idx) => {
            const isLatest = idx === 0;
            const time = formatSnapshotTime(snap.timestamp);
            const scoreClass = snap.score >= 70 ? 'good' : (snap.score >= 40 ? 'warn' : 'bad');
            const consClass = snap.consistency_score >= 85 ? 'good' : (snap.consistency_score >= 60 ? 'warn' : 'bad');

            html += `<div class="jtb-debug-timeline-entry ${isLatest ? 'latest' : ''}">
                <div class="jtb-debug-timeline-time">${time} ${isLatest ? '(latest)' : ''}</div>
                <div class="jtb-debug-timeline-stats">
                    <span class="jtb-debug-timeline-score ${scoreClass}">Score: ${snap.score}</span>
                    <span class="jtb-debug-timeline-cons ${consClass}">Cons: ${snap.consistency_score}</span>
                    <span class="jtb-debug-timeline-sections">Sec: ${snap.total_sections}</span>
                </div>
                <div class="jtb-debug-timeline-sig">NR: ${snap.narrative_signature || '—'}</div>
            </div>`;
        });

        html += `</div>`;
        popup.innerHTML = html;

        // Close button handler
        popup.querySelector('.jtb-debug-timeline-close').addEventListener('click', () => {
            popup.remove();
        });

        // Insert into preview modal
        const previewFrame = JTB_AI.previewModal?.querySelector('.jtb-ai-preview-frame');
        if (previewFrame) {
            previewFrame.appendChild(popup);
        } else {
            document.body.appendChild(popup);
        }
    }

    // ========================================
    // STAGE 24: DECISION GATES + AUTO-LOCK (Stability-Aware Hardening)
    // ========================================

    const AUTO_LOCK_ONCE_KEY = 'jtb_style_lock_autoonce_v1';

    /**
     * Stage 24: Build unified health signal from all metrics
     * @param {Object} quality - Quality metrics from API
     * @param {Object} consistencyObj - Result from calculateConsistencyScore()
     * @param {Object} temporalObj - Result from calculateTemporalStability()
     * @param {Array} drifts - Drifts array from detectTemporalDrift()
     * @returns {Object} Unified health signal
     */
    function buildHealthSignal(quality, consistencyObj, temporalObj, drifts) {
        if (!window.JTB_DEV_MODE) {
            return {
                score: 0, status: 'UNKNOWN', attempt: 0, confidence: 0,
                consistency_score: 0, consistency_level: 'unknown',
                stability_score: 100, stability_level: 'stable',
                drifts_count: 0, drifts_high_count: 0,
                has_critical: false, violations_count: 0, warnings_count: 0
            };
        }

        const metrics = quality?.metrics || {};
        const violations = quality?.violations || [];
        const warnings = quality?.warnings || [];

        // Determine consistency level
        const consScore = consistencyObj?.score ?? 0;
        let consLevel = 'low';
        if (consScore >= 85) consLevel = 'high';
        else if (consScore >= 60) consLevel = 'medium';

        // Check for critical issues
        const hasCritical = violations.some(v =>
            v.includes('DARK_MISUSE') ||
            v.includes('CTA_BEFORE_PROMISE') ||
            v.includes('BROKEN_STORY')
        ) || (quality?.has_dark_misuse === true);

        return {
            score: quality?.score ?? 0,
            status: quality?.decision || quality?.status || 'UNKNOWN',
            attempt: quality?.attempt ?? 0,
            confidence: quality?.confidence ?? 0,
            consistency_score: consScore,
            consistency_level: consLevel,
            stability_score: temporalObj?.score ?? 100,
            stability_level: temporalObj?.level ?? 'stable',
            drifts_count: (drifts || []).length,
            drifts_high_count: temporalObj?.highCount ?? 0,
            has_critical: hasCritical,
            violations_count: violations.length,
            warnings_count: warnings.length
        };
    }

    /**
     * Stage 24: Evaluate decision gates based on health signal
     * @param {Object} signal - Health signal from buildHealthSignal()
     * @returns {Object} { gate_state, reasons, suggested_action }
     */
    function evaluateGates(signal) {
        if (!window.JTB_DEV_MODE) {
            return { gate_state: 'OK', reasons: [], suggested_action: 'NONE' };
        }

        const reasons = [];

        // G1: HARD_FAIL
        if (signal.has_critical) {
            reasons.push('Critical violation detected');
        }
        if (signal.violations_count > 0 && signal.status === 'REJECT') {
            reasons.push(`${signal.violations_count} violation(s) with REJECT status`);
        }
        if (reasons.length > 0 && (signal.has_critical || signal.status === 'REJECT')) {
            return {
                gate_state: 'FAIL',
                reasons,
                suggested_action: 'STOP'
            };
        }

        // G2: CHAOS
        if (signal.stability_level === 'chaotic' || signal.stability_score < 60) {
            reasons.push(`Stability chaotic (${signal.stability_score})`);
        }
        if (signal.drifts_high_count >= 2) {
            reasons.push(`${signal.drifts_high_count} high-severity drifts`);
        }
        if (reasons.length > 0) {
            return {
                gate_state: 'CHAOTIC',
                reasons,
                suggested_action: 'LOCK_STYLE'
            };
        }

        // G3: LOW_CONSISTENCY
        if (signal.consistency_score < 70) {
            reasons.push(`Consistency low (${signal.consistency_score})`);
            return {
                gate_state: 'LOW_CONSISTENCY',
                reasons,
                suggested_action: 'LOCK_STYLE'
            };
        }

        // G4: OK
        return {
            gate_state: 'OK',
            reasons: [],
            suggested_action: 'NONE'
        };
    }

    /**
     * Stage 24: Check if auto-lock one-shot is pending
     * @returns {boolean}
     */
    function isAutoLockOncePending() {
        if (!window.JTB_DEV_MODE) return false;
        try {
            return sessionStorage.getItem(AUTO_LOCK_ONCE_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    /**
     * Stage 24: Set auto-lock one-shot flag
     */
    function setAutoLockOnce() {
        if (!window.JTB_DEV_MODE) return;
        try {
            sessionStorage.setItem(AUTO_LOCK_ONCE_KEY, '1');
        } catch (e) {
            // Ignore
        }
    }

    /**
     * Stage 24: Clear auto-lock one-shot flag
     */
    function clearAutoLockOnce() {
        if (!window.JTB_DEV_MODE) return;
        try {
            sessionStorage.removeItem(AUTO_LOCK_ONCE_KEY);
        } catch (e) {
            // Ignore
        }
    }

    /**
     * Stage 24: Apply auto-lock policy if needed
     * @param {Object} gateResult - Result from evaluateGates()
     * @returns {boolean} True if auto-lock was applied
     */
    function applyAutoLockPolicy(gateResult) {
        if (!window.JTB_DEV_MODE) return false;

        // Check if auto-lock one-shot is pending from previous generation
        if (isAutoLockOncePending()) {
            clearAutoLockOnce();
            // Don't apply new auto-lock, just clear the flag
            return false;
        }

        // Only apply auto-lock if user hasn't manually locked
        if (styleLocked) {
            return false;
        }

        // Apply auto-lock if suggested
        if (gateResult.suggested_action === 'LOCK_STYLE') {
            setAutoLockOnce();

            // Apply style lock for this preview
            const previewFrame = JTB_AI.previewModal?.querySelector('.jtb-ai-preview-frame');
            if (previewFrame) {
                previewFrame.classList.add('jtb-style-locked');
            }

            return true;
        }

        return false;
    }

    /**
     * Stage 24: Build Health Strip HTML
     * @param {Object} signal - Health signal
     * @param {Object} gateResult - Gate evaluation result
     * @returns {string} HTML string
     */
    function buildHealthStripHtml(signal, gateResult) {
        if (!window.JTB_DEV_MODE) return '';

        // CONS badge
        const consClass = signal.consistency_score >= 85 ? 'high' : (signal.consistency_score >= 60 ? 'medium' : 'low');

        // STAB badge
        const stabClass = signal.stability_level;

        // DRIFT badge
        const driftStr = `${signal.drifts_high_count}H/${signal.drifts_count}T`;

        // CONF badge
        const confClass = signal.confidence >= 70 ? 'high' : (signal.confidence >= 40 ? 'medium' : 'low');

        // GATE badge
        const gateClass = gateResult.gate_state.toLowerCase().replace('_', '-');
        const gateTooltip = gateResult.reasons.length > 0
            ? `Gate: ${gateResult.gate_state}\nReasons: ${gateResult.reasons.join(', ')}\nAction: ${gateResult.suggested_action}`
            : `Gate: ${gateResult.gate_state}`;

        let html = `<div class="jtb-debug-health-strip">`;
        html += `<span class="jtb-debug-hs-item jtb-debug-hs-cons-${consClass}" title="Consistency: ${signal.consistency_score}/100">CONS ${signal.consistency_score}</span>`;
        html += `<span class="jtb-debug-hs-item jtb-debug-hs-stab-${stabClass}" title="Stability: ${signal.stability_score}/100">STAB ${signal.stability_score}</span>`;
        html += `<span class="jtb-debug-hs-item jtb-debug-hs-drift" title="Drifts: ${signal.drifts_high_count} high / ${signal.drifts_count} total">DRIFT ${driftStr}</span>`;
        html += `<span class="jtb-debug-hs-item jtb-debug-hs-conf-${confClass}" title="Confidence: ${signal.confidence}%">CONF ${signal.confidence}</span>`;
        html += `<span class="jtb-debug-hs-item jtb-debug-gate jtb-debug-gate-${gateClass}" title="${gateTooltip}">GATE: ${gateResult.gate_state}</span>`;
        html += `</div>`;

        return html;
    }

    /**
     * Stage 24: Build gate warning badges HTML
     * @param {Object} gateResult - Gate evaluation result
     * @param {boolean} autoLockApplied - Whether auto-lock was applied
     * @returns {string} HTML string
     */
    function buildGateWarningsHtml(gateResult, autoLockApplied) {
        if (!window.JTB_DEV_MODE) return '';

        let html = '';

        if (gateResult.gate_state === 'FAIL') {
            const tooltip = `Hard fail detected\nReasons: ${gateResult.reasons.join(', ')}\nReview violations before proceeding`;
            html += `<span class="jtb-debug-stop-badge" title="${tooltip}">⛔ STOP</span>`;
            html += `<span class="jtb-debug-rec" title="Review violations">Hard fail detected — review violations</span>`;
        } else if (gateResult.gate_state === 'CHAOTIC') {
            const tooltip = `Chaotic generation detected\nReasons: ${gateResult.reasons.join(', ')}`;
            html += `<span class="jtb-debug-chaotic-badge" title="${tooltip}">⚠ CHAOTIC</span>`;
            if (autoLockApplied) {
                html += `<span class="jtb-debug-rec jtb-debug-rec-applied" title="Style lock auto-applied for next generation">Recommend: lock style (auto-applied once)</span>`;
            } else {
                html += `<span class="jtb-debug-rec" title="Consider locking style">Recommend: lock style</span>`;
            }
        } else if (gateResult.gate_state === 'LOW_CONSISTENCY') {
            const tooltip = `Low consistency detected\nReasons: ${gateResult.reasons.join(', ')}`;
            html += `<span class="jtb-debug-lowcons-badge" title="${tooltip}">⚠ LOW_CONSISTENCY</span>`;
            if (autoLockApplied) {
                html += `<span class="jtb-debug-rec jtb-debug-rec-applied" title="Style lock auto-applied for next generation">Recommend: lock style (auto-applied once)</span>`;
            } else {
                html += `<span class="jtb-debug-rec" title="Consider locking style">Recommend: lock style</span>`;
            }
        }

        return html;
    }

    /**
     * Stage 25: Preview Parity Guard
     * Verifies that preview iframe has properly rendered all debug classes and CSS variables
     * Detects render failures, missing styles, or broken CSS cascade
     *
     * @param {HTMLElement} previewFrame - The preview iframe content document or frame element
     */
    function checkPreviewParity(previewFrame) {
        if (!window.JTB_DEV_MODE) return;
        if (!previewFrame) return;

        const checks = {
            hasRenderedClass: false,
            hasGpRadiusToken: false,
            hasCueBarClass: false
        };
        const failures = [];

        try {
            // Get the document - handle both iframe and direct element
            let doc = previewFrame;
            if (previewFrame.contentDocument) {
                doc = previewFrame.contentDocument;
            } else if (previewFrame.ownerDocument) {
                doc = previewFrame.ownerDocument;
            }

            const root = doc.documentElement || doc.body;
            if (!root) {
                failures.push('NO_ROOT');
            } else {
                // Check A: Root has .jtb-ai-preview-rendered class
                const previewContainer = doc.querySelector('.jtb-ai-preview-rendered, .jtb-preview-content');
                checks.hasRenderedClass = !!previewContainer;
                if (!checks.hasRenderedClass) {
                    failures.push('NO_RENDERED_CLASS');
                }

                // Check B: --jtb-gp-radius CSS variable exists (Stage 19 token)
                const computedStyle = window.getComputedStyle(root);
                const gpRadius = computedStyle.getPropertyValue('--jtb-gp-radius');
                checks.hasGpRadiusToken = gpRadius && gpRadius.trim() !== '';
                if (!checks.hasGpRadiusToken) {
                    // Also check on sections
                    const section = doc.querySelector('.jtb-section');
                    if (section) {
                        const sectionStyle = window.getComputedStyle(section);
                        const sectionRadius = sectionStyle.getPropertyValue('--jtb-gp-radius');
                        checks.hasGpRadiusToken = sectionRadius && sectionRadius.trim() !== '';
                    }
                }
                if (!checks.hasGpRadiusToken) {
                    failures.push('NO_GP_RADIUS_TOKEN');
                }

                // Check C: At least one Stage 21 cue bar class exists
                const cueBarClasses = [
                    '.jtb-cue-active',
                    '.jtb-cue-climax',
                    '.jtb-cue-relief',
                    '.jtb-section[data-cue-type]'
                ];
                checks.hasCueBarClass = cueBarClasses.some(selector => {
                    try {
                        return doc.querySelector(selector) !== null;
                    } catch (e) {
                        return false;
                    }
                });
                // Cue bar is optional - only fail if sections exist but no cues
                const sections = doc.querySelectorAll('.jtb-section');
                if (sections.length > 0 && !checks.hasCueBarClass) {
                    // Soft warning - cue bars may not be applied yet
                    // failures.push('NO_CUE_BAR_CLASS'); // Commenting out - not critical
                }
            }
        } catch (e) {
            console.warn('[Stage 25] Parity check error:', e);
            failures.push('CHECK_ERROR');
        }

        // Store parity result for debug banner
        JTB_AI.parityResult = {
            checks: checks,
            failures: failures,
            passed: failures.length === 0,
            timestamp: Date.now()
        };

        // Update debug banner if it exists
        updateParityBadge(failures);
    }

    /**
     * Stage 25: Update parity fail badge in debug banner
     * @param {Array} failures - Array of failure codes
     */
    function updateParityBadge(failures) {
        if (!window.JTB_DEV_MODE) return;

        // Find or create parity badge container
        const banner = document.querySelector('.jtb-debug-banner-main');
        if (!banner) return;

        // Remove existing parity badge
        const existingBadge = banner.querySelector('.jtb-debug-parity-badge');
        if (existingBadge) {
            existingBadge.remove();
        }

        // Add badge if there are failures
        if (failures.length > 0) {
            const tooltip = `Preview Parity Check Failed\n\nFailures:\n${failures.map(f => '• ' + f).join('\n')}\n\nThis may indicate:\n- CSS not loaded properly\n- Preview frame isolation issue\n- Missing stage CSS variables`;

            const badge = document.createElement('span');
            badge.className = 'jtb-debug-parity-badge jtb-debug-parity-fail';
            badge.setAttribute('title', tooltip);
            badge.innerHTML = '⚠ PARITY_FAIL';

            // Insert after health strip or at start
            const healthStrip = banner.querySelector('.jtb-debug-health-strip');
            if (healthStrip) {
                healthStrip.after(badge);
            } else {
                banner.insertBefore(badge, banner.firstChild);
            }
        }
    }

    /**
     * Stage 20: Initialize IntersectionObserver for section in-view tracking
     */
    function initSectionObserver() {
        if (!window.JTB_DEV_MODE) return;

        const previewFrame = JTB_AI.previewModal?.querySelector('.jtb-ai-preview-frame');
        if (!previewFrame) return;

        // Cleanup existing observer
        if (sectionObserver) {
            sectionObserver.disconnect();
        }

        // Debounced callback
        let debounceTimer = null;
        const debouncedCallback = (entries) => {
            if (debounceTimer) clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('jtb-in-view');
                    } else {
                        entry.target.classList.remove('jtb-in-view');
                    }
                });
            }, 50);
        };

        // Create observer with preview frame as root
        sectionObserver = new IntersectionObserver(debouncedCallback, {
            root: previewFrame,
            rootMargin: '-20% 0px -20% 0px',
            threshold: 0.3
        });

        // Observe all sections
        previewFrame.querySelectorAll('.jtb-section').forEach(section => {
            sectionObserver.observe(section);
        });
    }

    /**
     * Stage 20: Create debug quick filters toolbar (DEV_MODE only)
     */
    function createDebugFilters() {
        if (!window.JTB_DEV_MODE) return null;

        const filters = document.createElement('div');
        filters.className = 'jtb-debug-filters';

        const filterTypes = [
            { id: 'problem', label: 'PROBLEM', title: 'Show only PROBLEM sections' },
            { id: 'promise', label: 'PROMISE', title: 'Show only PROMISE sections' },
            { id: 'proof', label: 'PROOF', title: 'Show only PROOF sections' },
            { id: 'cta', label: 'CTA', title: 'Show only CTA/RESOLUTION sections' },
            { id: 'dark', label: 'DARK', title: 'Show only DARK context sections' },
            { id: 'xl', label: 'XL', title: 'Show only XL scale sections' }
        ];

        filterTypes.forEach(filter => {
            const chip = document.createElement('button');
            chip.className = 'jtb-debug-filter-chip';
            chip.setAttribute('data-filter', filter.id);
            chip.setAttribute('title', filter.title);
            chip.textContent = filter.label;

            chip.addEventListener('click', () => {
                toggleDebugFilter(filter.id, chip);
            });

            filters.appendChild(chip);
        });

        return filters;
    }

    /**
     * Stage 20: Toggle debug filter (CSS class on preview root)
     */
    function toggleDebugFilter(filterId, chipEl) {
        const previewFrame = JTB_AI.previewModal?.querySelector('.jtb-ai-preview-frame');
        if (!previewFrame) return;

        // Remove all filter classes
        previewFrame.classList.remove(
            'jtb-debug-filter-problem',
            'jtb-debug-filter-promise',
            'jtb-debug-filter-proof',
            'jtb-debug-filter-cta',
            'jtb-debug-filter-dark',
            'jtb-debug-filter-xl'
        );

        // Deactivate all chips
        previewFrame.parentElement?.querySelectorAll('.jtb-debug-filter-chip').forEach(chip => {
            chip.classList.remove('active');
        });

        // Toggle filter
        if (activeDebugFilter === filterId) {
            activeDebugFilter = null;
        } else {
            activeDebugFilter = filterId;
            previewFrame.classList.add(`jtb-debug-filter-${filterId}`);
            chipEl.classList.add('active');
        }
    }

    /**
     * Stage 20: Build current debug snapshot for comparison
     */
    function buildDebugSnapshot(quality) {
        if (!quality) return null;

        const metrics = quality.metrics || {};
        return {
            score: quality.score ?? 0,
            confidence: quality.confidence ?? 0,
            narrative_score: metrics.narrative_score ?? 0,
            warnings_count: (quality.warnings || []).length,
            violations_count: (quality.violations || []).length,
            total_sections: metrics.total_sections ?? 0,
            autofix_count: quality.autofix_rules?.length ?? 0
        };
    }

    /**
     * Stage 20: Compare current snapshot with last and show diff
     */
    function compareSnapshots(currentQuality) {
        const current = buildDebugSnapshot(currentQuality);
        if (!current) {
            showToast('No current data to compare', 'warning');
            return;
        }

        if (!lastDebugSnapshot) {
            showToast('No previous snapshot to compare', 'info');
            // Store current as last for next comparison
            lastDebugSnapshot = current;
            return;
        }

        // Build diff
        const diff = {
            score: current.score - lastDebugSnapshot.score,
            confidence: current.confidence - lastDebugSnapshot.confidence,
            narrative_score: current.narrative_score - lastDebugSnapshot.narrative_score,
            warnings: current.warnings_count - lastDebugSnapshot.warnings_count,
            violations: current.violations_count - lastDebugSnapshot.violations_count
        };

        // Show diff in banner
        showDiffBanner(diff);

        // Update last snapshot
        lastDebugSnapshot = current;
    }

    /**
     * Stage 20: Display diff banner in preview
     */
    function showDiffBanner(diff) {
        const previewFrame = JTB_AI.previewModal?.querySelector('.jtb-ai-preview-frame');
        if (!previewFrame) return;

        // Remove existing diff banner
        previewFrame.querySelector('.jtb-debug-diff')?.remove();

        const diffBanner = document.createElement('div');
        diffBanner.className = 'jtb-debug-diff';

        const formatDelta = (value, label, invertColors = false) => {
            const sign = value > 0 ? '+' : '';
            let cls = 'neutral';
            if (value !== 0) {
                if (invertColors) {
                    cls = value > 0 ? 'negative' : 'positive';
                } else {
                    cls = value > 0 ? 'positive' : 'negative';
                }
            }
            return `<span class="jtb-debug-diff-item ${cls}">Δ${label}: ${sign}${value}</span>`;
        };

        diffBanner.innerHTML = `
            ${formatDelta(diff.score, 'score')}
            ${formatDelta(diff.confidence, 'conf')}
            ${formatDelta(diff.narrative_score, 'narr')}
            ${formatDelta(diff.warnings, 'warn', true)}
            ${formatDelta(diff.violations, 'viol', true)}
        `;

        // Insert after debug banner
        const debugBanner = previewFrame.querySelector('.jtb-debug-banner');
        if (debugBanner) {
            debugBanner.after(diffBanner);
        } else {
            previewFrame.insertBefore(diffBanner, previewFrame.firstChild);
        }

        showToast('Snapshot compared!', 'success');
    }

    /**
     * Stage 20: Lazy debug overlays - remove when debug OFF
     */
    function cleanupDebugOverlays() {
        const previewFrame = JTB_AI.previewModal?.querySelector('.jtb-ai-preview-frame');
        if (!previewFrame) return;

        // Remove all debug elements from DOM (not just hide)
        previewFrame.querySelectorAll('.jtb-debug-overlay, .jtb-debug-filters, .jtb-debug-diff').forEach(el => {
            el.remove();
        });

        previewFrame.classList.remove('jtb-debug-enabled');

        // Cleanup observer
        if (sectionObserver) {
            sectionObserver.disconnect();
            sectionObserver = null;
        }

        // Remove in-view classes
        previewFrame.querySelectorAll('.jtb-in-view').forEach(el => {
            el.classList.remove('jtb-in-view');
        });

        // Reset filter
        activeDebugFilter = null;
    }

    function insertLayout() {
        if (!JTB_AI.generatedLayout) {
            showError('No layout to insert');
            return;
        }

        if (!window.JTB || !window.JTB.state) {
            showError('Builder not ready. Please try again.');
            return;
        }

        try {
            const sections = JTB_AI.generatedLayout.sections ?? JTB_AI.generatedLayout.content ?? [];

            // DEBUG: Log what we're inserting
            if (sections[0]) {
                if (sections[0].children?.[0]) {
                    if (sections[0].children[0].children?.[0]?.children?.[0]) {
                        const firstModule = sections[0].children[0].children[0].children[0];
                    }
                }
            }

            const processedSections = sections.map(section => ensureSectionStructure(section));

            window.JTB.state.content = {
                version: '1.0',
                content: processedSections
            };

            window.JTB.state.isDirty = true;

            if (typeof window.JTB.renderCanvas === 'function') {
                window.JTB.renderCanvas();
            }

            showSuccess('Layout inserted successfully!');
            closePreviewModal();
            closePanel();

        } catch (error) {
            console.error('Insert layout error:', error);
            showError('Failed to insert layout');
        }
    }

    function insertSectionIntoBuilder(section) {
        if (!window.JTB || !window.JTB.state) {
            showError('Builder not ready');
            return;
        }

        if (!window.JTB.state.content) {
            window.JTB.state.content = { version: '1.0', content: [] };
        }
        if (!window.JTB.state.content.content) {
            window.JTB.state.content.content = [];
        }

        window.JTB.state.content.content.push(section);
        window.JTB.state.isDirty = true;

        if (typeof window.JTB.renderCanvas === 'function') {
            window.JTB.renderCanvas();
        }
    }

    function ensureSectionStructure(section) {
        // Ensure section has type
        if (!section.type) {
            section.type = 'section';
        }
        if (!section.id) {
            section.id = 'section_' + generateId();
        }
        if (!section.attrs) {
            section.attrs = {};
        }
        if (!section.children) {
            section.children = [];
        }

        section.children = section.children.map(row => {
            // Ensure row has type
            if (!row.type) {
                row.type = 'row';
            }
            if (!row.id) {
                row.id = 'row_' + generateId();
            }
            if (!row.attrs) {
                row.attrs = {};
            }
            if (!row.children) {
                row.children = [];
            }

            row.children = row.children.map(col => {
                // Ensure column has type
                if (!col.type) {
                    col.type = 'column';
                }
                if (!col.id) {
                    col.id = 'column_' + generateId();
                }
                if (!col.attrs) {
                    col.attrs = {};
                }
                if (!col.children) {
                    col.children = [];
                }

                col.children = col.children.map(mod => {
                    // Module must have type - skip invalid modules
                    if (!mod.type) {
                        console.warn('[AI] Skipping module without type:', mod);
                        return null;
                    }
                    if (!mod.id) {
                        mod.id = mod.type + '_' + generateId();
                    }
                    if (!mod.attrs) {
                        mod.attrs = {};
                    }
                    return mod;
                }).filter(Boolean); // Remove nulls

                return col;
            });

            return row;
        });

        return section;
    }

    function generateId() {
        return Math.random().toString(16).substr(2, 16);
    }

    // ========================================
    // Helpers
    // ========================================

    function formatPatternName(name) {
        if (!name) return '';
        return name
            .replace(/_/g, ' ')
            .replace(/\b\w/g, l => l.toUpperCase());
    }

    // ========================================
    // Icon Map for Preview (kept for fallback)
    // ========================================

    const ICON_MAP = {
        'users': '👥', 'award': '🏆', 'headphones': '🎧', 'star': '⭐',
        'check': '✓', 'shield': '🛡️', 'zap': '⚡', 'heart': '❤️',
        'globe': '🌐', 'clock': '⏰', 'mail': '✉️', 'phone': '📞',
        'map-pin': '📍', 'calendar': '📅', 'settings': '⚙️', 'lock': '🔒',
        'eye': '👁️', 'home': '🏠', 'user': '👤', 'search': '🔍',
        'play': '▶️', 'pause': '⏸️', 'download': '⬇️', 'upload': '⬆️',
        'trash': '🗑️', 'edit': '✏️', 'plus': '➕', 'minus': '➖',
        'arrow-right': '→', 'arrow-left': '←', 'arrow-up': '↑', 'arrow-down': '↓',
        'check-circle': '✅', 'x-circle': '❌', 'info': 'ℹ️', 'alert-triangle': '⚠️',
        'trending-up': '📈', 'trending-down': '📉', 'dollar-sign': '💰', 'credit-card': '💳',
        'package': '📦', 'truck': '🚚', 'shopping-cart': '🛒', 'gift': '🎁',
        'camera': '📷', 'image': '🖼️', 'film': '🎬', 'music': '🎵',
        'code': '💻', 'terminal': '⌨️', 'database': '🗄️', 'server': '🖥️',
        'wifi': '📶', 'bluetooth': '🔵', 'battery': '🔋', 'cpu': '🔧',
        'layers': '📚', 'grid': '▦', 'layout': '📐', 'sidebar': '📑',
        'book': '📖', 'bookmark': '🔖', 'file': '📄', 'folder': '📁',
        'message-circle': '💬', 'message-square': '📩', 'send': '📤', 'inbox': '📥'
    };

    // ========================================
    // JS-based Preview Rendering (FALLBACK ONLY)
    // Primary preview now uses /api/jtb/render
    // ========================================

    function renderPreviewFallback(layout) {
        const sections = layout?.sections ?? layout?.content ?? [];
        if (!sections.length) {
            return '<div style="padding:60px 20px;text-align:center;color:#6b7280;font-family:Inter,sans-serif;">No content generated</div>';
        }

        const styles = `
            <style>
                * { box-sizing: border-box; }
                .jtb-preview {
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    line-height: 1.6;
                    color: #1f2937;
                    background: #fff;
                }
                .jtb-section { width: 100%; overflow: hidden; }
                .jtb-container { max-width: 100%; margin: 0 auto; padding: 0 16px; }
                .jtb-row { display: flex; flex-wrap: nowrap; margin: 0 -8px; align-items: stretch; }
                .jtb-col { padding: 0 8px; min-width: 0; }
                .jtb-h1 { font-size: 32px; font-weight: 800; line-height: 1.1; margin: 0 0 16px; color: #111827; }
                .jtb-h2 { font-size: 28px; font-weight: 700; line-height: 1.2; margin: 0 0 14px; color: #111827; }
                .jtb-h3 { font-size: 22px; font-weight: 700; line-height: 1.3; margin: 0 0 12px; color: #111827; }
                .jtb-h4 { font-size: 18px; font-weight: 600; line-height: 1.4; margin: 0 0 10px; color: #111827; }
                .jtb-text { font-size: 14px; line-height: 1.7; color: #4b5563; margin: 0 0 14px; }
                .jtb-btn {
                    display: inline-block; padding: 12px 24px; font-size: 14px; font-weight: 600;
                    text-decoration: none; border-radius: 8px; transition: all 0.2s; cursor: pointer;
                    border: none; text-align: center;
                }
                .jtb-btn-primary { background: #3b82f6; color: #fff; }
                .jtb-btn-secondary { background: #f3f4f6; color: #1f2937; }
                .jtb-btn-white { background: #fff; color: #3b82f6; }
                .jtb-card {
                    background: #fff; border-radius: 12px;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
                    padding: 20px; height: 100%;
                }
                .jtb-icon-wrap {
                    width: 48px; height: 48px; border-radius: 12px;
                    display: flex; align-items: center; justify-content: center;
                    font-size: 20px; margin-bottom: 12px;
                }
                .jtb-avatar { width: 64px; height: 64px; border-radius: 50%; overflow: hidden; border: 3px solid #e5e7eb; }
                .jtb-avatar img { width: 100%; height: 100%; object-fit: cover; }
                .jtb-input {
                    width: 100%; padding: 10px 14px; font-size: 14px;
                    border: 1px solid #d1d5db; border-radius: 6px; margin-bottom: 12px; font-family: inherit;
                }
                .jtb-textarea { min-height: 80px; resize: vertical; }
                .jtb-grid { display: grid; gap: 16px; }
                .jtb-grid-2 { grid-template-columns: repeat(2, 1fr); }
                .jtb-grid-3 { grid-template-columns: repeat(3, 1fr); }
                .jtb-grid-4 { grid-template-columns: repeat(4, 1fr); }
            </style>
        `;

        function getColWidth(width) {
            const map = {
                '1_1': '100%', '1_2': '50%', '1_3': '33.333%', '2_3': '66.666%',
                '1_4': '25%', '3_4': '75%', '1_5': '20%', '2_5': '40%',
                '3_5': '60%', '4_5': '80%', '1_6': '16.666%', '5_6': '83.333%'
            };
            return map[width] || '100%';
        }

        let html = styles + '<div class="jtb-preview">';

        sections.forEach((section, sIdx) => {
            const sAttrs = section.attrs || {};
            const bg = sAttrs.background_color || (sIdx % 2 === 0 ? '#ffffff' : '#f9fafb');
            const pTop = Math.min(sAttrs.padding?.top || 40, 60);
            const pBottom = Math.min(sAttrs.padding?.bottom || 40, 60);

            html += `<div class="jtb-section" style="background:${bg};padding:${pTop}px 0 ${pBottom}px;">`;
            html += '<div class="jtb-container">';

            (section.children || []).forEach(row => {
                html += '<div class="jtb-row">';
                (row.children || []).forEach(col => {
                    const w = getColWidth(col.attrs?.width || '1_1');
                    html += `<div class="jtb-col" style="width:${w};flex:0 0 ${w};">`;
                    (col.children || []).forEach(mod => {
                        html += renderModuleFallback(mod);
                    });
                    html += '</div>';
                });
                html += '</div>';
            });

            html += '</div></div>';
        });

        html += '</div>';
        return html;
    }

    function renderModuleFallback(mod) {
        const type = mod.type || 'unknown';
        const a = mod.attrs || {};
        const children = mod.children || [];

        switch (type) {
            case 'section':
            case 'row':
            case 'column':
                return children.map(c => renderModuleFallback(c)).join('');

            case 'heading': {
                const lvl = a.level || 'h2';
                const sz = Math.min(a.font_size || 28, 36);
                const clr = a.text_color || '#111827';
                const align = a.text_align || 'left';
                return `<${lvl} class="jtb-${lvl}" style="font-size:${sz}px;color:${clr};text-align:${align};">${a.text || ''}</${lvl}>`;
            }

            case 'text': {
                const clr = a.text_color || '#4b5563';
                const sz = a.font_size || 14;
                return `<div class="jtb-text" style="color:${clr};font-size:${sz}px;">${a.content || ''}</div>`;
            }

            case 'image': {
                const src = a.src || a.image_url || '';
                const rad = a.border_radius?.top_left || 8;
                if (src) {
                    return `<div style="margin-bottom:14px;"><img src="${src}" alt="${a.alt || ''}" style="max-width:100%;height:auto;border-radius:${rad}px;display:block;" /></div>`;
                }
                return `<div style="background:#e5e7eb;height:180px;border-radius:${rad}px;display:flex;align-items:center;justify-content:center;color:#9ca3af;margin-bottom:14px;">📷 Image</div>`;
            }

            case 'button': {
                const bg = a.background_color || '#3b82f6';
                const clr = a.text_color || '#fff';
                const txt = a.text || 'Button';
                const align = a.align || 'left';
                return `<div style="text-align:${align};margin:14px 0;"><a href="${a.link_url || '#'}" class="jtb-btn" style="background:${bg};color:${clr};">${txt}</a></div>`;
            }

            case 'blurb': {
                const icon = a.font_icon || 'star';
                const iconClr = a.icon_color || '#3b82f6';
                const title = a.title || '';
                const content = (a.content || '').replace(/<[^>]*>/g, '');
                const align = a.text_orientation || 'center';
                return `
                    <div class="jtb-card" style="text-align:${align};">
                        <div class="jtb-icon-wrap" style="background:${iconClr}15;color:${iconClr};${align === 'center' ? 'margin:0 auto 12px;' : 'margin:0 0 12px;'}">
                            ${ICON_MAP[icon] || '⭐'}
                        </div>
                        <div class="jtb-h4" style="color:#111827;">${title}</div>
                        <p class="jtb-text" style="margin:0;">${content}</p>
                    </div>`;
            }

            case 'testimonial': {
                const img = a.portrait_url || '';
                const content = (a.content || '').replace(/<[^>]*>/g, '');
                const author = a.author || '';
                const job = a.job_title || '';
                const company = a.company || '';
                return `
                    <div class="jtb-card" style="text-align:center;">
                        ${img ? `<div class="jtb-avatar" style="margin:0 auto 12px;"><img src="${img}" alt="${author}" onerror="this.style.display='none'" /></div>` : '<div style="width:64px;height:64px;border-radius:50%;background:#e5e7eb;margin:0 auto 12px;"></div>'}
                        <div style="font-size:20px;color:#3b82f6;margin-bottom:8px;font-family:Georgia,serif;">"</div>
                        <p style="color:#374151;font-style:italic;line-height:1.6;margin-bottom:12px;font-size:13px;">${content || 'Great experience!'}</p>
                        ${author ? `<div style="font-weight:600;color:#111827;font-size:14px;">${author}</div>` : ''}
                        ${(job || company) ? `<div style="color:#6b7280;font-size:12px;">${job}${job && company ? ', ' : ''}${company}</div>` : ''}
                    </div>`;
            }

            case 'team_member': {
                const img = a.image || a.photo_url || '';
                const name = a.name || '';
                const pos = a.position || '';
                const bio = (a.content || a.bio || '').replace(/<[^>]*>/g, '');
                return `
                    <div class="jtb-card" style="text-align:center;">
                        ${img ? `<div style="width:80px;height:80px;border-radius:50%;overflow:hidden;margin:0 auto 12px;border:3px solid #e5e7eb;"><img src="${img}" alt="${name}" style="width:100%;height:100%;object-fit:cover;" /></div>` : '<div style="width:80px;height:80px;border-radius:50%;background:#e5e7eb;margin:0 auto 12px;"></div>'}
                        ${name ? `<div class="jtb-h4" style="margin-bottom:2px;">${name}</div>` : ''}
                        ${pos ? `<div style="color:#3b82f6;font-size:12px;font-weight:500;margin-bottom:8px;">${pos}</div>` : ''}
                        ${bio ? `<p class="jtb-text" style="font-size:12px;margin:0;">${bio}</p>` : ''}
                    </div>`;
            }

            case 'pricing_table': {
                const title = a.title || 'Plan';
                const price = a.price || '$0';
                const period = a.period || '/month';
                const features = (a.features || '').split('\n').filter(f => f.trim());
                const btn = a.button_text || 'Get Started';
                const featured = a.featured;
                return `
                    <div class="jtb-card" style="text-align:center;${featured ? 'border:2px solid #3b82f6;transform:scale(1.02);' : 'border:1px solid #e5e7eb;'}">
                        ${featured ? '<div style="background:#3b82f6;color:#fff;padding:4px 12px;border-radius:12px;font-size:10px;font-weight:700;display:inline-block;margin-bottom:10px;">POPULAR</div>' : ''}
                        <div class="jtb-h4">${title}</div>
                        <div style="margin:12px 0;"><span style="font-size:36px;font-weight:800;color:#111827;">${price}</span><span style="color:#6b7280;font-size:14px;">${period}</span></div>
                        <ul style="list-style:none;padding:0;margin:0 0 16px;text-align:left;">
                            ${features.map(f => `<li style="padding:6px 0;border-bottom:1px solid #f3f4f6;color:#4b5563;font-size:13px;">✓ ${f}</li>`).join('')}
                        </ul>
                        <a href="${a.link_url || '#'}" class="jtb-btn ${featured ? 'jtb-btn-primary' : 'jtb-btn-secondary'}" style="display:block;">${btn}</a>
                    </div>`;
            }

            default:
                return `<div style="padding:14px;background:#fef3c7;border:1px dashed #f59e0b;border-radius:6px;color:#92400e;text-align:center;font-size:12px;">[${type}]</div>`;
        }
    }

    // ========================================
    // UI Helpers
    // ========================================

    // Loading overlay state
    let loadingStepInterval = null;
    let currentLoadingStep = 1;

    function showProgress(message) {
        // Show the new loading overlay
        const overlay = document.getElementById('jtb-ai-loading-overlay');
        const titleEl = document.getElementById('jtb-ai-loading-title');

        if (overlay) {
            overlay.classList.add('is-visible');
            if (titleEl && message) {
                titleEl.textContent = message;
            }

            // Reset steps
            currentLoadingStep = 1;
            updateLoadingSteps(1);

            // Auto-advance steps for visual feedback
            loadingStepInterval = setInterval(() => {
                currentLoadingStep++;
                if (currentLoadingStep <= 4) {
                    updateLoadingSteps(currentLoadingStep);
                }
            }, 3000); // Advance every 3 seconds
        }

        // Also show old progress bar for backwards compatibility
        const progressEl = JTB_AI.panel?.querySelector('.jtb-ai-progress');
        const textEl = progressEl?.querySelector('.jtb-ai-progress-text');
        if (progressEl && textEl) {
            textEl.textContent = message;
            progressEl.classList.add('is-visible');
        }
    }

    function hideProgress() {
        // Hide loading overlay
        const overlay = document.getElementById('jtb-ai-loading-overlay');
        if (overlay) {
            overlay.classList.remove('is-visible');
        }

        // Clear step interval
        if (loadingStepInterval) {
            clearInterval(loadingStepInterval);
            loadingStepInterval = null;
        }

        // Reset steps
        updateLoadingSteps(0);

        // Also hide old progress bar
        const progressEl = JTB_AI.panel?.querySelector('.jtb-ai-progress');
        if (progressEl) {
            progressEl.classList.remove('is-visible');
        }
    }

    function updateLoadingSteps(activeStep) {
        const steps = document.querySelectorAll('.jtb-ai-loading-step');
        steps.forEach((step, index) => {
            const stepNum = index + 1;
            step.classList.remove('active', 'completed');

            if (stepNum < activeStep) {
                step.classList.add('completed');
                step.querySelector('.step-icon').textContent = '✓';
            } else if (stepNum === activeStep) {
                step.classList.add('active');
                step.querySelector('.step-icon').textContent = '●';
            } else {
                step.querySelector('.step-icon').textContent = '○';
            }
        });
    }

    function showError(message) {
        showToast(message, 'error');
    }

    function showSuccess(message) {
        showToast(message, 'success');
    }

    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `jtb-ai-toast jtb-ai-toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, type === 'error' ? 4000 : 3000);
    }

    // ========================================
    // Initialize
    // ========================================

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose to global scope - preserve state object and add methods
    Object.assign(JTB_AI, {
        init,
        openPanel,
        closePanel,
        switchTab,
        loadPatterns,
        handleProviderChange
    });
    window.JTB_AI = JTB_AI;

})();
