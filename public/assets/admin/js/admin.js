/**
 * Admin Panel JavaScript
 * Core functionality for the admin dashboard
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize admin panel functionality
    initAdminPanel();
});

function initAdminPanel() {
    // Handle logout confirmation
    const logoutForms = document.querySelectorAll('form[action="/logout"]');
    logoutForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to logout?')) {
                e.preventDefault();
            }
        });
    });

    // Initialize tooltips
    initTooltips();

    // Load any dashboard widgets
    loadDashboardWidgets();
}

function initTooltips() {
    // Simple tooltip implementation
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(el => {
        el.addEventListener('mouseenter', showTooltip);
        el.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltipText = this.getAttribute('data-tooltip');
    const tooltip = document.createElement('div');
    tooltip.className = 'admin-tooltip';
    tooltip.textContent = tooltipText;
    
    document.body.appendChild(tooltip);
    
    const rect = this.getBoundingClientRect();
    tooltip.style.left = `${rect.left + rect.width/2 - tooltip.offsetWidth/2}px`;
    tooltip.style.top = `${rect.bottom + 5}px`;
}

function hideTooltip() {
    const tooltip = document.querySelector('.admin-tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

function loadDashboardWidgets() {
    // Placeholder for future dashboard widgets
    console.log('Loading dashboard widgets...');
}