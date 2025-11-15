class DiffViewer {
    constructor(options) {
        this.container = options.container;
        this.oldContent = options.oldContent || '';
        this.newContent = options.newContent || '';
        this.renderMode = options.renderMode || 'side-by-side';
    }

    /**
     * Render the diff visualization
     */
    render() {
        fetch('/admin/versions.php?action=diff', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                oldContent: this.oldContent,
                newContent: this.newContent,
                format: 'html'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.container.innerHTML = data.html;
                this.highlightChanges();
            } else {
                console.error('Diff error:', data.error);
            }
        })
        .catch(error => {
            console.error('Diff request failed:', error);
        });
    }

    /**
     * Highlight changes in the diff
     */
    highlightChanges() {
        const changes = this.container.querySelectorAll('.diff-line');
        changes.forEach(line => {
            if (line.classList.contains('inserted')) {
                line.addEventListener('mouseenter', () => {
                    const newLineNum = line.dataset.newLine;
                    if (newLineNum) {
                        const corresponding = this.container.querySelector(
                            `.diff-line[data-old-line="${newLineNum}"]`
                        );
                        if (corresponding) {
                            corresponding.classList.add('highlight');
                            line.classList.add('highlight');
                        }
                    }
                });

                line.addEventListener('mouseleave', () => {
                    this.container.querySelectorAll('.highlight')
                        .forEach(el => el.classList.remove('highlight'));
                });
            }
        });
    }

    /**
     * Switch between diff view modes
     * @param {string} mode - 'side-by-side' or 'inline'
     */
    setRenderMode(mode) {
        this.renderMode = mode;
        this.container.className = `diff-container ${mode}`;
        this.render();
    }
}

// Export for module usage if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DiffViewer;
}