/**
 * JTB AI Multi-Agent System JavaScript
 *
 * Handles the multi-agent website generation flow:
 * 1. User fills panel → AI generates HTML mockup
 * 2. User views mockup, can iterate ("make hero darker")
 * 3. User accepts mockup → AI builds exact JTB JSON
 *
 * Flow: Mockup → Iteration → Accept → Build → Apply
 *
 * ZERO HARDCODES - All module info from backend dynamically
 *
 * @package JessieThemeBuilder
 * @since 2.0.0
 */

(function() {
    'use strict';

    // ========================================
    // State
    // ========================================

    const JTB_MultiAgent = {
        // Session state
        sessionId: null,
        phase: 'input',  // input → generating → mockup → iterating → building → done
        status: null,

        // User input
        prompt: '',
        industry: 'general',
        style: 'modern',
        pages: ['home', 'about', 'services', 'contact'],

        // Mockup data
        mockupHtml: null,
        mockupStructure: null,
        iterationHistory: [],

        // Build data
        buildSteps: [],
        currentStepIndex: 0,
        finalWebsite: null,

        // Stats
        stats: {
            totalTimeMs: 0,
            totalTokens: 0,
            stepsCompleted: 0,
            mockupIterations: 0
        },

        // UI elements
        modal: null,
        previewFrame: null,
        progressBar: null,

        // Config
        csrfToken: '',
        apiUrl: '/api/jtb/ai',

        // Event listeners tracking for cleanup
        _listeners: [],

        // Available industries (from backend)
        availableIndustries: {
            'general': 'General',
            'technology': 'Technology / SaaS',
            'healthcare': 'Healthcare / Medical',
            'legal': 'Legal / Law Firm',
            'restaurant': 'Restaurant / Food',
            'real_estate': 'Real Estate',
            'fitness': 'Fitness / Gym',
            'agency': 'Creative Agency',
            'ecommerce': 'E-Commerce',
            'education': 'Education / Courses'
        },

        // Available styles
        availableStyles: {
            'modern': 'Modern',
            'corporate': 'Corporate',
            'minimal': 'Minimal',
            'bold': 'Bold',
            'elegant': 'Elegant',
            'playful': 'Playful',
            'dark': 'Dark Mode'
        },

        // Available pages
        availablePages: {
            'home': 'Home',
            'about': 'About Us',
            'services': 'Services',
            'contact': 'Contact',
            'pricing': 'Pricing',
            'portfolio': 'Portfolio',
            'blog': 'Blog',
            'faq': 'FAQ',
            'team': 'Team',
            'testimonials': 'Testimonials'
        }
    };

    // ========================================
    // Initialization
    // ========================================

    /**
     * Initialize the Multi-Agent system
     * @param {Object} config Configuration options
     */
    // Languages available for content generation
    const MA_LANGUAGES = {
        '': 'English (default)',
        'pl': 'Polish / Polski',
        'de': 'German / Deutsch',
        'fr': 'French / Français',
        'es': 'Spanish / Español',
        'it': 'Italian / Italiano',
        'pt': 'Portuguese / Português',
        'nl': 'Dutch / Nederlands',
        'ru': 'Russian / Русский',
        'uk': 'Ukrainian / Українська',
        'cs': 'Czech / Čeština',
        'ja': 'Japanese / 日本語',
        'ko': 'Korean / 한국어',
        'zh': 'Chinese / 中文',
        'ar': 'Arabic / العربية',
        'tr': 'Turkish / Türkçe',
        'hi': 'Hindi / हिन्दी'
    };

    function init(config = {}) {
        // Apply config
        if (config.csrfToken) JTB_MultiAgent.csrfToken = config.csrfToken;
        if (config.apiUrl) JTB_MultiAgent.apiUrl = config.apiUrl;

        // Find or create modal
        createModal();

        // Bind events
        bindEvents();

        // Initialize provider/model dropdowns (uses WB_AI from website-builder.php)
        initMAProviders();

        // Initialize language dropdown
        initMALanguages();

        // Initialize collapsible sections
        initCollapsibles();

        // Add button to AI panel if exists
        addMultiAgentButton();

        // console.log removed
    }

    /**
     * Initialize AI Provider/Model dropdowns
     * Uses WB_AI.availableProviders from website-builder.php (NO HARDCODES!)
     */
    function initMAProviders() {
        const providerSelect = document.getElementById('jtb-ma-provider');
        const modelSelect = document.getElementById('jtb-ma-model');

        if (!providerSelect || !modelSelect) {
            // console.log removed
            return;
        }

        // Check if WB_AI exists (defined in website-builder.php)
        if (typeof WB_AI === 'undefined' || !WB_AI.availableProviders) {
            console.warn('[MultiAgent] WB_AI.availableProviders not found, using fallback');
            return;
        }

        // Populate providers from WB_AI.availableProviders
        providerSelect.innerHTML = '';
        Object.entries(WB_AI.availableProviders).forEach(([key, config]) => {
            const opt = document.createElement('option');
            opt.value = key;
            opt.textContent = config.name;
            if (key === WB_AI.aiProvider) opt.selected = true;
            providerSelect.appendChild(opt);
        });

        // Initialize models for current provider
        updateMAModels(WB_AI.aiProvider);

        // Provider change handler
        providerSelect.addEventListener('change', (e) => {
            updateMAModels(e.target.value);
        });

        // console.log removed
    }

    /**
     * Update model dropdown based on selected provider
     */
    function updateMAModels(provider) {
        const modelSelect = document.getElementById('jtb-ma-model');
        if (!modelSelect) return;

        if (typeof WB_AI === 'undefined' || !WB_AI.availableProviders[provider]) {
            console.warn('[MultiAgent] Provider config not found:', provider);
            return;
        }

        const config = WB_AI.availableProviders[provider];
        modelSelect.innerHTML = '';

        Object.entries(config.models).forEach(([value, label]) => {
            const opt = document.createElement('option');
            opt.value = value;
            opt.textContent = label;
            if (value === config.default) opt.selected = true;
            modelSelect.appendChild(opt);
        });
    }

    /**
     * Initialize language dropdown
     */
    function initMALanguages() {
        const langSelect = document.getElementById('jtb-ma-language');
        if (!langSelect) return;

        langSelect.innerHTML = '';
        Object.entries(MA_LANGUAGES).forEach(([code, name]) => {
            const opt = document.createElement('option');
            opt.value = code;
            opt.textContent = name;
            langSelect.appendChild(opt);
        });

        // console.log removed
    }

    /**
     * Initialize collapsible sections (Brand Kit, Competitor)
     */
    function initCollapsibles() {
        const labels = document.querySelectorAll('.jtb-ma-toggle-label');
        // console.log removed

        labels.forEach(label => {
            const targetId = label.dataset.toggle;
            // console.log removed

            label.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const target = document.getElementById(targetId);
                // console.log removed

                if (target) {
                    const isOpen = target.classList.toggle('open');
                    label.classList.toggle('active', isOpen);
                    // console.log removed
                }
            });
        });

        // Brand Kit extract button
        const extractBrandBtn = document.getElementById('ma-extract-brand-btn');
        if (extractBrandBtn && !extractBrandBtn._bound) {
            extractBrandBtn.addEventListener('click', handleExtractBrand); extractBrandBtn._bound = true;
        }

        // console.log removed
    }

    /**
     * Handle Brand Kit extraction
     */
    async function handleExtractBrand() {
        const url = document.getElementById('jtb-ma-brand-url')?.value?.trim();
        const resultDiv = document.getElementById('ma-brand-result');

        if (!url) {
            showToast('Please enter a URL', 'error');
            return;
        }

        if (!resultDiv) return;

        resultDiv.innerHTML = '<span class="loading">Extracting brand colors and fonts...</span>';
        resultDiv.classList.add('visible');

        try {
            const result = await apiCall('generate-website', {
                action: 'brand-kit',
                url: url
            });

            if (result.ok && result.data?.brand_kit) {
                const kit = result.data.brand_kit;
                let html = '<strong>Extracted Brand Kit:</strong><div class="brand-colors">';

                // Show colors
                if (kit.colors) {
                    kit.colors.forEach(color => {
                        html += `<div class="color-swatch" style="background:${color}" title="${color}"></div>`;
                    });
                }
                html += '</div>';

                // Show font if available
                if (kit.font) {
                    html += `<div style="margin-top:8px;font-size:12px;color:#666;">Font: ${kit.font}</div>`;
                }

                resultDiv.innerHTML = html;
            } else {
                resultDiv.innerHTML = `<span class="error">${result.error || 'Failed to extract brand kit'}</span>`;
            }
        } catch (e) {
            resultDiv.innerHTML = `<span class="error">${e.message}</span>`;
        }
    }

    /**
     * Find or initialize the modal
     * Modal HTML is expected to exist in website-builder.php with 'ma-*' prefixed IDs
     * We DON'T dynamically create the modal - it must exist in the HTML
     */
    function createModal() {
        // Check if modal already exists in HTML (website-builder.php provides it)
        const existingModal = document.getElementById('jtb-multiagent-modal');
        if (existingModal) {
            JTB_MultiAgent.modal = existingModal;
            // console.log removed
            return;
        }

        // Modal not found - this is an error condition
        // The modal HTML should be included in website-builder.php
        console.error('[MultiAgent] Modal not found! Expected #jtb-multiagent-modal in HTML');
        console.error('[MultiAgent] Make sure website-builder.php includes the multi-agent modal HTML');

        // Create a minimal fallback modal for debugging
        const fallbackModal = document.createElement('div');
        fallbackModal.id = 'jtb-multiagent-modal';
        fallbackModal.className = 'jtb-multiagent-modal';
        fallbackModal.innerHTML = `
            <div class="jtb-multiagent-overlay" id="jtb-multiagent-overlay"></div>
            <div class="jtb-multiagent-modal" role="dialog">
                <div class="jtb-multiagent-body" style="padding:40px;text-align:center;">
                    <h2 style="color:#ef4444;">Multi-Agent Modal Not Found</h2>
                    <p style="color:#888;">The multi-agent modal HTML is missing from the page.</p>
                    <p style="color:#888;">Please ensure website-builder.php includes the modal structure.</p>
                    <button id="ma-close-btn" style="margin-top:20px;padding:10px 20px;cursor:pointer;">Close</button>
                </div>
            </div>
        `;
        document.body.appendChild(fallbackModal);
        JTB_MultiAgent.modal = fallbackModal;
    }

    /**
     * Bind event listeners
     * Note: HTML uses 'ma-*' prefix for element IDs
     */
    function bindEvents() {
        // Close button - matches ma-close-btn in HTML
        addListener('ma-close-btn', 'click', closeModal);

        // Overlay click to close
        const overlay = document.querySelector('.jtb-multiagent-overlay');
        if (overlay) {
            overlay.addEventListener('click', closeModal);
        }

        // Generate button - matches ma-generate-btn in HTML
        addListener('ma-generate-btn', 'click', handleGenerateMockup);

        // Iterate button - matches id in HTML
        addListener('ma-iterate-btn', 'click', handleIterate);

        // Iteration input - Enter key
        const iterationInput = document.getElementById('ma-iteration-input');
        if (iterationInput) {
            iterationInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    handleIterate();
                }
            });
        }

        // Suggestion buttons
        document.querySelectorAll('.jtb-ma-suggestion-item').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById('ma-iteration-input');
                if (input) {
                    input.value = btn.dataset.suggestion || btn.textContent;
                    input.focus();
                }
            });
        });

        // Restart/Regenerate button (HTML uses ma-restart-btn)

        // Sidebar tab switching (Iterate / Structure)
        document.querySelectorAll('.jtb-ma-sidebar-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const tabName = tab.dataset.tab;
                // Update active tab
                document.querySelectorAll('.jtb-ma-sidebar-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                // Show/hide panels
                const iteratePanel = document.querySelector('.jtb-ma-iteration-panel');
                const structurePanel = document.querySelector('.jtb-ma-structure-panel');
                if (tabName === 'iterate') {
                    if (iteratePanel) iteratePanel.style.display = 'block';
                    if (structurePanel) structurePanel.style.display = 'none';
                } else if (tabName === 'structure') {
                    if (iteratePanel) iteratePanel.style.display = 'none';
                    if (structurePanel) structurePanel.style.display = 'block';
                    populateStructurePanel();
                }
            });
        });
        addListener('ma-restart-btn', 'click', handleRegenerate);

        // Accept button
        addListener('ma-accept-btn', 'click', handleAcceptMockup);

        // Apply button (Done phase)
        addListener('ma-done-apply', 'click', showMappingPhase);

        // Close button (Done phase)
        addListener('ma-done-close', 'click', closeModal);

        // Mapping phase buttons
        addListener('ma-mapping-back', 'click', handleMappingBack);
        addListener('ma-mapping-save', 'click', handleSaveToCMS);

        // Device buttons
        document.querySelectorAll('.jtb-ma-device-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.jtb-ma-device-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                setPreviewDevice(btn.dataset.device);
            });
        });

        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && JTB_MultiAgent.modal?.classList.contains('active')) {
                closeModal();
            }
        });
    }

    /**
     * Add event listener with tracking
     */
    function addListener(elementId, event, handler) {
        const element = document.getElementById(elementId);
        if (element) {
            element.addEventListener(event, handler);
            JTB_MultiAgent._listeners.push({ element, event, handler });
        }
    }

    /**
     * Clean up event listeners
     */
    function cleanupListeners() {
        JTB_MultiAgent._listeners.forEach(({ element, event, handler }) => {
            element.removeEventListener(event, handler);
        });
        JTB_MultiAgent._listeners = [];
    }

    /**
     * Add Multi-Agent button to existing AI panel
     */
    function addMultiAgentButton() {
        // Check if button already exists
        if (document.getElementById('jtb-ai-multiagent-trigger')) return;

        // Find AI panel header or compose tab
        const aiPanelHeader = document.querySelector('.jtb-ai-panel-header');
        if (aiPanelHeader) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.id = 'jtb-ai-multiagent-trigger';
            btn.className = 'jtb-ai-multiagent-trigger';
            btn.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <rect x="3" y="3" width="7" height="7"/>
                    <rect x="14" y="3" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/>
                </svg>
                <span>Full Website</span>
            `;
            btn.title = 'Generate complete website with AI';
            btn.addEventListener('click', openModal);

            // Insert before close button
            const closeBtn = aiPanelHeader.querySelector('.jtb-ai-panel-close');
            if (closeBtn) {
                aiPanelHeader.insertBefore(btn, closeBtn);
            } else {
                aiPanelHeader.appendChild(btn);
            }
        }
    }

    // ========================================
    // Modal Control
    // ========================================

    /**
     * Open the modal
     */
    function openModal() {
        if (!JTB_MultiAgent.modal) {
            createModal();
            bindEvents();
        }

        // Show overlay (CSS requires .active class)
        const overlay = document.getElementById('jtb-multiagent-overlay');
        if (overlay) {
            overlay.classList.add('active');
        }

        // Show modal (CSS requires .active class, not .is-open)
        JTB_MultiAgent.modal.classList.add('active');
        document.body.classList.add('jtb-multiagent-open');

        // Reset to input phase
        setPhase('input');

        // Close AI panel if open
        if (typeof JTB_AI !== 'undefined' && JTB_AI.closePanel) {
            JTB_AI.closePanel();
        }

        // console.log removed
    }

    /**
     * Close the modal
     */
    function closeModal() {
        // Hide overlay
        const overlay = document.getElementById('jtb-multiagent-overlay');
        if (overlay) {
            overlay.classList.remove('active');
        }

        // Hide modal (CSS uses .active class)
        if (JTB_MultiAgent.modal) {
            JTB_MultiAgent.modal.classList.remove('active');
        }
        document.body.classList.remove('jtb-multiagent-open');

        // console.log removed
    }

    /**
     * Set the current phase
     * HTML phases use IDs: ma-phase-input, ma-phase-generating, ma-phase-mockup, ma-phase-building, ma-phase-done
     */
    function setPhase(phase) {
        JTB_MultiAgent.phase = phase;

        // Update phase text (ma-phase-text)
        const phaseText = document.getElementById('ma-phase-text');
        if (phaseText) {
            const labels = {
                'input': 'Ready',
                'generating': 'Generating...',
                'mockup': 'Preview Ready',
                'iterating': 'Updating...',
                'building': 'Building...',
                'done': 'Complete',
                'mapping': 'Save to CMS'
            };
            phaseText.textContent = labels[phase] || phase;
        }

        // Update phase indicator
        const indicator = document.getElementById('ma-phase-indicator');
        if (indicator) {
            indicator.className = 'jtb-multiagent-phase-indicator phase-' + phase;
        }

        // Hide all phase panels, show current one
        document.querySelectorAll('.jtb-multiagent-phase').forEach(el => {
            el.classList.remove('active');
        });

        // Map phase name to ID suffix
        const phaseIdMap = {
            'input': 'input',
            'generating': 'generating',
            'mockup': 'mockup',
            'iterating': 'mockup', // Iterating uses mockup panel
            'building': 'building',
            'done': 'done',
            'mapping': 'mapping'
        };

        const panelId = 'ma-phase-' + (phaseIdMap[phase] || phase);
        const activePanel = document.getElementById(panelId);
        if (activePanel) {
            activePanel.classList.add('active');
        }
    }

    /**
     * Set preview device size
     */
    function setPreviewDevice(device) {
        const wrapper = document.querySelector('.jtb-ma-preview-frame-wrapper');
        if (wrapper) {
            wrapper.setAttribute('data-device', device);
        }
    }

    // ========================================
    // API Calls
    // ========================================

    /**
     * Make API call
     */
    async function apiCall(endpoint, data = {}) {
        const url = JTB_MultiAgent.apiUrl + '/' + endpoint;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': JTB_MultiAgent.csrfToken
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.error || `HTTP ${response.status}`);
            }

            return result;
        } catch (error) {
            console.error('[MultiAgent] API Error:', error);
            throw error;
        }
    }

    // ========================================
    // Mockup Phase Handlers
    // ========================================

    /**
     * Handle generate mockup button click
     * HTML element IDs: jtb-ma-prompt, jtb-ma-industry, jtb-ma-style, etc.
     */
    async function handleGenerateMockup() {
        // Get form values - using correct HTML IDs
        const prompt = document.getElementById('jtb-ma-prompt')?.value?.trim();
        const industry = document.getElementById('jtb-ma-industry')?.value || 'general';
        const style = document.getElementById('jtb-ma-style')?.value || 'modern';

        // Get selected pages - checkboxes have class jtb-ma-checkbox-item
        const pages = [];
        document.querySelectorAll('.jtb-ma-checkbox-item input:checked').forEach(checkbox => {
            pages.push(checkbox.value);
        });

        // NEW: Get provider, model, language from new fields
        const provider = document.getElementById('jtb-ma-provider')?.value || (typeof WB_AI !== 'undefined' ? WB_AI.aiProvider : 'anthropic');
        const model = document.getElementById('jtb-ma-model')?.value || (typeof WB_AI !== 'undefined' ? WB_AI.aiModel : '');
        const language = document.getElementById('jtb-ma-language')?.value || '';

        // NEW: Get optional brand kit and competitor URLs
        const brandUrl = document.getElementById('jtb-ma-brand-url')?.value?.trim() || '';
        const competitorUrl = document.getElementById('jtb-ma-competitor-url')?.value?.trim() || '';

        // Validate
        if (!prompt) {
            showToast('Please describe your business', 'error');
            document.getElementById('jtb-ma-prompt')?.focus();
            return;
        }

        if (pages.length === 0) {
            showToast('Please select at least one page', 'error');
            return;
        }

        // Save to state
        JTB_MultiAgent.prompt = prompt;
        JTB_MultiAgent.industry = industry;
        JTB_MultiAgent.style = style;
        JTB_MultiAgent.pages = pages;

        // Show generating phase
        setPhase('generating');
        startProgressAnimation();

        // Update status text with provider info
        const providerName = typeof WB_AI !== 'undefined' && WB_AI.availableProviders[provider]
            ? WB_AI.availableProviders[provider].name
            : provider;
        updateGeneratingStatus(`Connecting to ${providerName}...`);

        try {
            // Call API - quick mockup (start + mockup in one call)
            // console.log removed
            const startTime = performance.now();
            
            const result = await apiCall('generate-website', {
                action: 'multi-agent',
                step: 'quick-mockup',
                prompt: prompt,
                industry: industry,
                style: style,
                pages: pages,
                // NEW: Additional fields
                ai_provider: provider,
                ai_model: model,
                language: language,
                brand_url: brandUrl,
                competitor_url: competitorUrl
            });

            const elapsed = ((performance.now() - startTime) / 1000).toFixed(1);
            // console.log removed
            // console.log removed
            // console.log removed

            if (!result.ok) {
                console.error('[MultiAgent] API returned ok=false:', result.error);
                throw new Error(result.error || 'Failed to generate mockup');
            }

            // Save results
            // console.log removed
            JTB_MultiAgent.sessionId = result.session_id;
            JTB_MultiAgent.mockupHtml = result.mockup_html;
            JTB_MultiAgent.mockupStructure = result.structure;
            JTB_MultiAgent.stats.totalTimeMs = result.stats?.time_ms || 0;
            JTB_MultiAgent.stats.totalTokens = result.stats?.tokens_used || 0;

            // Show mockup phase
            stopProgressAnimation();
            setPhase('mockup');
            displayMockup();
            updateStats();

            showToast('Mockup generated successfully!', 'success');

        } catch (error) {
            console.error('[MultiAgent] Error in handleGenerateMockup:', error);
            stopProgressAnimation();
            setPhase('input');
            showToast(error.message || 'Failed to generate mockup', 'error');
        }
    }

    /**
     * Handle iteration request
     * HTML element ID: ma-iteration-input
     */
    
    async function handleIterate() {
    /**
     * Populate the Structure panel with current mockup structure
     */
    function populateStructurePanel() {
        const panel = document.querySelector('.jtb-ma-structure-panel');
        if (!panel) return;
        
        const structure = JTB_MultiAgent.mockupStructure;
        if (!structure || !structure.pages) {
            panel.innerHTML = '<div class="jtb-ma-structure-empty">No structure available. Generate a mockup first.</div>';
            return;
        }
        
        let html = '<div class="jtb-ma-structure-tree">';
        
        // Header
        if (structure.header) {
            html += '<div class="jtb-ma-structure-item"><strong>📋 Header</strong>';
            html += renderSections(structure.header.sections || []);
            html += '</div>';
        }
        
        // Pages
        structure.pages.forEach(page => {
            html += '<div class="jtb-ma-structure-item"><strong>📄 ' + (page.title || page.slug) + '</strong>';
            html += renderSections(page.sections || []);
            html += '</div>';
        });
        
        // Footer
        if (structure.footer) {
            html += '<div class="jtb-ma-structure-item"><strong>📋 Footer</strong>';
            html += renderSections(structure.footer.sections || []);
            html += '</div>';
        }
        
        html += '</div>';
        panel.innerHTML = html;
        
        function renderSections(sections) {
            if (!sections.length) return '';
            let s = '<ul class="jtb-ma-structure-sections">';
            sections.forEach(sec => {
                s += '<li>' + (sec.id || sec.type || 'Section');
                if (sec.children && sec.children.length) {
                    s += ' (' + sec.children.length + ' modules)';
                }
                s += '</li>';
            });
            s += '</ul>';
            return s;
        }
    }

        const input = document.getElementById('ma-iteration-input');
        const instruction = input?.value?.trim();

        if (!instruction) {
            showToast('Please enter what you want to change', 'error');
            input?.focus();
            return;
        }

        if (!JTB_MultiAgent.sessionId) {
            showToast('No active session. Please generate a mockup first.', 'error');
            return;
        }

        // Show iterating phase
        setPhase('iterating');
        const iterateBtn = document.getElementById('ma-iterate-btn');
        if (iterateBtn) iterateBtn.disabled = true;

        try {
            // console.log removed
            const result = await apiCall('multi-agent', {
                step: 'mockup-iterate',
                session_id: JTB_MultiAgent.sessionId,
                instruction: instruction
            });

            if (!result.ok) {
                throw new Error(result.error || 'Failed to apply changes');
            }

            // Update state
            JTB_MultiAgent.mockupHtml = result.mockup_html;
            if (result.structure) {
                JTB_MultiAgent.mockupStructure = result.structure;
            }
            JTB_MultiAgent.iterationHistory.push({
                instruction: instruction,
                changes: result.changes || [],
                timestamp: new Date().toISOString()
            });
            JTB_MultiAgent.stats.mockupIterations = result.iteration_count || JTB_MultiAgent.iterationHistory.length;
            JTB_MultiAgent.stats.totalTimeMs += result.stats?.time_ms || 0;
            JTB_MultiAgent.stats.totalTokens += result.stats?.tokens_used || 0;

            // Update UI
            setPhase('mockup');
            displayMockup();
            updateIterationHistory();
            updateStats();

            // Clear input
            if (input) input.value = '';

            showToast('Changes applied!', 'success');

        } catch (error) {
            setPhase('mockup');
            showToast(error.message || 'Failed to apply changes', 'error');
        } finally {
            if (iterateBtn) iterateBtn.disabled = false;
        }
    }

    /**
     * Handle regenerate button
     */
    async function handleRegenerate() {
        // Confirm
        if (!confirm('This will regenerate the mockup from scratch. Continue?')) {
            return;
        }

        // Reset iteration history
        JTB_MultiAgent.iterationHistory = [];
        JTB_MultiAgent.stats.mockupIterations = 0;

        // Generate again
        await handleGenerateMockup();
    }

    /**
     * Handle accept mockup and start building
     */
    async function handleAcceptMockup() {
        if (!JTB_MultiAgent.sessionId) {
            showToast('No active session', 'error');
            return;
        }

        try {
            // Accept mockup
            const acceptResult = await apiCall('multi-agent', {
                step: 'accept',
                session_id: JTB_MultiAgent.sessionId
            });

            if (!acceptResult.ok) {
                throw new Error(acceptResult.error || 'Failed to accept mockup');
            }

            // Save build steps
            JTB_MultiAgent.buildSteps = acceptResult.steps || [];
            JTB_MultiAgent.currentStepIndex = 0;

            // Show building phase
            setPhase('building');
            displayBuildSteps();

            // Start building
            await runBuildProcess();

        } catch (error) {
            setPhase('mockup');
            showToast(error.message || 'Failed to start building', 'error');
        }
    }

    /**
     * Run the build process (sequential steps)
     */
    async function runBuildProcess() {
        const steps = JTB_MultiAgent.buildSteps;
        const total = steps.length;

        for (let i = 0; i < total; i++) {
            // Small delay between steps for stability
            if (i > 0) {
                await new Promise(r => setTimeout(r, 100));
            }
            
            JTB_MultiAgent.currentStepIndex = i;
            const step = steps[i];

            // Update UI
            updateBuildProgress(i, total);
            highlightBuildStep(i, 'running');

            try {
                // Parse step (e.g., "content:home" -> step="content", page="home")
                const [stepType, page] = step.includes(':') ? step.split(':') : [step, null];

                // console.log removed
                const result = await apiCall('multi-agent', {
                    step: 'build',
                    session_id: JTB_MultiAgent.sessionId,
                    build_step: step
                });
                // Step completed

                if (!result.ok) {
                    throw new Error(result.error || `Step ${step} failed`);
                }

                // Mark step complete
                highlightBuildStep(i, 'completed');
                JTB_MultiAgent.stats.stepsCompleted++;
                JTB_MultiAgent.stats.totalTimeMs += result.stats?.time_ms || 0;
                JTB_MultiAgent.stats.totalTokens += result.tokens_used || 0;

            } catch (error) {
                highlightBuildStep(i, 'error');
                showToast(error.message || `Build step failed: ${step}`, 'error');
                // CRITICAL: Stop if architect fails - all other steps depend on it
                if (step === 'architect') {
                    showToast('Build stopped: Architect step is required', 'error');
                    return;
                }
            }
        }

        // Get final result
        try {
            const resultData = await apiCall('multi-agent', {
                step: 'result',
                session_id: JTB_MultiAgent.sessionId
            });

            if (resultData.ok && resultData.website) {
                JTB_MultiAgent.finalWebsite = resultData.website;
                JTB_MultiAgent.stats = { ...JTB_MultiAgent.stats, ...resultData.stats };

                // Show done phase
                setPhase('done');
                updateDoneStats();
                showToast('Website built successfully!', 'success');
            } else {
                throw new Error('Failed to get final website');
            }

        } catch (error) {
            showToast(error.message || 'Failed to get final result', 'error');
        }
    }

    /**
     * Handle apply to builder
     * Calls onApply callback if defined, dispatches event, or falls back
     */
    function handleApplyToBuilder() {
        if (!JTB_MultiAgent.finalWebsite) {
            showToast('No website data to apply', 'error');
            return;
        }

        // 1. Try the onApply callback first (set by website-builder.php via publicAPI)
        // Check both window.JTB_AI_MultiAgent.onApply and internal callback
        const callback = window.JTB_AI_MultiAgent?.onApply || window.JTB_MultiAgent?.onApply;
        if (typeof callback === 'function') {
            callback(JTB_MultiAgent.finalWebsite);
            showToast('Website applied to builder!', 'success');
            closeModal();
            return;
        }

        // 2. Dispatch event for any listeners
        const event = new CustomEvent('jtb:multiagent:apply', {
            detail: {
                website: JTB_MultiAgent.finalWebsite,
                stats: JTB_MultiAgent.stats
            }
        });
        document.dispatchEvent(event);

        // 3. Try to call builder directly if available
        if (typeof JTB !== 'undefined' && JTB.applyAILayout) {
            JTB.applyAILayout(JTB_MultiAgent.finalWebsite);
            showToast('Website applied to builder!', 'success');
            closeModal();
        } else if (typeof window.applyWebsiteToBuilder === 'function') {
            window.applyWebsiteToBuilder(JTB_MultiAgent.finalWebsite);
            showToast('Website applied to builder!', 'success');
            closeModal();
        } else {
            // 4. Fallback - copy to clipboard
            try {
                navigator.clipboard.writeText(JSON.stringify(JTB_MultiAgent.finalWebsite, null, 2));
                showToast('Website JSON copied to clipboard!', 'info');
                closeModal();
            } catch (e) {
                // console.log removed
                showToast('Website data logged to console', 'info');
            }
        }
    }

    /**
     * Handle preview final website
     */
    function handlePreviewFinal() {
        const iframe = document.getElementById('ma-preview-frame');
        if (!iframe) {
            showToast('No preview available', 'error');
            return;
        }

        try {
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            const html = iframeDoc.documentElement.outerHTML;

            const previewWindow = window.open('', '_blank');
            if (previewWindow) {
                previewWindow.document.write(html);
                previewWindow.document.close();
            } else {
                showToast('Popup blocked — please allow popups', 'error');
            }
        } catch (e) {
            // CORS fallback — use iframe src directly
            if (iframe.src && iframe.src !== 'about:blank') {
                window.open(iframe.src, '_blank');
            } else {
                showToast('Cannot open preview', 'error');
            }
        }
    }

    // ========================================
    // UI Updates
    // ========================================

    /**
     * Display mockup in iframe
     * HTML element ID: ma-preview-frame
     */
    function displayMockup() {
        // console.log removed
        const iframe = document.getElementById('ma-preview-frame');
        // console.log removed
        // console.log removed
        // console.log removed
        // console.log removed
        
        if (!iframe || !JTB_MultiAgent.mockupHtml) {
            console.error('[MultiAgent] displayMockup ABORT - no iframe or no html');
            return;
        }

        try {
            // Write HTML to iframe
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            // console.log removed
            doc.open();
            doc.write(JTB_MultiAgent.mockupHtml);
            doc.close();
            // console.log removed
            // console.log removed
        } catch (e) {
            console.error('[MultiAgent] doc.write ERROR:', e);
        }

        // Update current page label
        const pageLabel = document.getElementById('ma-current-page');
        if (pageLabel && JTB_MultiAgent.pages?.length > 0) {
            const firstPage = JTB_MultiAgent.pages[0];
            pageLabel.textContent = firstPage.charAt(0).toUpperCase() + firstPage.slice(1);
        }
    }

    /**
     * Update page tabs in preview (mockup may have multiple pages)
     */
    function updatePageTabs() {
        // Page tabs handled via ma-current-page label - no separate tabs container
        // The mockup HTML itself contains navigation between pages
    }

    /**
     * Update iteration history UI
     * HTML element ID: ma-history-list
     */
    function updateIterationHistory() {
        const container = document.getElementById('ma-history-container');
        const list = document.getElementById('ma-history-list');
        if (!list) return;

        if (JTB_MultiAgent.iterationHistory.length === 0) {
            if (container) container.style.display = 'none';
            return;
        }

        if (container) container.style.display = 'block';

        list.innerHTML = JTB_MultiAgent.iterationHistory.map((item, idx) => `
            <div class="jtb-ma-history-item">
                <div class="history-number">${idx + 1}</div>
                <div class="history-content">
                    <div class="history-instruction">${escapeHtml(item.instruction)}</div>
                    ${item.changes && item.changes.length > 0 ? `
                        <div class="history-changes">
                            ${item.changes.map(c => `<span class="change-tag">${escapeHtml(c)}</span>`).join('')}
                        </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }

    /**
     * Update generating status text
     */
    function updateGeneratingStatus(text) {
        const statusEl = document.getElementById('ma-generating-status');
        if (statusEl) {
            statusEl.textContent = text;
        }
    }

    /**
     * Update stats display
     */
    function updateStats() {
        // Stats are displayed in Done phase via ma-stat-* elements
        // No separate stats area during mockup phase in current HTML
    }

    /**
     * Display build steps
     * HTML element ID: ma-steps-list
     */
    function displayBuildSteps() {
        const container = document.getElementById('ma-steps-list');
        if (!container) return;

        const stepLabels = {
            'architect': 'Analyzing Structure',
            'content:header_footer': 'Creating Header & Footer',
            'content:home': 'Writing Home Page',
            'content:about': 'Writing About Page',
            'content:services': 'Writing Services Page',
            'content:contact': 'Writing Contact Page',
            'content:pricing': 'Writing Pricing Page',
            'content:portfolio': 'Writing Portfolio Page',
            'content:blog': 'Writing Blog Page',
            'content:faq': 'Writing FAQ Page',
            'content:team': 'Writing Team Page',
            'content:testimonials': 'Writing Testimonials Page',
            'stylist': 'Applying Styles',
            'seo': 'Optimizing SEO',
            'images': 'Fetching Images',
            'assemble': 'Assembling Website'
        };

        container.innerHTML = JTB_MultiAgent.buildSteps.map((step, idx) => `
            <div class="jtb-ma-build-step" data-step="${step}" data-status="pending">
                <div class="step-status">
                    <div class="step-icon pending">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                    </div>
                    <div class="step-icon running" style="display:none;">
                        <svg viewBox="0 0 24 24" class="spin" width="16" height="16">
                            <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="30 70"/>
                        </svg>
                    </div>
                    <div class="step-icon completed" style="display:none;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="8 12 11 15 16 9"/>
                        </svg>
                    </div>
                    <div class="step-icon error" style="display:none;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M15 9l-6 6M9 9l6 6"/>
                        </svg>
                    </div>
                </div>
                <div class="step-label">${stepLabels[step] || step}</div>
            </div>
        `).join('');
    }

    /**
     * Highlight build step
     * Uses class: jtb-ma-build-step
     */
    function highlightBuildStep(index, status) {
        const steps = document.querySelectorAll('.jtb-ma-build-step');
        const step = steps[index];
        if (!step) return;

        // Hide all icons, show relevant one
        step.querySelectorAll('.step-icon').forEach(icon => {
            icon.style.display = 'none';
        });
        const activeIcon = step.querySelector('.step-icon.' + status);
        if (activeIcon) {
            activeIcon.style.display = 'block';
        }

        // Update data-status and class
        step.setAttribute('data-status', status);
        step.classList.remove('pending', 'running', 'completed', 'error');
        step.classList.add(status);
    }

    /**
     * Update build progress
     * HTML element ID: ma-progress-fill
     */
    function updateBuildProgress(current, total) {
        const progressFill = document.getElementById('ma-progress-fill');

        const percent = Math.round(((current + 1) / total) * 100);

        if (progressFill) {
            progressFill.style.width = percent + '%';
        }
    }

    /**
     * Update done stats
     * HTML element IDs: ma-stat-pages, ma-stat-sections, ma-stat-modules
     */
    function updateDoneStats() {
        const website = JTB_MultiAgent.finalWebsite;

        // Count pages
        const pagesCount = website?.pages ? Object.keys(website.pages).length : 0;

        // Count sections and modules
        let sectionsCount = 0;
        let modulesCount = 0;

        const countModulesInChildren = (children) => {
            if (!children) return;
            children.forEach(child => {
                if (child.type && child.type !== 'section' && child.type !== 'row' && child.type !== 'column') {
                    modulesCount++;
                }
                if (child.children) {
                    countModulesInChildren(child.children);
                }
            });
        };

        if (website?.pages) {
            Object.values(website.pages).forEach(page => {
                sectionsCount += page.sections?.length || 0;
                page.sections?.forEach(section => {
                    countModulesInChildren(section.children);
                });
            });
        }
        if (website?.header?.sections) {
            sectionsCount += website.header.sections.length;
            website.header.sections.forEach(section => {
                countModulesInChildren(section.children);
            });
        }
        if (website?.footer?.sections) {
            sectionsCount += website.footer.sections.length;
            website.footer.sections.forEach(section => {
                countModulesInChildren(section.children);
            });
        }

        // Update UI - using correct HTML IDs
        const pagesEl = document.getElementById('ma-stat-pages');
        const sectionsEl = document.getElementById('ma-stat-sections');
        const modulesEl = document.getElementById('ma-stat-modules');

        if (pagesEl) pagesEl.textContent = pagesCount;
        if (sectionsEl) sectionsEl.textContent = sectionsCount;
        if (modulesEl) modulesEl.textContent = modulesCount;
    }

    // ========================================
    // Progress Animation
    // ========================================

    let progressInterval = null;
    let progressValue = 0;
    let statusMessages = [
        'Analyzing requirements...',
        'Designing layout structure...',
        'Creating visual elements...',
        'Generating mockup content...',
        'Finalizing preview...'
    ];
    let currentMessageIndex = 0;

    function startProgressAnimation() {
        progressValue = 0;
        currentMessageIndex = 0;

        // Update status text periodically
        const statusEl = document.getElementById('ma-generating-status');

        progressInterval = setInterval(() => {
            // Slow down as we approach 90%
            if (progressValue < 30) {
                progressValue += 2;
            } else if (progressValue < 60) {
                progressValue += 1;
            } else if (progressValue < 85) {
                progressValue += 0.3;
            } else if (progressValue < 90) {
                progressValue += 0.1;
            }

            // Update status message based on progress
            const newIndex = Math.min(Math.floor(progressValue / 20), statusMessages.length - 1);
            if (newIndex !== currentMessageIndex && statusEl) {
                currentMessageIndex = newIndex;
                statusEl.textContent = statusMessages[currentMessageIndex];
            }
        }, 200);
    }

    function stopProgressAnimation() {
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }

        // Set final status message
        const statusEl = document.getElementById('ma-generating-status');
        if (statusEl) {
            statusEl.textContent = 'Complete!';
        }
    }

    // ========================================
    // Utilities
    // ========================================

    /**
     * Escape HTML
     */
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Show toast notification
     * HTML container ID: ma-toast-container
     */
    function showToast(message, type = 'info') {
        // Use toast container from HTML
        let container = document.getElementById('ma-toast-container');

        // Fallback: create container if not found
        if (!container) {
            container = document.createElement('div');
            container.id = 'ma-toast-container';
            container.className = 'jtb-ma-toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `jtb-ma-toast jtb-ma-toast-${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}</span>
            <span class="toast-message">${escapeHtml(message)}</span>
        `;
        container.appendChild(toast);

        // Animate in
        setTimeout(() => toast.classList.add('show'), 10);

        // Remove after delay
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, type === 'error' ? 5000 : 3000);
    }

    // ========================================
    // ========================================
    // MAPPING PHASE FUNCTIONS
    // ========================================

    /**
     * Show mapping phase - called when user clicks "Apply to Builder"
     */
    function showMappingPhase() {
        if (!JTB_MultiAgent.finalWebsite) {
            showToast('No website data available', 'error');
            return;
        }

        setPhase('mapping');
        populateMappingDropdowns();
    }

    /**
     * Populate mapping dropdowns with existing pages/templates
     */
    function populateMappingDropdowns() {
        const website = JTB_MultiAgent.finalWebsite;

        // Get existing data from WB (Website Builder) global
        const existingHeaders = window.WB?.headers || [];
        const existingFooters = window.WB?.footers || [];
        const existingPages = window.WB?.pages || [];

        // Populate header dropdown
        const headerSelect = document.getElementById('ma-map-header');
        if (headerSelect && website.header) {
            const headerSections = website.header.sections?.length || 0;
            document.getElementById('ma-map-header-info').textContent = `(${headerSections} section${headerSections !== 1 ? 's' : ''})`;
            
            headerSelect.innerHTML = existingHeaders.map(h => 
                `<option value="${h.id}">${h.name || 'Header #' + h.id}</option>`
            ).join('');
            
            if (existingHeaders.length === 0) {
                headerSelect.innerHTML = '<option value="">No headers available</option>';
            }
        }

        // Populate footer dropdown
        const footerSelect = document.getElementById('ma-map-footer');
        if (footerSelect && website.footer) {
            const footerSections = website.footer.sections?.length || 0;
            document.getElementById('ma-map-footer-info').textContent = `(${footerSections} section${footerSections !== 1 ? 's' : ''})`;
            
            footerSelect.innerHTML = existingFooters.map(f => 
                `<option value="${f.id}">${f.name || 'Footer #' + f.id}</option>`
            ).join('');
            
            if (existingFooters.length === 0) {
                footerSelect.innerHTML = '<option value="">No footers available</option>';
            }
        }

        // Populate pages list
        const pagesContainer = document.getElementById('ma-mapping-pages-list');
        if (pagesContainer && website.pages) {
            pagesContainer.innerHTML = '';
            
            Object.entries(website.pages).forEach(([pageKey, pageData]) => {
                const sections = pageData.sections?.length || 0;
                
                // Create row
                const row = document.createElement('div');
                row.className = 'jtb-ma-mapping-row';
                row.id = `ma-map-page-${pageKey}-row`;
                
                // Find matching existing page
                const matchingPage = existingPages.find(p => 
                    p.slug === pageKey || 
                    p.slug === pageKey.toLowerCase() ||
                    p.title?.toLowerCase() === pageKey.toLowerCase()
                );
                
                row.innerHTML = `
                    <div class="mapping-label">
                        <span class="mapping-icon">📄</span>
                        <span>${pageKey.charAt(0).toUpperCase() + pageKey.slice(1)}</span>
                        <span class="mapping-info">(${sections} sections)</span>
                    </div>
                    <div class="mapping-control">
                        <select id="ma-map-page-${pageKey}" class="jtb-ma-mapping-select" data-page-key="${pageKey}">
                            <option value="__new__" class="create-new">+ Create new page</option>
                            ${existingPages.map(p => 
                                `<option value="${p.id}" ${matchingPage?.id === p.id ? 'selected' : ''}>${p.title || p.slug}</option>`
                            ).join('')}
                        </select>
                    </div>
                `;
                
                pagesContainer.appendChild(row);
            });
        }
    }

    /**
     * Handle save to CMS
     */
    async function handleSaveToCMS() {
        const website = JTB_MultiAgent.finalWebsite;
        if (!website) {
            showToast('No website data to save', 'error');
            return;
        }

        // Collect mapping
        const mapping = {
            header: {
                target_id: document.getElementById('ma-map-header')?.value || null,
                content: website.header
            },
            footer: {
                target_id: document.getElementById('ma-map-footer')?.value || null,
                content: website.footer
            },
            pages: {}
        };

        // Collect page mappings
        Object.keys(website.pages || {}).forEach(pageKey => {
            const select = document.getElementById(`ma-map-page-${pageKey}`);
            if (select) {
                mapping.pages[pageKey] = {
                    target_id: select.value === '__new__' ? null : select.value,
                    create_new: select.value === '__new__',
                    content: website.pages[pageKey]
                };
            }
        });

        // Show saving state
        const saveBtn = document.getElementById('ma-mapping-save');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<span class="jtb-spinner"></span> Saving...';
        saveBtn.disabled = true;

        try {
            // Check if user wants to clear existing
            const clearExisting = document.getElementById('ma-clear-existing')?.checked ?? true;
            
            const result = await apiCall('save-website', {
                session_id: JTB_MultiAgent.sessionId,
                mapping: mapping,
                clear_existing: clearExisting
            });

            if (result.ok) {
                showToast(`Saved ${result.saved_count || 0} items to CMS! Reloading...`, 'success');
                
                // Reload page after short delay to show fresh sidebar
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(result.error || 'Failed to save');
            }
        } catch (error) {
            showToast(error.message || 'Failed to save to CMS', 'error');
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
    }

    /**
     * Handle back from mapping
     */
    function handleMappingBack() {
        setPhase('done');
    }

    // Public API
    // ========================================

    // Public API - expose as both JTB_MultiAgent and JTB_AI_MultiAgent for compatibility
    const publicAPI = {
        init,
        openModal,
        closeModal,
        setPhase,
        // Expose state for debugging
        getState: () => ({ ...JTB_MultiAgent }),
        // Manual trigger
        generate: handleGenerateMockup,
        iterate: handleIterate,
        accept: handleAcceptMockup,
        apply: handleApplyToBuilder,
        // Callback for when website is applied (set by hosting page)
        onApply: null
    };

    window.JTB_MultiAgent = publicAPI;
    window.JTB_AI_MultiAgent = publicAPI; // Alias for website-builder.php compatibility

    // ========================================
    // Auto-initialize - DISABLED (manual init from website-builder.php)
    // ========================================
    // Removed to prevent triple initialization

})();
