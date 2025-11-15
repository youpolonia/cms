/**
 * Version Comparison UI Controls
 */
class VersionComparator {
    constructor(options) {
        this.options = options;
        this.container = document.querySelector(options.container);
        this.diffIndex = 0;
        this.diffItems = [];
        this.initElements();
        this.bindEvents();
    }

    initElements() {
        // Create controls container
        this.controlsContainer = document.createElement('div');
        this.controlsContainer.className = 'controls';
        
        // Version selectors
        this.oldVersionSelect = document.createElement('select');
        this.oldVersionSelect.className = 'version-select';
        this.oldVersionSelect.id = 'old-version-select';
        
        this.newVersionSelect = document.createElement('select');
        this.newVersionSelect.className = 'version-select';
        this.newVersionSelect.id = 'new-version-select';
        
        // Navigation buttons
        this.navContainer = document.createElement('div');
        this.navContainer.className = 'nav-buttons';
        
        this.prevBtn = document.createElement('button');
        this.prevBtn.className = 'nav-btn';
        this.prevBtn.id = 'prev-diff';
        this.prevBtn.textContent = 'Previous Change';
        
        this.nextBtn = document.createElement('button');
        this.nextBtn.className = 'nav-btn';
        this.nextBtn.id = 'next-diff';
        this.nextBtn.textContent = 'Next Change';
        
        this.navContainer.appendChild(this.prevBtn);
        this.navContainer.appendChild(this.nextBtn);
        
        // Stats display
        this.statsDisplay = document.createElement('div');
        this.statsDisplay.className = 'stats';
        
        // Loading and error states
        this.loadingIndicator = document.createElement('div');
        this.loadingIndicator.className = 'loading';
        this.loadingIndicator.textContent = 'Loading comparison...';
        
        this.errorDisplay = document.createElement('div');
        this.errorDisplay.className = 'error';
        this.errorDisplay.style.display = 'none';
        
        // Diff container
        this.diffContainer = document.createElement('div');
        this.diffContainer.className = 'diff-view';
        
        this.leftPanel = document.createElement('div');
        this.leftPanel.className = 'old-version';
        this.leftPanel.innerHTML = '<h3>Old Version</h3><div class="content-container"></div>';
        
        this.rightPanel = document.createElement('div');
        this.rightPanel.className = 'new-version';
        this.rightPanel.innerHTML = '<h3>New Version</h3><div class="content-container"></div>';
        
        this.diffContainer.appendChild(this.leftPanel);
        this.diffContainer.appendChild(this.rightPanel);
        
        // Assemble all elements
        this.controlsContainer.appendChild(this.oldVersionSelect);
        this.controlsContainer.appendChild(this.newVersionSelect);
        this.container.appendChild(this.controlsContainer);
        this.container.appendChild(this.navContainer);
        this.container.appendChild(this.statsDisplay);
        this.container.appendChild(this.loadingIndicator);
        this.container.appendChild(this.errorDisplay);
        this.container.appendChild(this.diffContainer);
    }

    bindEvents() {
        this.prevBtn.addEventListener('click', () => this.navigateDiff(-1));
        this.nextBtn.addEventListener('click', () => this.navigateDiff(1));
        this.oldVersionSelect.addEventListener('change', () => this.handleVersionChange());
        this.newVersionSelect.addEventListener('change', () => this.handleVersionChange());
    }

