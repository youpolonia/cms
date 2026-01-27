document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('seo-form');
    const contentTextarea = document.getElementById('content');
    const metaTitleInput = document.getElementById('meta_title');
    const metaDescriptionTextarea = document.getElementById('meta_description');
    const generateTitleBtn = document.getElementById('generate-title');
    const generateDescriptionBtn = document.getElementById('generate-description');
    const seoScoreSpan = document.getElementById('seo-score');

    // Handle title generation
    generateTitleBtn.addEventListener('click', function() {
        if (!contentTextarea.value.trim()) {
            alert('Please enter some content first');
            return;
        }

        generateTitleBtn.disabled = true;
        generateTitleBtn.textContent = 'Generating...';

        fetch('/admin/seo/seo-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': form.csrf_token.value
            },
            body: JSON.stringify({
                action: 'generate-from-content',
                content: contentTextarea.value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            metaTitleInput.value = data.title;
            updateSeoScore();
        })
        .catch(error => {
            alert('Error generating title: ' + error.message);
        })
        .finally(() => {
            generateTitleBtn.disabled = false;
            generateTitleBtn.textContent = 'Generate with AI';
        });
    });

    // Handle description generation
    generateDescriptionBtn.addEventListener('click', function() {
        if (!contentTextarea.value.trim()) {
            alert('Please enter some content first');
            return;
        }

        generateDescriptionBtn.disabled = true;
        generateDescriptionBtn.textContent = 'Generating...';

        fetch('/admin/seo/seo-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': form.csrf_token.value
            },
            body: JSON.stringify({
                action: 'generate-from-content',
                content: contentTextarea.value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            metaDescriptionTextarea.value = data.description;
            updateSeoScore();
        })
        .catch(error => {
            alert('Error generating description: ' + error.message);
        })
        .finally(() => {
            generateDescriptionBtn.disabled = false;
            generateDescriptionBtn.textContent = 'Generate with AI';
        });
    });

    // Update SEO score when content changes
    contentTextarea.addEventListener('input', updateSeoScore);

    function updateSeoScore() {
        fetch('/admin/seo/seo-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': form.csrf_token.value
            },
            body: JSON.stringify({
                action: 'analyze',
                content: contentTextarea.value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.error) {
                seoScoreSpan.textContent = data.score || 0;
            }
        });
    }
});