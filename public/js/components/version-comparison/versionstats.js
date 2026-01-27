class VersionStats {
    constructor(containerId, version1, version2) {
        this.container = document.getElementById(containerId);
        this.version1 = version1;
        this.version2 = version2;
    }

    calculateStats() {
        return {
            linesAdded: this.countLines(this.version2) - this.countLines(this.version1),
            linesRemoved: this.countLines(this.version1) - this.countLines(this.version2),
            totalChanges: Math.abs(this.countLines(this.version1) - this.countLines(this.version2))
        };
    }

    countLines(content) {
        return content.split('\n').length;
    }

    render() {
        const stats = this.calculateStats();
        this.container.innerHTML = `
            <div class="version-stats">
                <h3>Version Statistics</h3>
                <p>Lines Added: ${stats.linesAdded}</p>
                <p>Lines Removed: ${stats.linesRemoved}</p>
                <p>Total Changes: ${stats.totalChanges}</p>
            </div>
        `;
    }
}

export default VersionStats;