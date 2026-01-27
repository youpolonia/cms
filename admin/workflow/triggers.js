document.addEventListener('DOMContentLoaded', function() {
    const triggerCards = document.querySelectorAll('.trigger-card');
    const triggerForm = document.getElementById('trigger-form');
    const saveBtn = document.getElementById('save-trigger');
    
    let selectedTrigger = null;
    let triggerData = {};
    
    // Handle trigger card selection
    triggerCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove selected class from all cards
            triggerCards.forEach(c => c.classList.remove('selected'));
            
            // Add selected class to clicked card
            card.classList.add('selected');
            selectedTrigger = card.dataset.type;
            
            // Enable save button
            saveBtn.disabled = false;
            
            // Generate form for selected trigger
            generateTriggerForm(selectedTrigger);
        });
    });
    
    // Generate form based on trigger type
    function generateTriggerForm(triggerType) {
        let formHtml = '';
        
        switch(triggerType) {
            case 'content-published':
                formHtml = `
                    <div class="form-group">
                        <label>Content Type:</label>
                        <select name="content_type" required>
                            <option value="">Select content type</option>
                            <option value="post">Blog Post</option>
                            <option value="page">Page</option>
                            <option value="product">Product</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Trigger Condition:</label>
                        <select name="condition" required>
                            <option value="published">When published</option>
                            <option value="updated">When updated</option>
                            <option value="deleted">When deleted</option>
                        </select>
                    </div>
                `;
                break;
                
            case 'schedule':
                formHtml = `
                    <div class="form-group">
                        <label>Schedule Type:</label>
                        <select name="schedule_type" required>
                            <option value="">Select schedule type</option>
                            <option value="once">Run once</option>
                            <option value="recurring">Recurring</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Start Date/Time:</label>
                        <input type="datetime-local" name="start_datetime" required>
                    </div>
                    <div id="recurring-options" style="display: none;">
                        <div class="form-group">
                            <label>Recurrence Pattern:</label>
                            <select name="recurrence_pattern">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>
                `;
                break;
                
            case 'user-action':
                formHtml = `
                    <div class="form-group">
                        <label>User Action:</label>
                        <select name="user_action" required>
                            <option value="">Select action</option>
                            <option value="login">User login</option>
                            <option value="logout">User logout</option>
                            <option value="registration">New registration</option>
                            <option value="purchase">Purchase completed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>User Role:</label>
                        <select name="user_role">
                            <option value="any">Any role</option>
                            <option value="admin">Administrator</option>
                            <option value="editor">Editor</option>
                            <option value="subscriber">Subscriber</option>
                        </select>
                    </div>
                `;
                break;
        }
        
        triggerForm.innerHTML = formHtml;
        
        // Add event listeners for dynamic form elements
        if (triggerType === 'schedule') {
            const scheduleTypeSelect = document.querySelector('select[name="schedule_type"]');
            scheduleTypeSelect.addEventListener('change', function() {
                const recurringOptions = document.getElementById('recurring-options');
                recurringOptions.style.display = this.value === 'recurring' ? 'block' : 'none';
            });
        }
    }
    
    // Save trigger configuration
    saveBtn.addEventListener('click', function() {
        if (!selectedTrigger) return;
        
        const formData = new FormData(triggerForm);
        triggerData = {
            type: selectedTrigger,
            config: Object.fromEntries(formData)
        };
        
        console.log('Trigger saved:', triggerData);
        alert('Trigger configuration saved successfully');
    });
});