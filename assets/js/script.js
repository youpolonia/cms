// Load JSON Editor from CDN
const jsonEditorScript = document.createElement('script');
jsonEditorScript.src = 'https://cdn.jsdelivr.net/npm/jsoneditor@9.5.6/dist/jsoneditor.min.js';
document.head.appendChild(jsonEditorScript);

const jsonEditorStyle = document.createElement('link');
jsonEditorStyle.rel = 'stylesheet';
jsonEditorStyle.href = 'https://cdn.jsdelivr.net/npm/jsoneditor@9.5.6/dist/jsoneditor.min.css';
document.head.appendChild(jsonEditorStyle);

// Bulk Plugin Operations
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('input[name="plugin_ids[]"]');
    const bulkSubmitBtn = document.getElementById('bulkSubmitBtn');
    const bulkActionSelect = document.querySelector('select[name="bulk_action"]');
    const form = document.getElementById('bulkPluginForm');

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        updateSubmitButton();
    });

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSubmitButton();
            if (!this.checked) {
                selectAll.checked = false;
            }
        });
    });

    function updateSubmitButton() {
        const checkedCount = document.querySelectorAll('input[name="plugin_ids[]"]:checked').length;
        bulkSubmitBtn.disabled = checkedCount === 0 || !bulkActionSelect.value;
    }

    bulkActionSelect.addEventListener('change', updateSubmitButton);

    form.addEventListener('submit', function(e) {
        const action = bulkActionSelect.value;
        const checkedCount = document.querySelectorAll('input[name="plugin_ids[]"]:checked').length;

        if (action === 'delete') {
            if (!confirm(`Are you sure you want to delete ${checkedCount} plugin(s)? This cannot be undone.`)) {
                e.preventDefault();
                return false;
            }
        } else if (action === 'deactivate') {
            if (!confirm(`Are you sure you want to deactivate ${checkedCount} plugin(s)?`)) {
                e.preventDefault();
                return false;
            }
        }

        return true;
    });

    // Error handling - check for error message in data attribute
    const errorMessage = form.dataset.error;
    if (errorMessage) {
        alert(`Error: ${errorMessage}`);
    }
});

// Custom Fields JSON Editor
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('type').addEventListener('change', function() {
        const needsOptions = ['select', 'radio', 'checkbox'].includes(this.value);
        document.getElementById('options-container').style.display = needsOptions ? 'block' : 'none';
        
        if (needsOptions) {
            if (!window.editor) {
                const container = document.getElementById('jsoneditor');
                window.editor = new JSONEditor(container, {
                    mode: 'code',
                    modes: ['code', 'tree'],
                    onError: function(err) {
                        alert(err.toString());
                    },
                    onChange: function() {
                        try {
                            const json = editor.get();
                            document.getElementById('options').value = JSON.stringify(json);
                        } catch (e) {
                            console.error(e);
                        }
                    }
                });
                editor.set({ options: [] });
            }
        }
    });
});

// Generic confirmation handler
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-confirm]').forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
});