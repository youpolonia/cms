<?php
/**
 * Layout Gallery View (modal content)
 * Displays page structure layouts for selection in builder
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

$csrfToken = $_SESSION['csrf_token'] ?? '';
?>

<div class="jtb-layout-gallery">
    <div class="jtb-layout-gallery-header">
        <h2>Choose a Layout</h2>
        <p>Select a page structure to start with. You can customize it later.</p>
    </div>

    <div class="jtb-layout-gallery-filters">
        <button class="jtb-filter-btn active" data-category="all">All</button>
        <button class="jtb-filter-btn" data-category="rows">Row Structures</button>
        <button class="jtb-filter-btn" data-category="sections">Section Layouts</button>
        <button class="jtb-filter-btn" data-category="pages">Page Layouts</button>
    </div>

    <div class="jtb-layout-gallery-grid" id="layoutGalleryGrid">
        <!-- Layouts will be loaded here -->
        <div class="jtb-layout-gallery-loading">
            <div class="jtb-spinner"></div>
            <p>Loading layouts...</p>
        </div>
    </div>
</div>

<style>
.jtb-layout-gallery {
    padding: 24px;
    max-height: 70vh;
    overflow-y: auto;
}

.jtb-layout-gallery-header {
    text-align: center;
    margin-bottom: 24px;
}

.jtb-layout-gallery-header h2 {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 8px 0;
}

.jtb-layout-gallery-header p {
    color: #64748b;
    margin: 0;
}

.jtb-layout-gallery-filters {
    display: flex;
    gap: 8px;
    justify-content: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.jtb-filter-btn {
    padding: 8px 16px;
    border: 1px solid #e2e8f0;
    background: #fff;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
}

.jtb-filter-btn:hover {
    border-color: #3b82f6;
    color: #3b82f6;
}

.jtb-filter-btn.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: #fff;
}

.jtb-layout-gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
}

.jtb-layout-gallery-loading {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #64748b;
}

.jtb-layout-card {
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s;
}

.jtb-layout-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.jtb-layout-card.selected {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.jtb-layout-card-preview {
    aspect-ratio: 16/10;
    background: #f8fafc;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    justify-content: center;
}

.jtb-layout-card-info {
    padding: 12px;
    border-top: 1px solid #e2e8f0;
}

.jtb-layout-card-name {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.jtb-layout-card-type {
    font-size: 11px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Layout preview visualizations */
.jtb-preview-row {
    display: flex;
    gap: 4px;
    height: 20px;
}

.jtb-preview-col {
    background: #cbd5e1;
    border-radius: 3px;
    flex-shrink: 0;
}

.jtb-preview-section {
    background: #e2e8f0;
    border-radius: 4px;
    padding: 6px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

/* Column width classes */
.jtb-col-1 { flex: 1; }
.jtb-col-1_2 { flex: 0 0 calc(50% - 2px); }
.jtb-col-1_3 { flex: 0 0 calc(33.333% - 3px); }
.jtb-col-2_3 { flex: 0 0 calc(66.666% - 3px); }
.jtb-col-1_4 { flex: 0 0 calc(25% - 3px); }
.jtb-col-3_4 { flex: 0 0 calc(75% - 3px); }
.jtb-col-1_5 { flex: 0 0 calc(20% - 4px); }
.jtb-col-2_5 { flex: 0 0 calc(40% - 3px); }
.jtb-col-3_5 { flex: 0 0 calc(60% - 3px); }
.jtb-col-1_6 { flex: 0 0 calc(16.666% - 4px); }
</style>

<script>
(function() {
    const grid = document.getElementById('layoutGalleryGrid');
    const filterBtns = document.querySelectorAll('.jtb-filter-btn');
    let layouts = [];
    let selectedLayout = null;

    // Load layouts
    async function loadLayouts() {
        try {
            const response = await fetch('/api/jtb/layouts');
            const data = await response.json();

            if (data.success) {
                layouts = data.layouts;
                renderLayouts('all');
            } else {
                grid.innerHTML = '<p class="jtb-layout-gallery-loading">Failed to load layouts</p>';
            }
        } catch (error) {
            console.error('Error loading layouts:', error);
            grid.innerHTML = '<p class="jtb-layout-gallery-loading">Error loading layouts</p>';
        }
    }

    // Render layouts
    function renderLayouts(category) {
        const filtered = category === 'all'
            ? layouts
            : layouts.filter(l => l.category === category);

        if (filtered.length === 0) {
            grid.innerHTML = '<p class="jtb-layout-gallery-loading">No layouts found</p>';
            return;
        }

        grid.innerHTML = filtered.map(layout => `
            <div class="jtb-layout-card" data-id="${layout.id}" data-type="${layout.layout_type}">
                <div class="jtb-layout-card-preview">
                    ${renderLayoutPreview(layout)}
                </div>
                <div class="jtb-layout-card-info">
                    <h4 class="jtb-layout-card-name">${escapeHtml(layout.name)}</h4>
                    <span class="jtb-layout-card-type">${layout.layout_type}</span>
                </div>
            </div>
        `).join('');

        // Bind click events
        grid.querySelectorAll('.jtb-layout-card').forEach(card => {
            card.addEventListener('click', () => selectLayout(card));
            card.addEventListener('dblclick', () => useLayout(card.dataset.id));
        });
    }

    // Render layout preview
    function renderLayoutPreview(layout) {
        const content = layout.content?.content || [];

        if (layout.layout_type === 'row') {
            const cols = (layout.column_structure || '1').split(',');
            return `<div class="jtb-preview-row">${cols.map(c =>
                `<div class="jtb-preview-col jtb-col-${c.replace('/', '_')}"></div>`
            ).join('')}</div>`;
        }

        if (layout.layout_type === 'section') {
            const section = content[0] || {};
            const rows = section.children || [];
            return `<div class="jtb-preview-section">${rows.map(row => {
                const cols = (row.attrs?.columns || '1').split(',');
                return `<div class="jtb-preview-row">${cols.map(c =>
                    `<div class="jtb-preview-col jtb-col-${c.replace('/', '_')}"></div>`
                ).join('')}</div>`;
            }).join('')}</div>`;
        }

        // Page layout
        return content.map(section => {
            const rows = section.children || [];
            return `<div class="jtb-preview-section">${rows.slice(0, 2).map(row => {
                const cols = (row.attrs?.columns || '1').split(',');
                return `<div class="jtb-preview-row">${cols.map(c =>
                    `<div class="jtb-preview-col jtb-col-${c.replace('/', '_')}"></div>`
                ).join('')}</div>`;
            }).join('')}</div>`;
        }).slice(0, 3).join('');
    }

    // Select layout
    function selectLayout(card) {
        grid.querySelectorAll('.jtb-layout-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        selectedLayout = card.dataset.id;
    }

    // Use layout (apply to builder)
    function useLayout(id) {
        const layout = layouts.find(l => l.id == id);
        if (layout && window.JTB && window.JTB.applyLayout) {
            window.JTB.applyLayout(layout.content);
            // Close modal
            const modal = document.querySelector('.jtb-modal');
            if (modal) modal.remove();
        }
    }

    // Filter buttons
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            renderLayouts(btn.dataset.category);
        });
    });

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Export for external use
    window.JTBLayoutGallery = {
        getSelected: () => selectedLayout ? layouts.find(l => l.id == selectedLayout) : null,
        useSelected: () => selectedLayout && useLayout(selectedLayout)
    };

    // Load on init
    loadLayouts();
})();
</script>
