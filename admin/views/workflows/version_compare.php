<?php
declare(strict_types=1);
/** Workflow Version Comparison View */
?><div class="version-comparison-container">
  <div class="version-header">
    <h3>Comparing Versions <span id="versionLeft"></span> - <span id="versionRight"></span></h3>
    <div class="view-options">
      <button class="btn btn-sm" data-view="unified">Unified View</button>
      <button class="btn btn-sm" data-view="split">Split View</button>
    </div>
  </div>

  <div class="diff-container">
    <table class="diff-table" id="diffTable">
      <thead>
        <tr>
          <th>Line</th>
          <th>Original</th>
          <th>Modified</th>
        </tr>
      </thead>
      <tbody id="diffContent"></tbody>
    </table>
  </div>

  <div class="comparison-actions">
    <button class="btn btn-primary" id="restoreVersion">Restore This Version</button>
    <button class="btn btn-secondary" id="closeCompare">Close Comparison</button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  loadComparisonData(params.get('v1'), params.get('v2'));
  
  document.querySelectorAll('[data-view]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelector('.diff-container').dataset.view = btn.dataset.view;
    });
  });
});

async function loadComparisonData(v1, v2) {
  try {
    const response = await fetch(`/api/workflows/versions/compare?v1=${v1}&v2=${v2}`);
    const diff = await response.json();
    renderDiff(diff);
  } catch (error) {
    console.error('Comparison load failed:', error);
  }
}
</script>
