<?php
/**
 * AI Model Selector Helper
 * Provides functions to load and render AI model selection UI
 *
 * @package CMS\Core
 * @since 2025-12
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

/**
 * Load AI settings from JSON config
 *
 * @return array AI settings configuration
 */
function ai_model_selector_load_settings(): array
{
    $settingsFile = CMS_ROOT . '/config/ai_settings.json';

    if (!file_exists($settingsFile)) {
        return [
            'default_provider' => 'openai',
            'providers' => []
        ];
    }

    $content = file_get_contents($settingsFile);
    $settings = json_decode($content, true);

    return is_array($settings) ? $settings : ['default_provider' => 'openai', 'providers' => []];
}

/**
 * Get enabled providers with their models
 *
 * @return array List of enabled providers
 */
function ai_model_selector_get_providers(): array
{
    $settings = ai_model_selector_load_settings();
    $providers = [];

    $providerMeta = [
        'openai' => ['name' => 'OpenAI', 'icon' => '🤖', 'color' => '#10a37f'],
        'anthropic' => ['name' => 'Anthropic Claude', 'icon' => '🧠', 'color' => '#d97757'],
        'google' => ['name' => 'Google Gemini', 'icon' => '✨', 'color' => '#4285f4'],
        'deepseek' => ['name' => 'DeepSeek', 'icon' => '🔮', 'color' => '#0066ff'],
        'huggingface' => ['name' => 'HuggingFace', 'icon' => '🤗', 'color' => '#ffcc00'],
        'ollama' => ['name' => 'Ollama (Local)', 'icon' => '🦙', 'color' => '#ffffff']
    ];

    foreach ($settings['providers'] ?? [] as $key => $config) {
        // Skip providers without proper model configuration (like huggingface with different structure)
        if (!isset($config['models']) || !is_array($config['models'])) {
            continue;
        }

        // Check if models have standard structure (name key)
        $firstModel = reset($config['models']);
        if (!is_array($firstModel) || !isset($firstModel['name'])) {
            continue;
        }

        $meta = $providerMeta[$key] ?? ['name' => ucfirst($key), 'icon' => '🔧', 'color' => '#888888'];

        $providers[$key] = [
            'key' => $key,
            'name' => $meta['name'],
            'icon' => $meta['icon'],
            'color' => $meta['color'],
            'enabled' => $config['enabled'] ?? false,
            'default_model' => $config['default_model'] ?? '',
            'models' => $config['models'] ?? []
        ];
    }

    return $providers;
}

/**
 * Get default provider key
 *
 * @return string Default provider key
 */
function ai_model_selector_get_default_provider(): string
{
    $settings = ai_model_selector_load_settings();
    return $settings['default_provider'] ?? 'openai';
}

/**
 * Format context size for display
 *
 * @param int $tokens Token count
 * @return string Formatted context size
 */
function ai_model_selector_format_context(int $tokens): string
{
    if ($tokens >= 1000000) {
        return round($tokens / 1000000, 1) . 'M';
    } elseif ($tokens >= 1000) {
        return round($tokens / 1000) . 'K';
    }
    return (string)$tokens;
}

/**
 * Format cost for display
 *
 * @param float $cost Cost per 1K tokens
 * @return string Formatted cost
 */
function ai_model_selector_format_cost(float $cost): string
{
    if ($cost < 0.001) {
        return '$' . number_format($cost * 1000, 2) . '/M';
    }
    return '$' . number_format($cost, 4) . '/1K';
}

/**
 * Render the model selector HTML
 *
 * @param string $idPrefix ID prefix for form elements
 * @return string HTML output
 */
