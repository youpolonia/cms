/**
 * Default Theme JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Default theme loaded');
    
    // Basic theme functionality
    const navLinks = document.querySelectorAll('nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            console.log('Navigating to:', this.href);
        });
    });
});