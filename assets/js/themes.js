document.addEventListener('DOMContentLoaded', function() {
    const themePreviews = document.querySelectorAll('.theme-preview');
    
    themePreviews.forEach(preview => {
        const bgColor = preview.dataset.bgColor;
        if (bgColor) {
            preview.style.backgroundColor = bgColor;
        }
    });
});