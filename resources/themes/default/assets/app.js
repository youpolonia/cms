document.addEventListener('DOMContentLoaded', function() {
    console.log('Default theme loaded');
    
    // Theme switching handler
    document.querySelectorAll('[data-theme-switch]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const theme = this.dataset.themeSwitch;
            fetch(`/themes/${theme}/switch`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).then(response => {
                if (response.ok) {
                    window.location.reload();
                }
            });
        });
    });
});