    async loadVersions() {
        this.showLoading();
        this.clearError();
        
        try {
            const response = await fetch(`${this.options.apiEndpoint}?contentId=${this.options.contentId}&oldVersionId=${this.options.oldVersionId}&newVersionId=${this.options.newVersionId}`);
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Comparison failed');
            }
            
            this.displayDiff(data.visualDiff);
            this.updateStats(data);
        } catch (error) {
            this.showError(error.message);
            console.error('Version comparison error:', error);
        } finally {
            this.hideLoading();
        }
    }

    async loadVersionOptions() {
        try {
            const response = await fetch(`${this.options.apiEndpoint}/versions?contentId=${this.options.contentId}`);
            const versions = await response.json();
            
            this.populateVersionSelects(versions);
        } catch (error) {
            console.error('Failed to load version options:', error);
        }
    }

    populateVersionSelects(versions) {
        this.oldVersionSelect.innerHTML = '';
        this.newVersionSelect.innerHTML = '';
        
        versions.forEach(version => {
            const option1 = document.createElement('option');
            option1.value = version.id;
            option1.textContent = version.name;
            
            const option2 = document.createElement('option');
            option2.value = version.id;
            option2.textContent = version.name;
            
            this.oldVersionSelect.appendChild(option1);
            this.newVersionSelect.appendChild(option2);
        });
        
        // Set initial selections if provided
        if (this.options.oldVersionId) {
            this.oldVersionSelect.value = this.options.oldVersionId;
        }
        if (this.options.newVersionId) {
            this.newVersionSelect.value = this.options.newVersionId;
        }
    }

    handleVersionChange() {
        this.options.oldVersionId = this.oldVersionSelect.value;
        this.options.newVersionId = this.newVersionSelect.value;
        this.loadVersions();
    }

    updateStats(data) {
        let added = 0;
        let removed = 0;
        let changed = 0;
        
        data.diff.forEach(line => {
            if (line.startsWith('+')) added++;
            else if (line.startsWith('-')) removed++;
            else if (line.includes('->')) changed++;
        });
        
        this.statsDisplay.innerHTML = `
            <div class="current-version">
                Comparing version ${data.oldVersion} to ${data.newVersion}
            </div>
            <div class="change-stats">
                Changes: ${added} added, ${removed} removed, ${changed} modified
            </div>
        `;
    }

    displayDiff(visualDiff) {
        this.leftPanel.querySelector('.content-container').innerHTML = visualDiff.left;
        this.rightPanel.querySelector('.content-container').innerHTML = visualDiff.right;
        this.diffItems = this.extractDiffItems();
        this.diffIndex = 0;
        this.updateNavigation();
        this.highlightCurrentDiff();
    }
    
    extractDiffItems() {
        const items = [];
        const leftLines = this.leftPanel.querySelectorAll('.diff-line');
        
        leftLines.forEach((line, index) => {
            if (line.classList.contains('diff-added') ||
                line.classList.contains('diff-removed') ||
                line.classList.contains('diff-changed')) {
                items.push(index);
            }
        });
        
        return items;
    }
    
    showLoading() {
        this.loadingIndicator.style.display = 'block';
        this.diffContainer.style.display = 'none';
        this.errorDisplay.style.display = 'none';
    }
    
    hideLoading() {
        this.loadingIndicator.style.display = 'none';
        this.diffContainer.style.display = 'flex';
    }
    
    showError(message) {
        this.errorDisplay.textContent = message;
        this.errorDisplay.style.display = 'block';
    }
    
    clearError() {
        this.errorDisplay.style.display = 'none';
        this.errorDisplay.textContent = '';
    }
    
    navigateDiff(direction) {
        this.diffIndex += direction;
        this.updateNavigation();
        this.highlightCurrentDiff();
    }
    
    updateNavigation() {
        // Update navigation buttons if they exist
        const prevBtn = document.getElementById('prev-diff');
        const nextBtn = document.getElementById('next-diff');
        
        if (prevBtn && nextBtn) {
            prevBtn.disabled = this.diffIndex <= 0;
            nextBtn.disabled = this.diffIndex >= this.diffItems.length - 1;
        }
    }
    
    highlightCurrentDiff() {
        const leftLines = this.leftPanel.querySelectorAll('.diff-line');
        const rightLines = this.rightPanel.querySelectorAll('.diff-line');
        
        // Clear previous highlights
        document.querySelectorAll('.diff-highlight').forEach(el => {
            el.classList.remove('diff-highlight');
        });
        
        if (this.diffItems.length > 0 && this.diffIndex >= 0 && this.diffIndex < this.diffItems.length) {
            const lineIndex = this.diffItems[this.diffIndex];
            
            if (leftLines[lineIndex]) leftLines[lineIndex].classList.add('diff-highlight');
            if (rightLines[lineIndex]) rightLines[lineIndex].classList.add('diff-highlight');
            
            // Scroll to highlighted line
            leftLines[lineIndex]?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            rightLines[lineIndex]?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}