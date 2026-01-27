<?php
/**
 * AI Models Configuration
 * Central configuration for all AI model selections across the CMS
 * NOW READS FROM ai_settings.json for dynamic model updates
 *
 * @package CMS\Core
 */

/**
 * Load AI settings from JSON file (cached per request)
 *
 * @return array Settings array
 */
function _ai_load_settings_json(): array {
    static $settings = null;

    if ($settings === null) {
        $settingsFile = (defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__)) . '/config/ai_settings.json';

        if (file_exists($settingsFile)) {
            $content = file_get_contents($settingsFile);
            $settings = json_decode($content, true) ?: [];
        } else {
            $settings = [];
        }
    }

    return $settings;
}

/**
 * Convert models from ai_settings.json format to grouped format
 *
 * @param array $models Models from JSON (model_id => config)
 * @return array Grouped models for UI display
 */
function _ai_group_models(array $models, string $provider = 'openai'): array {
    $groups = [];

    // Define group names based on model patterns
    foreach ($models as $modelId => $config) {
        $groupName = 'Other Models';

        if ($provider === 'openai') {
            if (preg_match('/^gpt-5\.2/', $modelId)) {
                $groupName = 'GPT-5.2 Series (Latest)';
            } elseif (preg_match('/^gpt-5/', $modelId)) {
                $groupName = 'GPT-5 Series';
            } elseif (preg_match('/^gpt-4\.1/', $modelId)) {
                $groupName = 'GPT-4.1 Series';
            } elseif (preg_match('/^o[34]/', $modelId)) {
                $groupName = 'O-Series (Reasoning)';
            } elseif (preg_match('/^gpt-4o/', $modelId)) {
                $groupName = 'GPT-4o (Legacy)';
            }
        } elseif ($provider === 'anthropic') {
            if (preg_match('/claude-(opus|sonnet|haiku)-4-5/', $modelId)) {
                $groupName = 'Claude 4.5 (Latest)';
            } elseif (preg_match('/claude-(sonnet|opus)-4-/', $modelId)) {
                $groupName = 'Claude 4';
            } elseif (preg_match('/claude-3-5/', $modelId)) {
                $groupName = 'Claude 3.5';
            } elseif (preg_match('/claude-3/', $modelId)) {
                $groupName = 'Claude 3';
            }
        } elseif ($provider === 'google') {
            if (preg_match('/gemini-3/', $modelId)) {
                $groupName = 'Gemini 3 (Preview)';
            } elseif (preg_match('/gemini-2\.5/', $modelId)) {
                $groupName = 'Gemini 2.5 (Latest)';
            } elseif (preg_match('/gemini-2\.0/', $modelId)) {
                $groupName = 'Gemini 2.0';
            } elseif (preg_match('/gemini-1\.5/', $modelId)) {
                $groupName = 'Gemini 1.5 (Legacy)';
            }
        } elseif ($provider === 'deepseek') {
            $groupName = 'DeepSeek Models';
        } elseif ($provider === 'huggingface') {
            $groupName = 'Popular Models';
        }

        // Mark legacy models
        if (!empty($config['legacy'])) {
            if (strpos($groupName, 'Legacy') === false) {
                $groupName = str_replace(' (Latest)', '', $groupName);
                $groupName .= ' (Legacy)';
            }
        }

        if (!isset($groups[$groupName])) {
            $groups[$groupName] = [];
        }

        $groups[$groupName][$modelId] = [
            'name' => $config['name'] ?? $modelId,
            'reasoning' => !empty($config['reasoning']) || !empty($config['extended_thinking']),
            'default' => !empty($config['recommended']),
            'legacy' => !empty($config['legacy']),
            'preview' => !empty($config['preview']),
        ];
    }

    return $groups;
}

/**
 * Get all available AI models grouped by series (for OpenAI - backward compatibility)
 * NOW READS FROM ai_settings.json
 *
 * @return array Model groups with model definitions
 */
function ai_get_model_groups(): array {
    $settings = _ai_load_settings_json();
    $models = $settings['providers']['openai']['models'] ?? [];

    if (empty($models)) {
        // Fallback to hardcoded if JSON is empty
        return [
            'GPT-4o (Legacy)' => [
                'gpt-4o' => ['name' => 'GPT-4o', 'reasoning' => false],
                'gpt-4o-mini' => ['name' => 'GPT-4o Mini', 'reasoning' => false],
            ],
        ];
    }

    return _ai_group_models($models, 'openai');
}

/**
 * Get flat list of allowed model IDs for validation
 *
 * @return array List of valid model identifiers
 */