function ai_model_selector_render(string $idPrefix = 'ai'): string
{
    $providers = ai_model_selector_get_providers();
    $defaultProvider = ai_model_selector_get_default_provider();

    if (empty($providers)) {
        return '<div class="alert alert-warning">No AI providers configured. Please configure providers in AI Settings.</div>';
    }

    $html = '<div class="model-selector-section">';
    $html .= '<div class="model-selector-header"><span class="model-selector-icon">🧠</span> AI Model Selection</div>';

    // Provider selector
    $html .= '<div class="model-selector-row">';
    $html .= '<label class="model-selector-label">Provider</label>';
    $html .= '<select id="' . htmlspecialchars($idPrefix) . '-provider" class="form-select model-provider-select">';

    foreach ($providers as $key => $provider) {
        $selected = ($key === $defaultProvider) ? ' selected' : '';
        $disabled = !$provider['enabled'] ? ' disabled' : '';
        $statusText = !$provider['enabled'] ? ' (Not Configured)' : '';
        $html .= '<option value="' . htmlspecialchars($key) . '"' . $selected . $disabled . ' data-icon="' . htmlspecialchars($provider['icon']) . '">';
        $html .= htmlspecialchars($provider['icon'] . ' ' . $provider['name'] . $statusText);
        $html .= '</option>';
    }

    $html .= '</select>';
    $html .= '</div>';

    // Model selector
    $html .= '<div class="model-selector-row">';
    $html .= '<label class="model-selector-label">Model</label>';
    $html .= '<select id="' . htmlspecialchars($idPrefix) . '-model" class="form-select model-model-select">';
    $html .= '</select>';
    $html .= '</div>';

    // Model info display
    $html .= '<div class="model-info" id="' . htmlspecialchars($idPrefix) . '-model-info">';
    $html .= '<span class="model-info-item" id="' . htmlspecialchars($idPrefix) . '-context"><span class="info-label">Context:</span> <span class="info-value">-</span></span>';
    $html .= '<span class="model-info-item" id="' . htmlspecialchars($idPrefix) . '-cost"><span class="info-label">Cost:</span> <span class="info-value">-</span></span>';
    $html .= '</div>';

    $html .= '</div>';

    // Embed providers data as JSON for JavaScript
    $html .= '<script id="' . htmlspecialchars($idPrefix) . '-providers-data" type="application/json">';
    $html .= json_encode($providers);
    $html .= '</script>';

    return $html;
}

/**
 * Get JavaScript initialization code for the model selector
 *
 * @param string $idPrefix ID prefix for form elements
 * @return string JavaScript code
 */
function ai_model_selector_js(string $idPrefix = 'ai'): string
{
    $defaultProvider = ai_model_selector_get_default_provider();

    return <<<JS
(function() {
    const prefix = '{$idPrefix}';
    const defaultProvider = '{$defaultProvider}';

    const providerSelect = document.getElementById(prefix + '-provider');
    const modelSelect = document.getElementById(prefix + '-model');
    const contextInfo = document.getElementById(prefix + '-context');
    const costInfo = document.getElementById(prefix + '-cost');
    const providersDataEl = document.getElementById(prefix + '-providers-data');

    if (!providerSelect || !modelSelect || !providersDataEl) return;

    let providers = {};
    try {
        providers = JSON.parse(providersDataEl.textContent);
    } catch(e) {
        console.error('Failed to parse providers data:', e);
        return;
    }

    function formatContext(tokens) {
        if (tokens >= 1000000) return (tokens / 1000000).toFixed(1) + 'M';
        if (tokens >= 1000) return Math.round(tokens / 1000) + 'K';
        return tokens.toString();
    }

    function formatCost(cost) {
        if (cost < 0.001) return '\$' + (cost * 1000).toFixed(2) + '/M';
        return '\$' + cost.toFixed(4) + '/1K';
    }

    function updateModels() {
        const providerKey = providerSelect.value;
        const provider = providers[providerKey];

        if (!provider) return;

        modelSelect.innerHTML = '';

        Object.entries(provider.models).forEach(([modelKey, modelData]) => {
            const option = document.createElement('option');
            option.value = modelKey;
            option.textContent = modelData.name || modelKey;
            if (modelKey === provider.default_model) {
                option.selected = true;
            }
            modelSelect.appendChild(option);
        });

        updateModelInfo();
    }

    function updateModelInfo() {
        const providerKey = providerSelect.value;
        const modelKey = modelSelect.value;
        const provider = providers[providerKey];

        if (!provider || !provider.models[modelKey]) {
            if (contextInfo) contextInfo.querySelector('.info-value').textContent = '-';
            if (costInfo) costInfo.querySelector('.info-value').textContent = '-';
            return;
        }

        const model = provider.models[modelKey];

        if (contextInfo && model.max_tokens) {
            contextInfo.querySelector('.info-value').textContent = formatContext(model.max_tokens);
        }

        if (costInfo && model.cost_per_1k_input !== undefined) {
            const inputCost = formatCost(model.cost_per_1k_input);
            const outputCost = formatCost(model.cost_per_1k_output || 0);
            costInfo.querySelector('.info-value').textContent = inputCost + ' in / ' + outputCost + ' out';
        }
    }

    providerSelect.addEventListener('change', updateModels);
    modelSelect.addEventListener('change', updateModelInfo);

    // Initialize
    updateModels();

    // Export for external access
    window[prefix + 'ModelSelector'] = {
        getProvider: () => providerSelect.value,
        getModel: () => modelSelect.value,
        getSelection: () => ({ provider: providerSelect.value, model: modelSelect.value })
    };
})();
JS;
}
