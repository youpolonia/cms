document.addEventListener('DOMContentLoaded', function() {
    const ruleForm = document.getElementById('rule-form');
    const ruleList = document.getElementById('rule-list');
    const conditionsContainer = document.getElementById('conditions-container');
    const actionsContainer = document.getElementById('actions-container');
    const testResult = document.getElementById('test-result');
    const testData = document.getElementById('test-data');
    
    let currentRuleId = null;

    // Load rule when selected from list
    ruleList.addEventListener('click', function(e) {
        const listItem = e.target.closest('li');
        if (!listItem) return;

        const ruleId = listItem.dataset.id;
        loadRule(ruleId);
    });

    // New rule button
    document.getElementById('new-rule-btn').addEventListener('click', function() {
        resetForm();
        currentRuleId = null;
    });

    // Add condition
    document.getElementById('add-condition').addEventListener('click', function() {
        addConditionField();
    });

    // Add action
    document.getElementById('add-action').addEventListener('click', function() {
        addActionField();
    });

    // Test rule
    document.getElementById('test-rule').addEventListener('click', function() {
        testRule();
    });

    // Delete rule
    document.getElementById('delete-rule').addEventListener('click', function() {
        if (!currentRuleId) return;
        if (!confirm('Are you sure you want to delete this rule?')) return;

        fetch('/admin/personalization/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${currentRuleId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`#rule-list li[data-id="${currentRuleId}"]`).remove();
                resetForm();
            }
        });
    });

    // Form submission
    ruleForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(ruleForm);
        const conditions = [];
        const actions = [];

        // Collect conditions
        document.querySelectorAll('.condition-field').forEach(field => {
            conditions.push({
                field: field.querySelector('.condition-field-name').value,
                operator: field.querySelector('.condition-operator').value,
                value: field.querySelector('.condition-value').value
            });
        });

        // Collect actions
        document.querySelectorAll('.action-field').forEach(field => {
            actions.push({
                type: field.querySelector('.action-type').value,
                value: field.querySelector('.action-value').value
            });
        });

        formData.append('conditions', JSON.stringify(conditions));
        formData.append('actions', JSON.stringify(actions));

        const url = currentRuleId ? '/admin/personalization/update' : '/admin/personalization/create';
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Refresh to show updated rule list
            }
        });
    });

    function loadRule(ruleId) {
        fetch(`/admin/personalization/get/${ruleId}`)
            .then(response => response.json())
            .then(rule => {
                currentRuleId = rule.id;
                document.getElementById('rule-id').value = rule.id;
                document.getElementById('rule-name').value = rule.name;
                document.getElementById('rule-active').checked = rule.is_active;

                // Clear existing fields
                conditionsContainer.innerHTML = '';
                actionsContainer.innerHTML = '';

                // Add conditions
                rule.conditions.forEach(condition => {
                    addConditionField(condition);
                });

                // Add actions
                rule.actions.forEach(action => {
                    addActionField(action);
                });
            });
    }

    function addConditionField(condition = {}) {
        const fieldId = Date.now();
        const field = document.createElement('div');
        field.className = 'condition-field';
        field.innerHTML = `
            <select class="condition-field-name form-control">
                <option value="user_role" ${condition.field === 'user_role' ? 'selected' : ''}>User Role</option>
                <option value="device_type" ${condition.field === 'device_type' ? 'selected' : ''}>Device Type</option>
                <option value="referrer" ${condition.field === 'referrer' ? 'selected' : ''}>Referrer</option>
            </select>
            <select class="condition-operator form-control">
                <option value="equals" ${condition.operator === 'equals' ? 'selected' : ''}>Equals</option>
                <option value="contains" ${condition.operator === 'contains' ? 'selected' : ''}>Contains</option>
                <option value="starts_with" ${condition.operator === 'starts_with' ? 'selected' : ''}>Starts With</option>
            </select>
            <input type="text" class="condition-value form-control" value="${condition.value || ''}">
            <button type="button" class="remove-field btn btn-danger">Remove</button>
        `;
        conditionsContainer.appendChild(field);

        field.querySelector('.remove-field').addEventListener('click', function() {
            field.remove();
        });
    }

    function addActionField(action = {}) {
        const fieldId = Date.now();
        const field = document.createElement('div');
        field.className = 'action-field';
        field.innerHTML = `
            <select class="action-type form-control">
                <option value="show_content" ${action.type === 'show_content' ? 'selected' : ''}>Show Content</option>
                <option value="redirect" ${action.type === 'redirect' ? 'selected' : ''}>Redirect</option>
                <option value="apply_theme" ${action.type === 'apply_theme' ? 'selected' : ''}>Apply Theme</option>
            </select>
            <input type="text" class="action-value form-control" value="${action.value || ''}">
            <button type="button" class="remove-field btn btn-danger">Remove</button>
        `;
        actionsContainer.appendChild(field);

        field.querySelector('.remove-field').addEventListener('click', function() {
            field.remove();
        });
    }

    function testRule() {
        if (!testData.value) {
            testResult.textContent = 'Please enter test data';
            return;
        }

        const conditions = [];
        document.querySelectorAll('.condition-field').forEach(field => {
            conditions.push({
                field: field.querySelector('.condition-field-name').value,
                operator: field.querySelector('.condition-operator').value,
                value: field.querySelector('.condition-value').value
            });
        });

        fetch('/admin/personalization/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                conditions: conditions,
                test_data: JSON.parse(testData.value)
            })
        })
        .then(response => response.json())
        .then(data => {
            testResult.textContent = data.matches ? 
                '✅ Conditions match the test data' : 
                '❌ Conditions do not match the test data';
        })
        .catch(() => {
            testResult.textContent = 'Invalid test data format';
        });
    }

    function resetForm() {
        ruleForm.reset();
        conditionsContainer.innerHTML = '';
        actionsContainer.innerHTML = '';
        testResult.textContent = '';
        testData.value = '';
    }
});