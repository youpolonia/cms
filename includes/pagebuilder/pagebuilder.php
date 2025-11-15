<?php
/**
 * Pure PHP Page Builder for CMS
 * Implements WYSIWYG functionality without framework dependencies
 */
class PageBuilder {
    private $db;
    private $content;
    private $editorId;
    
    // Constants for API responses
    const COMPONENT_ID = 'componentId';
    const RESPONSE = 'response';
    const STATUS = 'status';
    const TEXT = 'text';
    const STATUS_TEXT = 'statusText';

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->editorId = 'wysiwyg-editor-' . uniqid();
    }

    /**
     * Load page content from database
     */
    public function loadPage($pageId) {
        $stmt = $this->db->prepare("SELECT content FROM pages WHERE id = ?");
        $stmt->execute([$pageId]);
        $this->content = $stmt->fetchColumn();
        return $this;
    }

    /**
     * Save page content to database
     */
    public function savePage($pageId, $content) {
        $stmt = $this->db->prepare(
            "INSERT INTO pages (id, content) VALUES (?, ?) 
             ON DUPLICATE KEY UPDATE content = VALUES(content)"
        );
        return $stmt->execute([$pageId, $content]);
    }

    /**
     * Render editor HTML
     */
    public function renderEditor() {
        $component = new Component($this->db);
        $components = $component->fetchComponents();
        $componentHTML = $component->renderComponentPalette($components);
        
        return <<<HTML
        <div class="page-builder">
            <div class="component-palette">
{$componentHTML}</div>
            <div class="editor-area" id="editor-area-{$this->editorId}">
                <textarea id="{$this->editorId}" name="page_content">{$this->content}</textarea>
                <div class="toolbar">
                    <button type="button" data-command="bold">Bold</button>
                    <button type="button" data-command="italic">Italic</button>
                    <button type="button" class="ai-suggest" data-editor="{$this->editorId}"
                        data-api-url="/api/components/get?id=">AI Suggestions
</button>
                </div>
            </div>
        </div>
        <!-- Include DOMPurify library for HTML sanitization -->
        <!-- <script src="path/to/dompurify.min.js"></script> -->
        <style>
            .ai-suggestions-modal {
                position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
                background: white; border: 1px solid #ccc; padding: 20px; z-index: 1000;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 8px; width: 400px;
            }
            .ai-suggestions-modal h4 { margin-top: 0; }
            .ai-suggestions-modal ul { list-style: none; padding: 0; }
            .ai-suggestions-modal li { padding: 8px; border-bottom: 1px solid #eee; cursor: pointer; }
            .ai-suggestions-modal li:hover { background-color: #f0f0f0; }
            .ai-suggestions-modal .close-btn { float: right; cursor: pointer; }
        </style>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editor = document.getElementById('{$this->editorId}');
            const editorArea = document.getElementById('editor-area-{$this->editorId}');

            document.querySelectorAll('.toolbar button:not(.ai-suggest)').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.execCommand(this.dataset.command, false, null);
                    editor.focus();
                });
            });

            document.querySelectorAll('.component').forEach(comp => {
                comp.addEventListener('dragstart', handleDragStart);
            });

            editorArea.addEventListener('dragover', handleDragOver);
            editorArea.addEventListener('drop', handleDrop);

            document.querySelector('.ai-suggest').addEventListener('click', function() {
                const selectedText = editor.value.substring(editor.selectionStart, editor.selectionEnd);
                if (selectedText.trim() === "") {
                    alert("Please select some text to get AI suggestions.");
                    return;
                }
                fetchAISuggestions(selectedText);
            });

            function handleDragStart(e) {
                e.dataTransfer.setData('text/plain', e.target.dataset.componentId);
            }

            function handleDragOver(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
            }

            function handleDrop(e) {
                e.preventDefault();
                const componentId = e.dataTransfer.getData('text/plain');
                if (componentId) {
                    insertComponent(componentId, e.clientX, e.clientY);
                }
            }

            function insertComponent(componentId, x, y) {
                const btn = document.querySelector('.ai-suggest');
                fetch(btn.dataset.apiUrl + encodeURIComponent(componentId))
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => { throw new Error('Component fetch error: ' + response.status + ' ' + (text || response.statusText))});
                        }
                        return response.text();
                    })
                    .then(html => {
                        const range = document.caretRangeFromPoint(x, y);
                        if (!range) {
                           editor.focus();
                           const selection = window.getSelection();
                           if (selection.rangeCount > 0) {
                               range = selection.getRangeAt(0);
                           } else {
                               console.error("Cannot determine insertion point.");
                               return;
                           }
                        }
                        const node = document.createElement('div');
                        node.innerHTML = typeof DOMPurify !== 'undefined' ? DOMPurify.sanitize(html) : html;
                        range.insertNode(node);
                        editor.focus();
                    })
                    .catch(error => {
                        console.error('Error inserting component:', error);
                        alert('Failed to insert component: ' + error.message);
                    });
            }

            function fetchAISuggestions(prompt) {
                const loadingModal = showLoadingModal("Fetching AI suggestions...");
                fetch('/api/ai/suggest-content', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({prompt: prompt})
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.error || 'AI API Error: ' + response.status) });
                    }
                    return response.json();
                })
                .then(data => {
                    loadingModal.remove();
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    showAISuggestionsModal(data.suggestions || []);
                })
                .catch(error => {
                    loadingModal.remove();
                    console.error('Error fetching AI suggestions:', error);
                    alert('Failed to fetch AI suggestions: ' + error.message);
                });
            }

            function showLoadingModal(message) {
                message = message || '';
                let modal = document.getElementById('loading-modal-pb');
                if (!modal) {
                    modal = document.createElement('div');
                    modal.id = 'loading-modal-pb';
                    modal.className = 'ai-suggestions-modal';
                    modal.style.textAlign = 'center';
                    document.body.appendChild(modal);
                }
                modal.innerHTML = '<h4>' + message + '</h4><p>Please wait...</p>';
                return modal;
            }

            function showAISuggestionsModal(suggestions) {
                let modal = document.getElementById('ai-suggestions-modal-pb');
                if (modal) modal.remove();

                modal = document.createElement('div');
                modal.id = 'ai-suggestions-modal-pb';
                modal.className = 'ai-suggestions-modal';

                const closeBtn = document.createElement('span');
                closeBtn.className = 'close-btn';
                closeBtn.innerHTML = '&times;';
                closeBtn.onclick = () => modal.remove();
                modal.appendChild(closeBtn);

                const title = document.createElement('h4');
                title.textContent = 'AI Content Suggestions';
                modal.appendChild(title);

                if (suggestions.length === 0) {
                    const noSuggestions = document.createElement('p');
                    noSuggestions.textContent = 'No suggestions available at the moment.';
                    modal.appendChild(noSuggestions);
                } else {
                    const ul = document.createElement('ul');
                    suggestions.forEach(suggestionText => {
                        const li = document.createElement('li');
                        li.textContent = suggestionText;
                        li.onclick = () => {
                            const start = editor.selectionStart;
                            const end = editor.selectionEnd;
                            editor.value = editor.value.substring(0, start) + suggestionText + editor.value.substring(end);
                            editor.focus();
                            editor.selectionStart = start + suggestionText.length;
                            editor.selectionEnd = start + suggestionText.length;
                            modal.remove();
                        };
                        ul.appendChild(li);
                    });
                    modal.appendChild(ul);
                }
                document.body.appendChild(modal);
            }
        });
        </script>
HTML;
    }
}
