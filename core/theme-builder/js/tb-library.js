/**
 * Theme Builder 3.0 - Layout Library Functions
 * Handles loading layouts from library and inserting sections
 */

Object.assign(window.TB, {
    // Library state
    libraryLayouts: [],
    selectedLayout: null,
    selectedPageIndex: 0,

    openLibrary() {
        document.getElementById('tb-library-modal').classList.add('active');
        this.loadLibraryLayouts();
    },

    closeLibrary() {
        document.getElementById('tb-library-modal').classList.remove('active');
        this.selectedLayout = null;
        this.selectedPageIndex = 0;
        document.getElementById('tb-library-pages').style.display = 'none';
        document.getElementById('tb-library-insert-btn').disabled = true;
    },

    loadLibraryLayouts() {
        const body = document.getElementById('tb-library-body');
        const search = document.getElementById('tb-library-search')?.value || '';
        const category = document.getElementById('tb-library-category')?.value || '';

        body.innerHTML = '<div class="tb-library-loading"><div class="tb-spinner"></div><p>Loading layouts...</p></div>';

        let url = '/admin/layout-library/list?';
        if (search) url += 'search=' + encodeURIComponent(search) + '&';
        if (category) url += 'category=' + encodeURIComponent(category);

        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.layouts) {
                    this.libraryLayouts = data.layouts;
                    this.renderLibraryGrid(data.layouts);
                } else {
                    body.innerHTML = '<div class="tb-library-empty"><p style="font-size:2rem;margin-bottom:0.5rem;">📭</p><p>' + (data.error || 'No layouts found') + '</p></div>';
                }
            })
            .catch(err => {
                body.innerHTML = '<div class="tb-library-empty"><p style="font-size:2rem;margin-bottom:0.5rem;">⚠️</p><p>Failed to load layouts: ' + err.message + '</p></div>';
            });
    },

    renderLibraryGrid(layouts) {
        const body = document.getElementById('tb-library-body');

        if (!layouts || layouts.length === 0) {
            body.innerHTML = '<div class="tb-library-empty"><p style="font-size:2rem;margin-bottom:0.5rem;">📭</p><p>No layouts found.</p></div>';
            return;
        }

        let html = '<div class="tb-library-grid">';
        layouts.forEach(layout => {
            const thumbnail = layout.thumbnail
                ? '<img src="' + this.escapeHtml(layout.thumbnail) + '" alt="">'
                : '📄';
            const badge = layout.is_ai_generated ? '<span class="tb-library-badge">✨ AI</span>' : '';

            html += '<div class="tb-library-card" data-id="' + layout.id + '" data-pages="' + (layout.page_count || 1) + '" onclick="TB.selectLayout(this)">';
            html += '<div class="tb-library-thumbnail">' + thumbnail + '</div>';
            html += '<div class="tb-library-info">';
            html += '<div class="tb-library-name">' + this.escapeHtml(layout.name) + '</div>';
            html += '<div class="tb-library-meta">';
            html += '<span>📄 ' + (layout.page_count || 1) + ' pages</span>';
            if (layout.category) html += '<span>📁 ' + this.escapeHtml(layout.category) + '</span>';
            html += badge;
            html += '</div></div></div>';
        });
        html += '</div>';

        body.innerHTML = html;
    },

    selectLayout(card) {
        document.querySelectorAll('.tb-library-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');

        const layoutId = parseInt(card.dataset.id);
        const pageCount = parseInt(card.dataset.pages) || 1;

        this.selectedLayout = this.libraryLayouts.find(l => l.id == layoutId);
        this.selectedPageIndex = 0;

        const pagesDiv = document.getElementById('tb-library-pages');
        const pagesList = document.getElementById('tb-library-pages-list');

        if (pageCount > 1) {
            pagesList.innerHTML = '';
            for (let i = 0; i < pageCount; i++) {
                const pageNames = ['Home', 'About', 'Services', 'Contact', 'Gallery', 'Blog', 'FAQ', 'Pricing', 'Team', 'Portfolio'];
                const pageName = i < pageNames.length ? pageNames[i] : 'Page ' + (i + 1);
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'tb-library-page-btn' + (i === 0 ? ' active' : '');
                btn.textContent = pageName;
                btn.onclick = () => this.selectPage(i);
                pagesList.appendChild(btn);
            }
            pagesDiv.style.display = 'block';
        } else {
            pagesDiv.style.display = 'none';
        }

        document.getElementById('tb-library-insert-btn').disabled = false;
    },

    selectPage(index) {
        this.selectedPageIndex = index;
        document.querySelectorAll('.tb-library-page-btn').forEach((btn, i) => {
            btn.classList.toggle('active', i === index);
        });
    },

    insertFromLibrary() {
        if (!this.selectedLayout) {
            this.showToast('Please select a layout first', 'warning');
            return;
        }

        const insertBtn = document.getElementById('tb-library-insert-btn');
        insertBtn.disabled = true;
        insertBtn.textContent = 'Loading...';

        const insertModeEl = document.querySelector('input[name="insert-mode"]:checked');
        const insertMode = insertModeEl ? insertModeEl.value : 'append';

        fetch('/admin/layout-library/get-sections?id=' + this.selectedLayout.id + '&page_index=' + this.selectedPageIndex)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.sections) {
                    if (insertMode === 'replace') {
                        this.content.sections = data.sections;
                    } else {
                        if (!this.content.sections) this.content.sections = [];
                        this.content.sections.push(...data.sections);
                    }

                    this.renderCanvas();
                    this.saveToHistory();
                    this.closeLibrary();
                    this.showToast(data.section_count + ' sections inserted from "' + data.page_title + '"', 'success');
                } else {
                    this.showToast(data.error || 'Failed to load sections', 'error');
                    insertBtn.disabled = false;
                    insertBtn.textContent = 'Insert Sections';
                }
            })
            .catch(err => {
                this.showToast('Error: ' + err.message, 'error');
                insertBtn.disabled = false;
                insertBtn.textContent = 'Insert Sections';
            });
    }
});

console.log('[TB] Library module loaded');
