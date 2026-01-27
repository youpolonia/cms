/**
 * Batch Processing Manager UI Component
 */
class BatchManager {
    constructor(options) {
        this.apiBaseUrl = options.apiBaseUrl || '/api/v1/batch-schedule';
        this.container = document.querySelector(options.containerSelector);
        this.pollInterval = options.pollInterval || 5000;
        this.pollTimer = null;
        
        this.init();
    }

    init() {
        this.renderBaseUI();
        this.bindEvents();
        this.loadBatches();
    }

    renderBaseUI() {
        this.container.innerHTML = `
            <div class="batch-manager-container">
                <div class="batch-list-header">
                    <h2>Batch Processing</h2>
                    <button class="refresh-button">Refresh</button>
                </div>
                
                <div class="batch-list"></div>
                
                <div class="batch-details" style="display:none">
                    <h3>Batch Details</h3>
                    <div class="details-content"></div>
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                        <span class="progress-text">0%</span>
                    </div>
                    <button class="cancel-button">Cancel Batch</button>
                </div>
            </div>
        `;
    }

    bindEvents() {
        // Refresh button
        this.container.querySelector('.refresh-button').addEventListener('click', () => {
            this.loadBatches();
        });

        // Cancel button
        this.container.querySelector('.cancel-button').addEventListener('click', () => {
            this.cancelBatch();
        });
    }

    async loadBatches() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/status`);
            const batches = await response.json();
            
            const batchList = this.container.querySelector('.batch-list');
            batchList.innerHTML = batches.data.map(batch => `
                <div class="batch-item" data-batch-id="${batch.id}">
                    <div class="batch-info">
                        <h4>Batch #${batch.id}</h4>
                        <p>Status: ${batch.status}</p>
                        <p>Progress: ${batch.progress}%</p>
                        <p>Created: ${new Date(batch.created_at).toLocaleString()}</p>
                    </div>
                    <button class="view-details">Details</button>
                </div>
            `).join('');

            // Add event listeners for details buttons
            batchList.querySelectorAll('.view-details').forEach(button => {
                button.addEventListener('click', (e) => {
                    const batchId = e.target.closest('.batch-item').dataset.batchId;
                    this.showBatchDetails(batchId);
                });
            });

        } catch (error) {
            console.error('Error loading batches:', error);
        }
    }

    async showBatchDetails(batchId) {
        try {
            const response = await fetch(`${this.apiBaseUrl}/progress?batch_id=${batchId}`);
            const batch = await response.json();
            
            const detailsDiv = this.container.querySelector('.batch-details');
            detailsDiv.style.display = 'block';
            
            // Update details content
            detailsDiv.querySelector('.details-content').innerHTML = `
                <p><strong>ID:</strong> ${batch.id}</p>
                <p><strong>Status:</strong> ${batch.status}</p>
                <p><strong>Progress:</strong> ${batch.progress}%</p>
                <p><strong>Total Items:</strong> ${batch.total_items}</p>
                <p><strong>Processed:</strong> ${batch.processed_items}</p>
                <p><strong>Errors:</strong> ${batch.error_count}</p>
                <p><strong>Created:</strong> ${new Date(batch.created_at).toLocaleString()}</p>
                ${batch.last_error ? `<p><strong>Last Error:</strong> ${batch.last_error}</p>` : ''}
            `;
            
            // Update progress bar
            const progressFill = detailsDiv.querySelector('.progress-fill');
            const progressText = detailsDiv.querySelector('.progress-text');
            progressFill.style.width = `${batch.progress}%`;
            progressText.textContent = `${batch.progress}%`;
            
            // Store current batch ID
            this.currentBatchId = batchId;
            
            // Start polling if not already running
            if (!this.pollTimer) {
                this.startPolling();
            }
            
        } catch (error) {
            console.error('Error loading batch details:', error);
        }
    }

    startPolling() {
        this.pollTimer = setInterval(() => {
            this.updateBatchProgress();
        }, this.pollInterval);
    }

    stopPolling() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }
    }

    async updateBatchProgress() {
        if (!this.currentBatchId) return;
        
        try {
            const response = await fetch(`${this.apiBaseUrl}/progress?batch_id=${this.currentBatchId}`);
            const batch = await response.json();
            
            // Update progress display
            const progressFill = this.container.querySelector('.progress-fill');
            const progressText = this.container.querySelector('.progress-text');
            progressFill.style.width = `${batch.progress}%`;
            progressText.textContent = `${batch.progress}%`;
            
            // Update status in details
            const statusElement = this.container.querySelector('.details-content p:nth-child(2)');
            if (statusElement) {
                statusElement.innerHTML = `<strong>Status:</strong> ${batch.status}`;
            }
            
            // Stop polling if batch is complete
            if (batch.status === 'completed' || batch.status === 'failed' || batch.status === 'cancelled') {
                this.stopPolling();
            }
            
        } catch (error) {
            console.error('Error updating batch progress:', error);
            this.stopPolling();
        }
    }

    async cancelBatch() {
        if (!this.currentBatchId) return;
        
        try {
            const response = await fetch(`${this.apiBaseUrl}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    batch_id: this.currentBatchId
                })
            });
            
            const result = await response.json();
            if (result.success) {
                alert('Batch cancellation requested');
                this.stopPolling();
                this.loadBatches();
            } else {
                alert('Error cancelling batch: ' + (result.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error cancelling batch:', error);
            alert('Failed to cancel batch');
        }
    }
}

// Initialize batch manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const batchManager = new BatchManager({
        containerSelector: '#batch-manager-container'
    });
});