function ai_get_allowed_models(): array {
    $models = [];
    foreach (ai_get_model_groups() as $group) {
        $models = array_merge($models, array_keys($group));
    }
    return $models;
}

/**
 * Check if a model ID is valid
 *
 * @param string $model Model identifier to check
 * @return bool True if model is valid
 */
function ai_is_valid_model(string $model): bool {
    return in_array($model, ai_get_allowed_models(), true);
}

/**
 * Check if model is a reasoning model (GPT-5, GPT-4.1, O-series, Claude extended_thinking)
 * These models use max_completion_tokens and don't support temperature/penalties
 *
 * @param string $model Model identifier
 * @return bool True if reasoning model
 */
function ai_is_reasoning_model(string $model): bool {
    // Check in JSON settings first
    $settings = _ai_load_settings_json();
    foreach ($settings['providers'] ?? [] as $provider => $providerConfig) {
        $models = $providerConfig['models'] ?? [];
        if (isset($models[$model])) {
            return !empty($models[$model]['reasoning']) || !empty($models[$model]['extended_thinking']);
        }
    }

    // Fallback to pattern matching
    return (bool) preg_match('/^(o[1-4]|gpt-[45]\.|gpt-5$)/', $model);
}

/**
 * Get the default model ID
 *
 * @return string Default model identifier
 */
function ai_get_default_model(): string {
    $settings = _ai_load_settings_json();
    $defaultModel = $settings['providers']['openai']['default_model'] ?? null;

    if ($defaultModel) {
        return $defaultModel;
    }

    // Find model marked as recommended
    foreach (ai_get_model_groups() as $group) {
        foreach ($group as $id => $config) {
            if (!empty($config['default'])) {
                return $id;
            }
        }
    }

    return 'gpt-4o-mini';
}

/**
 * Render HTML select element for model selection
 *
 * @param string $name Input name attribute
 * @param string $id Input id attribute
 * @param string|null $selected Currently selected model (null = use default)
 * @param array $attrs Additional HTML attributes
 * @return string HTML select element
 */
function ai_render_model_selector(string $name = 'model', string $id = 'modelSelect', ?string $selected = null, array $attrs = []): string {
    $selected = $selected ?? ai_get_default_model();
    $groups = ai_get_model_groups();

    $attrsStr = '';
    foreach ($attrs as $key => $value) {
        $attrsStr .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
    }

    $html = '<select name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($id) . '"' . $attrsStr . '>';

    foreach ($groups as $groupName => $models) {
        $html .= '<optgroup label="' . htmlspecialchars($groupName) . '">';
        foreach ($models as $modelId => $config) {
            $isSelected = ($modelId === $selected) ? ' selected' : '';
            $displayName = $config['name'];

            // Add badges
            if (!empty($config['default'])) {
                $displayName .= ' ⭐';
            }
            if (!empty($config['reasoning'])) {
                $displayName .= ' 🧠';
            }
            if (!empty($config['legacy'])) {
                $displayName .= ' [Legacy]';
            }
            if (!empty($config['preview'])) {
                $displayName .= ' [Preview]';
            }

            $html .= '<option value="' . htmlspecialchars($modelId) . '"' . $isSelected . '>';
            $html .= htmlspecialchars($displayName);
            $html .= '</option>';
        }
        $html .= '</optgroup>';
    }

    $html .= '</select>';

    return $html;
}

/**
 * Build API payload with correct parameters for model type
 *
 * @param string $model Model identifier
 * @param array $messages Messages array for API
 * @param int $maxTokens Maximum tokens
 * @param float $temperature Temperature (ignored for reasoning models)
 * @param float $frequencyPenalty Frequency penalty (ignored for reasoning models)
 * @param float $presencePenalty Presence penalty (ignored for reasoning models)
 * @return array API payload
 */
function ai_build_payload(
    string $model,
    array $messages,
    int $maxTokens = 4000,
    float $temperature = 0.7,
    float $frequencyPenalty = 0.15,
    float $presencePenalty = 0.1
): array {
    $isReasoning = ai_is_reasoning_model($model);

    $payload = [
        'model' => $model,
        'messages' => $messages
    ];

    if ($isReasoning) {
        // Reasoning models (GPT-5, GPT-4.1, O-series) use max_completion_tokens
        // They don't support temperature, frequency_penalty, presence_penalty
        // GPT-5 uses reasoning tokens, so we need higher limit
        $isGPT5 = (bool) preg_match('/^gpt-5/', $model);
        $payload['max_completion_tokens'] = $isGPT5 ? max($maxTokens * 4, 16000) : $maxTokens;
    } else {
        // Legacy models (GPT-4o) use standard parameters
        $payload['max_tokens'] = $maxTokens;
        $payload['temperature'] = $temperature;
        $payload['frequency_penalty'] = $frequencyPenalty;
        $payload['presence_penalty'] = $presencePenalty;
    }

    return $payload;
}

