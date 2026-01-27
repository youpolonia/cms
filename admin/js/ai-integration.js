// AI Integration JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Template definitions (matches server-side templates)
    const templates = {
        blog_post: {
            variables: ['topic', 'tone', 'audience'],
            labels: {
                topic: 'Blog Topic',
                tone: 'Writing Tone',
                audience: 'Target Audience'
            }
        },
        product_description: {
            variables: ['product_name', 'feature1', 'feature2', 'feature3', 'style'],
            labels: {
                product_name: 'Product Name',
                feature1: 'Feature 1',
                feature2: 'Feature 2', 
                feature3: 'Feature 3',
                style: 'Writing Style'
            }
        },
        seo_meta: {
            variables: ['topic', 'keywords'],
            labels: {
                topic: 'Page Topic',
                keywords: 'Target Keywords'
            }
        }
    };

    // DOM elements
    const templateSelect = document.getElementById('template-select');
    const variablesContainer = document.getElementById('template-variables');
    const contentForm = document.getElementById('content-generation-form');
    const testForms = document.querySelectorAll('.test-form');

    // Initialize template variables when page loads
    updateTemplateVariables();

    // Update variables when template changes
    templateSelect.addEventListener('change', updateTemplateVariables);

    // Handle form submissions with fetch API
    testForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const action = formData.get('action');
            const button = this.querySelector('button[type="submit"]');
            const originalButtonText = button.textContent;

            // Show loading state
            button.disabled = true;
            button.textContent = 'Processing...';

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                // Parse HTML response
                const text = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(text, 'text/html');
                
                // Find notice message
                const notice = doc.querySelector('.notice');
                if (notice) {
                    showNotice(notice.textContent, 'success');
                }

                // For content generation, show results
                if (action === 'generate_test_content') {
                    const generatedContent = doc.querySelector('.generated-content');
                    if (generatedContent) {
                        // Remove existing results if any
                        const existingResults = document.querySelector('.generated-content');
                        if (existingResults) {
                            existingResults.remove();
                        }
                        
                        // Insert new results after form
                        this.insertAdjacentHTML('afterend', generatedContent.outerHTML);
                    }
                }
            } catch (error) {
                showNotice(error.message, 'error');
                console.error('Error:', error);
            } finally {
                // Restore button state
                button.disabled = false;
                button.textContent = originalButtonText;
            }
        });
    });

    // Update template variables UI based on selected template
    function updateTemplateVariables() {
        const selectedTemplate = templateSelect.value;
        const template = templates[selectedTemplate];
        
        // Clear existing variables
        variablesContainer.innerHTML = '';

        if (!template) return;

        // Create input for each variable
        template.variables.forEach(variable => {
            const group = document.createElement('div');
            group.className = 'form-group';

            const label = document.createElement('label');
            label.textContent = template.labels[variable] || variable;
            label.htmlFor = `var-${variable}`;

            const input = document.createElement('input');
            input.type = 'text';
            input.id = `var-${variable}`;
            input.name = `variables[${variable}]`;
            input.required = true;

            group.appendChild(label);
            group.appendChild(input);
            variablesContainer.appendChild(group);
        });

        // Add hidden input with JSON representation of variables
        const jsonInput = document.createElement('input');
        jsonInput.type = 'hidden';
        jsonInput.name = 'variables';
        jsonInput.value = JSON.stringify(template.variables);
        variablesContainer.appendChild(jsonInput);
    }

    // Show notice message
    function showNotice(message, type = 'info') {
        // Remove existing notices
        const existingNotices = document.querySelectorAll('.notice');
        existingNotices.forEach(notice => notice.remove());

        // Create new notice
        const notice = document.createElement('div');
        notice.className = `notice notice-${type}`;
        notice.textContent = message;

        // Insert at top of content
        const content = document.querySelector('body');
        if (content) {
            content.insertBefore(notice, content.firstChild);
        }

        // Auto-hide after 5 seconds
        setTimeout(() => {
            notice.remove();
        }, 5000);
    }
});