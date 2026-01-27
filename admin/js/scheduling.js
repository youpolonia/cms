/**
 * Content Scheduling UI Component
 */
class ContentScheduler {
    constructor(options) {
        this.contentId = options.contentId;
        this.versionId = options.versionId;
        this.apiBaseUrl = options.apiBaseUrl || '/api/v1/content';
        this.container = document.querySelector(options.containerSelector);
        this.previewData = null;
        
        this.init();
    }

    init() {
        this.renderBaseUI();
        this.bindEvents();
        this.loadVersions();
    }

    renderBaseUI() {
        this.container.innerHTML = `
            <div class="scheduler-container">
                <div class="version-selector">
                    <h3>Select Version</h3>
                    <div class="version-list"></div>
                </div>
                
                <div class="schedule-options">
                    <h3>Schedule Options</h3>
                    <div class="form-group">
                        <label>Publish Date/Time</label>
                        <input type="datetime-local" class="publish-time">
                    </div>
                    
                    <div class="form-group recurrence-option">
                        <label>
                            <input type="checkbox" class="enable-recurrence"> Recurring
                        </label>
                        <div class="recurrence-options" style="display:none">
                            <!-- Will be populated when recurrence is enabled -->
                        </div>
                    </div>
                    
                    <button class="schedule-button">Schedule</button>
                </div>
                
                <div class="conflict-warning" style="display:none"></div>
            </div>
        `;
    }

    bindEvents() {
        // Toggle recurrence options
        this.container.querySelector('.enable-recurrence').addEventListener('change', (e) => {
            const optionsDiv = this.container.querySelector('.recurrence-options');
            optionsDiv.style.display = e.target.checked ? 'block' : 'none';
            
            if (e.target.checked) {
                this.renderRecurrenceOptions();
            }
        });

        // Schedule button
        this.container.querySelector('.schedule-button').addEventListener('click', () => {
            this.scheduleContent();
        });
    }

    renderRecurrenceOptions() {
        const optionsDiv = this.container.querySelector('.recurrence-options');
        optionsDiv.innerHTML = `
            <div class="form-group">
                <label>Recurrence Pattern</label>
                <select class="recurrence-pattern">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
            <div class="form-group">
                <label>Interval</label>
                <input type="number" class="recurrence-interval" min="1" value="1">
            </div>
            <div class="form-group custom-weekly" style="display:none">
                <label>Days of Week</label>
                <div class="day-checkboxes">
                    <label><input type="checkbox" value="MO"> Mon</label>
                    <label><input type="checkbox" value="TU"> Tue</label>
                    <label><input type="checkbox" value="WE"> Wed</label>
                    <label><input type="checkbox" value="TH"> Thu</label>
                    <label><input type="checkbox" value="FR"> Fri</label>
                    <label><input type="checkbox" value="SA"> Sat</label>
                    <label><input type="checkbox" value="SU"> Sun</label>
                </div>
            </div>
            <div class="form-group custom-monthly" style="display:none">
                <label>Day of Month</label>
                <input type="number" class="recurrence-bymonthday" min="1" max="31" value="1">
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input type="date" class="recurrence-end">
            </div>
            <button class="preview-button">Preview Schedule</button>
            <div class="preview-results" style="display:none"></div>
        `;
        
        // Bind pattern change event
        this.container.querySelector('.recurrence-pattern').addEventListener('change', (e) => {
            this.toggleCustomOptions(e.target.value);
        });
        
        // Bind preview button
        this.container.querySelector('.preview-button').addEventListener('click', () => {
            this.generatePreview();
        });
    }
    
    toggleCustomOptions(pattern) {
        this.container.querySelector('.custom-weekly').style.display =
            pattern === 'weekly' ? 'block' : 'none';
        this.container.querySelector('.custom-monthly').style.display =
            pattern === 'monthly' ? 'block' : 'none';
    }
    