/**
 * Parse API response from various model formats
 *
 * @param array|null $data Decoded JSON response
 * @param string $rawResponse Raw response string for debugging
 * @return array ['ok' => bool, 'content' => string|null, 'error' => string|null, 'usage' => array|null]
 */
function ai_parse_response(?array $data, string $rawResponse = ''): array {
    if (!$data) {
        return ['ok' => false, 'content' => null, 'error' => 'Invalid JSON response', 'usage' => null];
    }

    $content = null;

    // Try Chat Completions format (GPT-4o, GPT-4.1, some GPT-5)
    if (isset($data['choices'][0]['message']['content'])) {
        $content = $data['choices'][0]['message']['content'];
    }
    // Try Responses API output_text (GPT-5.x preferred)
    elseif (isset($data['output_text']) && !empty($data['output_text'])) {
        $content = $data['output_text'];
    }
    // Try Responses API structured output (GPT-5.x)
    elseif (isset($data['output']) && is_array($data['output'])) {
        foreach ($data['output'] as $item) {
            if (isset($item['type']) && $item['type'] === 'message' && isset($item['content'])) {
                foreach ($item['content'] as $contentItem) {
                    if (isset($contentItem['text'])) {
                        $content = $contentItem['text'];
                        break 2;
                    }
                }
            }
            if (isset($item['content']) && is_string($item['content'])) {
                $content = $item['content'];
                break;
            }
        }
    }
    // Try legacy text completions
    elseif (isset($data['choices'][0]['text'])) {
        $content = $data['choices'][0]['text'];
    }
    // Try direct content field
    elseif (isset($data['content']) && is_string($data['content'])) {
        $content = $data['content'];
    }

    if (!$content) {
        return [
            'ok' => false,
            'content' => null,
            'error' => 'Empty response - unexpected format',
            'usage' => $data['usage'] ?? null
        ];
    }

    return [
        'ok' => true,
        'content' => trim($content),
        'error' => null,
        'usage' => $data['usage'] ?? null
    ];
}

// ============================================================================
// MULTI-PROVIDER SUPPORT - NOW READS FROM ai_settings.json
// ============================================================================

/**
 * Get all AI providers with their models
 * NOW READS FROM ai_settings.json
 *
 * @return array Providers with model configurations
 */
function ai_get_all_providers(): array {
    $settings = _ai_load_settings_json();
    $providers = [];

    // Provider display names
    $providerNames = [
        'openai' => 'OpenAI',
        'anthropic' => 'Anthropic (Claude)',
        'google' => 'Google (Gemini)',
        'deepseek' => 'DeepSeek',
        'huggingface' => 'HuggingFace',
        'ollama' => 'Ollama (Local)',
    ];

    foreach ($settings['providers'] ?? [] as $providerId => $providerConfig) {
        $models = $providerConfig['models'] ?? [];

        if (empty($models)) {
            continue;
        }

        $providers[$providerId] = [
            'name' => $providerNames[$providerId] ?? ucfirst($providerId),
            'groups' => _ai_group_models($models, $providerId),
        ];
    }

    // Fallback if no providers in JSON
    if (empty($providers)) {
        return [
            'openai' => [
                'name' => 'OpenAI',
                'groups' => [
                    'GPT-4o (Legacy)' => [
                        'gpt-4o' => ['name' => 'GPT-4o', 'reasoning' => false],
                        'gpt-4o-mini' => ['name' => 'GPT-4o Mini', 'reasoning' => false],
                    ],
                ]
            ],
        ];
    }

    return $providers;
}

/**
 * Get provider display names for dropdown
 *
 * @return array Provider ID => Display name
 */
function ai_get_provider_list(): array {
    $providers = ai_get_all_providers();
    $list = [];
    foreach ($providers as $id => $config) {
        $list[$id] = $config['name'];
    }
    return $list;
}

/**
 * Get models for a specific provider (flat list)
 *
 * @param string $provider Provider identifier
 * @return array Model ID => Model config
 */
function ai_get_models_for_provider(string $provider): array {
    $providers = ai_get_all_providers();
    if (!isset($providers[$provider])) {
        return [];
    }

    $models = [];
    foreach ($providers[$provider]['groups'] as $groupName => $groupModels) {
        foreach ($groupModels as $modelId => $modelConfig) {
            $models[$modelId] = $modelConfig;
        }
    }
    return $models;
}

