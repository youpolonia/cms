<div class="version-comparison-container">
    <h2>Version Comparison</h2>
    
    <div class="comparison-controls">
        <div class="form-group">
            <label>View Mode:</label>
            <select id="view-mode-select" class="form-control">
                <option value="side-by-side">Side by Side</option>
                <option value="unified">Unified</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Diff Type:</label>
            <select id="diff-type-select" class="form-control">
                <option value="regular">Regular</option>
                <option value="html">HTML-Aware</option>
            </select>
        </div>
        
        <button id="refresh-comparison" class="btn btn-primary">
            Refresh Comparison
        </button>
    </div>
    
    <div class="comparison-stats" id="comparison-stats"></div>
    
    <div class="diff-viewer-container">
        <div id="diff-viewer"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const diffViewer = new Diff({
        sideBySide: true,
        htmlAware: false
    });
    
    // Initialize with current versions
    const version1 = <?= $version1 ?>; ?>
    const version2 = <?= $version2 ?>; ?>
    
    fetch(`/api/versions/compare/${version1}/${version2}`)
        .then(response => response.json())
        .then(data => {
            const result = diffViewer.compare(data.oldText, data.newText);
            document.getElementById('diff-viewer').innerHTML = result.sideBySide;
            
            // Update stats
            document.getElementById('comparison-stats').innerHTML = `
                <div class="stat-item">Added: ${result.stats.chars_added} chars</div>
                <div class="stat-item">Removed: ${result.stats.chars_removed} chars</div>
                <div class="stat-item">Changed: ${result.stats.lines_changed} lines</div>
            `;
        });
        
    // Handle view mode changes
    document.getElementById('view-mode-select').addEventListener('change', function() {
        diffViewer.options.sideBySide = this.value === 'side-by-side';
        // Re-render comparison
    });
    
    // Handle diff type changes
    document.getElementById('diff-type-select').addEventListener('change', function() {
        diffViewer.options.htmlAware = this.value === 'html';
        // Re-render comparison
    });
});
</script>