    async generatePreview() {
        const pattern = this.container.querySelector('.recurrence-pattern').value;
        const interval = this.container.querySelector('.recurrence-interval').value;
        const endDate = this.container.querySelector('.recurrence-end').value;
        
        let byday = '';
        if (pattern === 'weekly') {
            const checkedDays = Array.from(
                this.container.querySelectorAll('.day-checkboxes input:checked')
            ).map(el => el.value);
            byday = checkedDays.join(',');
        }
        
        const bymonthday = pattern === 'monthly' ?
            this.container.querySelector('.recurrence-bymonthday').value : null;
            
        try {
            const response = await fetch(`${this.apiBaseUrl}/schedule/preview`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    start_date: this.container.querySelector('.publish-time').value,
                    recurrence_pattern: pattern,
                    recurrence_interval: interval,
                    recurrence_end_date: endDate,
                    recurrence_byday: byday,
                    recurrence_bymonthday: bymonthday
                })
            });
            
            const result = await response.json();
            if (result.success) {
                this.previewData = result.data;
                this.showPreview(result.data);
            } else {
                alert('Preview error: ' + (result.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Preview error:', error);
            alert('Failed to generate preview');
        }
    }
    
    showPreview(events) {
        const previewDiv = this.container.querySelector('.preview-results');
        previewDiv.style.display = 'block';
        previewDiv.innerHTML = `
            <h4>Preview (${events.length} events)</h4>
            <ul class="preview-list">
                ${events.slice(0, 10).map(event => `
                    <li>${new Date(event.scheduled_at).toLocaleString()}</li>
                `).join('')}
                ${events.length > 10 ? `<li>...and ${events.length - 10} more</li>` : ''}
            </ul>
        `;
    }

    async loadVersions() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/${this.contentId}/versions`);
            const versions = await response.json();
            
            const versionList = this.container.querySelector('.version-list');
            versionList.innerHTML = versions.data.map(version => `
                <div class="version-item" data-version-id="${version.id}">
                    <input type="radio" name="version" id="version-${version.id}" 
                           ${version.id === this.versionId ? 'checked' : ''}>
                    <label for="version-${version.id}">
                        Version #${version.id} - ${version.created_at}
                    </label>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading versions:', error);
        }
    }

    async scheduleContent() {
        const publishTime = this.container.querySelector('.publish-time').value;
        const enableRecurrence = this.container.querySelector('.enable-recurrence').checked;
        const selectedVersion = this.container.querySelector('input[name="version"]:checked');
        
        if (!selectedVersion || !publishTime) {
            alert('Please select a version and publish time');
            return;
        }

        const versionId = selectedVersion.dataset.versionId;
        const scheduleData = {
            content_id: this.contentId,
            version_id: versionId,
            publish_at: publishTime,
            is_recurring: enableRecurrence ? 1 : 0
        };

        if (enableRecurrence) {
            const pattern = this.container.querySelector('.recurrence-pattern').value;
            scheduleData.recurrence_pattern = pattern;
            scheduleData.recurrence_interval = this.container.querySelector('.recurrence-interval').value;
            scheduleData.recurrence_end_date = this.container.querySelector('.recurrence-end').value;
            
            if (pattern === 'weekly') {
                const checkedDays = Array.from(
                    this.container.querySelectorAll('.day-checkboxes input:checked')
                ).map(el => el.value);
                scheduleData.recurrence_byday = checkedDays.join(',');
            } else if (pattern === 'monthly') {
                scheduleData.recurrence_bymonthday = this.container.querySelector('.recurrence-bymonthday').value;
            }
            
            // Use preview data if available
            if (this.previewData) {
                scheduleData.recurrence_events = this.previewData;
            }
        }

        try {
            const response = await fetch(`${this.apiBaseUrl}/schedule`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(scheduleData)
            });

            const result = await response.json();
            
            if (result.success) {
                alert('Content scheduled successfully!');
            } else if (result.data?.conflicts) {
                this.showConflicts(result.data.conflicts);
            } else {
                alert('Error scheduling content: ' + (result.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error scheduling content:', error);
            alert('Failed to schedule content');
        }
    }

    showConflicts(conflicts) {
        const warningDiv = this.container.querySelector('.conflict-warning');
        warningDiv.style.display = 'block';
        warningDiv.innerHTML = `
            <h4>Scheduling Conflicts Detected</h4>
            <ul>
                ${conflicts.map(conflict => `
                    <li>
                        ${conflict.type}: ${conflict.message}
                        ${conflict.resolution_suggestion ? ` (${conflict.resolution_suggestion})` : ''}
                    </li>
                `).join('')}
            </ul>
        `;
    }
}

// Initialize scheduler when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const scheduler = new ContentScheduler({
        containerSelector: '#scheduler-container',
        contentId: window.currentContentId,
        versionId: window.currentVersionId
    });
});