// Theme-specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Theme initialization code
    console.log('Default theme initialized');
    
    // Example theme functionality
    document.querySelectorAll('.btn-primary').forEach(btn => {
        btn.addEventListener('click', function(e) {
            console.log('Primary button clicked');
        });
    });
});

// Export theme functions if needed
export function themeFunction() {
    return 'Theme function executed';
}
