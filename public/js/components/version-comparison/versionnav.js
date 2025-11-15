class VersionNav {
    constructor(containerId, currentVersion, versions) {
        this.container = document.getElementById(containerId);
        this.currentVersion = currentVersion;
        this.versions = versions;
        this.onVersionChange = null;
    }

    setVersionChangeHandler(handler) {
        this.onVersionChange = handler;
    }

    render() {
        const currentIndex = this.versions.indexOf(this.currentVersion);
        const prevVersion = currentIndex > 0 ? this.versions[currentIndex - 1] : null;
        const nextVersion = currentIndex < this.versions.length - 1 ? this.versions[currentIndex + 1] : null;

        this.container.innerHTML = `
            <div class="version-nav">
                <button ${!prevVersion ? 'disabled' : ''} 
                    class="nav-btn prev-btn" 
                    ${prevVersion ? `data-version="${prevVersion}"` : ''}>
                    ← Previous
                </button>
                <span class="current-version">Version ${this.currentVersion}</span>
                <button ${!nextVersion ? 'disabled' : ''} 
                    class="nav-btn next-btn" 
                    ${nextVersion ? `data-version="${nextVersion}"` : ''}>
                    Next →
                </button>
            </div>
        `;

        this.container.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (this.onVersionChange) {
                    this.onVersionChange(e.target.dataset.version);
                }
            });
        });
    }
}

export default VersionNav;