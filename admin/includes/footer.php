    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('theme-toggle');
        var icon = document.querySelector('.theme-icon');
        
        var currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
        if (icon) {
            icon.textContent = currentTheme === 'dark' ? '🌙' : '☀️';
        }
        
        if (btn) {
            btn.addEventListener('click', function() {
                var html = document.documentElement;
                var current = html.getAttribute('data-theme') || 'dark';
                var next = current === 'dark' ? 'light' : 'dark';
                
                html.setAttribute('data-theme', next);
                localStorage.setItem('cms-theme', next);
                
                if (icon) {
                    icon.textContent = next === 'dark' ? '🌙' : '☀️';
                }
            });
        }
    });
    </script>
<?php
// Floating AI Assistant Widget — available on all admin pages
if (file_exists(CMS_ROOT . '/admin/includes/ai-assistant-widget.php')) {
    require_once CMS_ROOT . '/admin/includes/ai-assistant-widget.php';
}
?>
</body>
</html>