/**
 * Get default model for a provider
 *
 * @param string $provider Provider identifier
 * @return string Default model ID or first model if no default
 */
function ai_get_provider_default_model(string $provider): string {
    // Check JSON settings first
    $settings = _ai_load_settings_json();
    $defaultModel = $settings['providers'][$provider]['default_model'] ?? null;

    if ($defaultModel) {
        return $defaultModel;
    }

    // Find model marked as default/recommended
    $models = ai_get_models_for_provider($provider);
    foreach ($models as $id => $config) {
        if (!empty($config['default'])) {
            return $id;
        }
    }
    return array_key_first($models) ?? '';
}

/**
 * Check if provider is valid
 *
 * @param string $provider Provider identifier
 * @return bool True if provider exists
 */
function ai_is_valid_provider(string $provider): bool {
    return isset(ai_get_all_providers()[$provider]);
}

/**
 * Check if model is valid for provider
 *
 * @param string $provider Provider identifier
 * @param string $model Model identifier
 * @return bool True if model exists for provider
 */
function ai_is_valid_provider_model(string $provider, string $model): bool {
    $models = ai_get_models_for_provider($provider);
    return isset($models[$model]);
}

/**
 * Check if model is reasoning model (for any provider)
 *
 * @param string $provider Provider identifier
 * @param string $model Model identifier
 * @return bool True if model supports reasoning
 */
function ai_is_provider_reasoning_model(string $provider, string $model): bool {
    $models = ai_get_models_for_provider($provider);
    return !empty($models[$model]['reasoning']);
}

// ============================================================================
// UI RENDERING FUNCTIONS
// ============================================================================

/**
 * Render provider dropdown
 *
 * @param string $name Input name attribute
 * @param string $id Input id attribute
 * @param string|null $selected Currently selected provider
 * @param array $attrs Additional HTML attributes
 * @return string HTML select element
 */
function ai_render_provider_selector(
    string $name = 'ai_provider',
    string $id = 'aiProviderSelect',
    ?string $selected = null,
    array $attrs = []
): string {
    $selected = $selected ?? 'openai';
    $providers = ai_get_provider_list();

    $attrsStr = '';
    foreach ($attrs as $key => $value) {
        $attrsStr .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
    }

    $html = '<select name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($id) . '"' . $attrsStr . '>';

    foreach ($providers as $providerId => $providerName) {
        $isSelected = ($providerId === $selected) ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars($providerId) . '"' . $isSelected . '>';
        $html .= htmlspecialchars($providerName);
        $html .= '</option>';
    }

    $html .= '</select>';
    return $html;
}

/**
 * Render model dropdown for specific provider
 *
 * @param string $provider Provider identifier
 * @param string $name Input name attribute
 * @param string $id Input id attribute
 * @param string|null $selected Currently selected model
 * @param array $attrs Additional HTML attributes
 * @return string HTML select element with optgroups
 */
function ai_render_provider_model_selector(
    string $provider,
    string $name = 'ai_model',
    string $id = 'aiModelSelect',
    ?string $selected = null,
    array $attrs = []
): string {
    $providers = ai_get_all_providers();
    if (!isset($providers[$provider])) {
        return '<select name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($id) . '"><option>No models</option></select>';
    }

    $selected = $selected ?? ai_get_provider_default_model($provider);
    $groups = $providers[$provider]['groups'];

    $attrsStr = '';
    foreach ($attrs as $key => $value) {
        $attrsStr .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
    }

    $html = '<select name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($id) . '"' . $attrsStr . '>';

    foreach ($groups as $groupName => $models) {
        $html .= '<optgroup label="' . htmlspecialchars($groupName) . '">';
        foreach ($models as $modelId => $modelConfig) {
            $isSelected = ($modelId === $selected) ? ' selected' : '';
            $displayName = $modelConfig['name'];

            // Add badges
            if (!empty($modelConfig['default'])) {
                $displayName .= ' ⭐';
            }
            if (!empty($modelConfig['reasoning'])) {
                $displayName .= ' 🧠';
            }
            if (!empty($modelConfig['legacy'])) {
                $displayName .= ' [Legacy]';
            }
            if (!empty($modelConfig['preview'])) {
                $displayName .= ' [Preview]';
            }

            $html .= '<option value="' . htmlspecialchars($modelId) . '"' . $isSelected . '>';
            $html .= htmlspecialchars($displayName);
            $html .= '</option>';
        }
        $html .= '</optgroup>';
    }

    $html .= '</select>';
    return $html;
}

/**
 * Get all models as JSON for JavaScript
 *
 * @return string JSON-encoded model data
 */
