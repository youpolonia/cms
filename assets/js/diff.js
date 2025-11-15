class Diff {
    constructor(options = {}) {
        this.options = {
            sideBySide: true,
            htmlAware: false,
            ...options
        };
    }

    compare(text1, text2) {
        const lines1 = text1.split('\n');
        const lines2 = text2.split('\n');
        
        const result = {
            unified: '',
            sideBySide: '',
            stats: {
                chars_added: 0,
                chars_removed: 0,
                lines_changed: 0
            }
        };

        // Basic diff implementation (would be enhanced with actual diff algorithm)
        const maxLength = Math.max(lines1.length, lines2.length);
        let sideBySideHtml = '<div class="diff-container">';
        
        for (let i = 0; i < maxLength; i++) {
            const line1 = lines1[i] || '';
            const line2 = lines2[i] || '';
            
            if (line1 !== line2) {
                result.stats.lines_changed++;
                result.stats.chars_removed += line1.length;
                result.stats.chars_added += line2.length;
                
                sideBySideHtml += `<div class="diff-line changed">
                    <div class="old-line">${line1}</div>
                    <div class="new-line">${line2}</div>
                </div>`;
            } else {
                sideBySideHtml += `<div class="diff-line">
                    <div class="old-line">${line1}</div>
                    <div class="new-line">${line2}</div>
                </div>`;
            }
        }
        
        sideBySideHtml += '</div>';
        result.sideBySide = sideBySideHtml;
        
        return result;
    }

    static initDiffViewer(containerId, options) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const version1 = container.dataset.version1;
        const version2 = container.dataset.version2;
        
        // API call to get versions and compare
        fetch(`/api/versions/compare/${version1}/${version2}`)
            .then(response => response.json())
            .then(data => {
                const diff = new Diff(options);
                const result = diff.compare(data.oldText, data.newText);
                
                if (options.sideBySide) {
                    container.innerHTML = result.sideBySide;
                } else {
                    container.innerHTML = result.unified;
                }
            });
    }

    highlight(element) {
        // Implementation for highlighting changes
    }
}