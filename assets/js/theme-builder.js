class ThemeBuilder {
    constructor() {
        this.themeSelector = document.querySelector('.theme-selector select');
        this.themePreview = document.querySelector('.theme-preview');
        this.currentTheme = null;
        this.presets = {};

        this.init();
    }

    async init() {
        await this.loadPresets();
        this.setupEventListeners();
        this.renderThemeOptions();
    }

    async loadPresets() {
        try {
            const response = await fetch('/api/theme/load.php');
            this.presets = await response.json();
            console.log('Theme presets loaded:', this.presets);
        } catch (error) {
            console.error('Error loading theme presets:', error);
        }
    }

    renderThemeOptions() {
        // Clear existing options
        this.themeSelector.innerHTML = '';

        // Add default option
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Select a theme...';
        this.themeSelector.appendChild(defaultOption);

        // Add theme options
        Object.keys(this.presets).forEach(presetName => {
            const option = document.createElement('option');
            option.value = presetName;
            option.textContent = this.presets[presetName].name;
            this.themeSelector.appendChild(option);
        });
    }

    setupEventListeners() {
        // Theme selector change
        this.themeSelector.addEventListener('change', (e) => {
            const selectedTheme = e.target.value;
            if (selectedTheme && this.presets[selectedTheme]) {
                this.applyTheme(this.presets[selectedTheme]);
                this.renderThemePreviews();
            }
        });
    }

    applyTheme(themeData) {
        this.currentTheme = themeData;

        // Update CSS variables
        const root = document.documentElement;
        root.style.setProperty('--bg-color', themeData.colors.background);
        root.style.setProperty('--text-color', themeData.colors.text);
        root.style.setProperty('--primary-color', themeData.colors.primary);
        root.style.setProperty('--secondary-color', themeData.colors.secondary);
        root.style.setProperty('--accent-color', themeData.colors.accent);

        console.log('Theme applied:', themeData.name);
    }

    renderThemePreviews() {
        this.themePreview.innerHTML = '';

        Object.keys(this.presets).forEach(presetName => {
            const theme = this.presets[presetName];
            const preview = document.createElement('div');
            preview.className = 'theme-preview-item';
            if (this.currentTheme && this.currentTheme.name === theme.name) {
                preview.classList.add('active');
            }

            preview.style.background = `
                linear-gradient(
                    135deg,
                    ${theme.colors.background} 0%,
                    ${theme.colors.primary} 33%,
                    ${theme.colors.secondary} 66%,
                    ${theme.colors.accent} 100%
                )
            `;

            preview.addEventListener('click', () => {
                this.applyTheme(theme);
                this.themeSelector.value = presetName;
                document.querySelectorAll('.theme-preview-item').forEach(item => {
                    item.classList.remove('active');
                });
                preview.classList.add('active');
            });

            this.themePreview.appendChild(preview);
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ThemeBuilder();
});