function ai_get_models_json(): string {
    $providers = ai_get_all_providers();
    $data = [];

    foreach ($providers as $providerId => $providerConfig) {
        $data[$providerId] = [];
        foreach ($providerConfig['groups'] as $groupName => $models) {
            foreach ($models as $modelId => $modelConfig) {
                $data[$providerId][$modelId] = [
                    'name' => $modelConfig['name'],
                    'group' => $groupName,
                    'default' => !empty($modelConfig['default']),
                    'reasoning' => !empty($modelConfig['reasoning']),
                    'legacy' => !empty($modelConfig['legacy']),
                    'preview' => !empty($modelConfig['preview']),
                ];
            }
        }
    }

    return json_encode($data);
}

/**
 * Render complete dual provider/model selector with JavaScript
 *
 * @param string $providerName Provider input name
 * @param string $modelName Model input name
 * @param string|null $selectedProvider Currently selected provider
 * @param string|null $selectedModel Currently selected model
 * @param array $containerAttrs Container div attributes
 * @return string HTML with JavaScript for dynamic model loading
 */
function ai_render_dual_selector(
    string $providerName = 'ai_provider',
    string $modelName = 'ai_model',
    ?string $selectedProvider = null,
    ?string $selectedModel = null,
    array $containerAttrs = []
): string {
    $selectedProvider = $selectedProvider ?? 'openai';
    $selectedModel = $selectedModel ?? ai_get_provider_default_model($selectedProvider);

    $containerId = 'aiSelectorContainer_' . uniqid();
    $providerSelectId = 'aiProviderSelect_' . uniqid();
    $modelSelectId = 'aiModelSelect_' . uniqid();

    $html = '<div class="ai-dual-selector" id="' . $containerId . '" style="display:flex;gap:15px;margin-bottom:15px;flex-wrap:wrap">';

    // Provider selector
    $html .= '<div class="ai-selector-group" style="flex:1;min-width:180px">';
    $html .= '<label style="display:block;margin-bottom:5px;font-weight:600;color:var(--text,#cdd6f4)">AI Provider</label>';
    $html .= ai_render_provider_selector($providerName, $providerSelectId, $selectedProvider, ['class' => 'form-control', 'style' => 'width:100%;padding:8px 12px;background:var(--bg3,#313244);border:1px solid var(--border,#313244);border-radius:6px;color:var(--text,#cdd6f4)']);
    $html .= '</div>';

    // Model selector
    $html .= '<div class="ai-selector-group" style="flex:2;min-width:250px">';
    $html .= '<label style="display:block;margin-bottom:5px;font-weight:600;color:var(--text,#cdd6f4)">Model</label>';
    $html .= '<select name="' . htmlspecialchars($modelName) . '" id="' . $modelSelectId . '" class="form-control" style="width:100%;padding:8px 12px;background:var(--bg3,#313244);border:1px solid var(--border,#313244);border-radius:6px;color:var(--text,#cdd6f4)">';
    $html .= '</select>';
    $html .= '</div>';

    $html .= '</div>';

    // JavaScript for dynamic model loading
    $html .= '<script>
(function() {
    const AI_MODELS = ' . ai_get_models_json() . ';
    const providerSelect = document.getElementById("' . $providerSelectId . '");
    const modelSelect = document.getElementById("' . $modelSelectId . '");
    const initialModel = "' . htmlspecialchars($selectedModel) . '";

    function updateModels() {
        const provider = providerSelect.value;
        const models = AI_MODELS[provider] || {};

        modelSelect.innerHTML = "";

        // Group models by their group name
        const groups = {};
        for (const [modelId, config] of Object.entries(models)) {
            const group = config.group || "Models";
            if (!groups[group]) groups[group] = [];
            groups[group].push({id: modelId, ...config});
        }

        // Render optgroups
        for (const [groupName, groupModels] of Object.entries(groups)) {
            const optgroup = document.createElement("optgroup");
            optgroup.label = groupName;

            for (const model of groupModels) {
                const option = document.createElement("option");
                option.value = model.id;
                let displayName = model.name;
                if (model.default) displayName += " ⭐";
                if (model.reasoning) displayName += " 🧠";
                if (model.legacy) displayName += " [Legacy]";
                if (model.preview) displayName += " [Preview]";
                option.textContent = displayName;
                if (model.default && !initialModel) option.selected = true;
                if (model.id === initialModel) option.selected = true;
                optgroup.appendChild(option);
            }

            modelSelect.appendChild(optgroup);
        }
    }

    providerSelect.addEventListener("change", updateModels);
    updateModels();
})();
</script>';

    return $html;
}
