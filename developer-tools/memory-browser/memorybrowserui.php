<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
require_once __DIR__ . '/memorybrowser.php';

class MemoryBrowserUI {
    private MemoryBrowser $browser;

    public function __construct() {
        $this->browser = new MemoryBrowser();
    }

    public function render(): string {
        $html = '
<div class="memory-browser">
            <div class="search-box">
                <input type="text" id="memory-search" placeholder="Search memory-bank...">
                <button id="search-btn">Search</button>
            </div>
            <div class="file-list">';

        $files = $this->browser->listFiles();
        foreach ($files as $file) {
            $html .= '
<div class="file-item" data-file="'.htmlspecialchars(
$file).'">
                <span class="file-name">'.htmlspecialchars(basename(
$file)).'</span>
                <span class="file-path">'.htmlspecialchars(dirname(
$file)).'</span>
                <button class="view-btn">View</button>
                <button class="export-btn">Export</button>
            </div>';
        }

        $html .= '
</div>
            <div class="file-viewer">
                <pre id="file-content"></pre>
            </div>
        </div>';

        return $html;
    }

    public function renderScripts(): string {
        return '
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // File selection
                document.querySelectorAll(".file-item").forEach(item => {
                    item.addEventListener("click", function(e) {
                        if (e.target.classList.contains("view-btn")) {
                            const file = this.dataset.file;
                            fetch("/developer-tools/memory-browser/view?file=" + encodeURIComponent(file))
                                .then(response => response.text())
                                .then(content => {
                                    document.getElementById("file-content").textContent = content;
                                    hljs.highlightAll();
                                });
                        } else if (e.target.classList.contains("export-btn")) {
                            const file = this.dataset.file;
                            window.location.href = "/developer-tools/memory-browser/export?file=" + encodeURIComponent(file);
                        }
                    });
                });

                // Search functionality
                document.getElementById("search-btn").addEventListener("click", function() {
                    const query = document.getElementById("memory-search").value;
                    fetch("/developer-tools/memory-browser/search?q=" + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(results => {
                            // Update file list with search results
                        });
                });
            });
        </script>';
    }
}
