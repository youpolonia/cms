/**
 * Blank Canvas Theme - Minimal JS
 * Only essential functionality - no overhead
 */

document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for anchor links (if needed by TB modules)
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            var targetId = this.getAttribute('href');
            if (targetId !== '#') {
                var target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });
});
