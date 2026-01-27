/**
 * Version Comparison UI Controller
 * Handles version selection and diff rendering
 */

class VersionComparison {
    constructor() {
        this.version1Select = document.getElementById('version1');
        this.version2Select = document.getElementById('version2');
        this.compareBtn = document.getElementById('compare-btn');
        this.diffContent = document.getElementById('diff-content');
        
        this.initEventListeners();
        this.loadVersions();
    }

    initEventListeners() {
        this.compareBtn.addEventListener('click', () => this.compareVersions());
    }

    async loadVersions() {
        try {
            const response = await fetch('/api/versions');
            const versions = await response.json();
            
            versions.forEach(version => {
                const option1 = document.createElement('option');
                const option2 = document.createElement('option');
                
                option1.value = option2.value = version.id;
                option1.textContent = option2.textContent = `${version.name} (${version.number})`;
                
                this.version1Select.appendChild(option1);
                this.version2Select.appendChild(option2);
            });
        } catch (error) {
            console.error('Failed to load versions:', error);
        }
    }

    async compareVersions() {
        const version1 = this.version1Select.value;
        const version2 = this.version2Select.value;
        
        if (!version1 || !version2) {
            alert('Please select both versions to compare');
            return;
        }

        try {
            const response = await fetch(`/api/version/compare?v1=${version1}&v2=${version2}`);
            const diff = await response.json();
            
            this.renderDiff(diff);
        } catch (error) {
            console.error('Comparison failed:', error);
            this.diffContent.innerHTML = '<div class="error">Failed to compare versions</div>';
        }
    }

    renderDiff(diff) {
        // TODO: Implement diff rendering logic
        this.diffContent.innerHTML = '<div class="notice">Diff rendering coming soon</div>';
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new VersionComparison();
});