class BatchSchedulerUI {
    constructor() {
        this.initEventListeners();
        this.loadBatches();
    }

    initEventListeners() {
        document.getElementById('create-batch-btn').addEventListener('click', () => this.showCreateModal());
        document.getElementById('batch-form').addEventListener('submit', (e) => this.handleBatchSubmit(e));
        document.getElementById('refresh-batches').addEventListener('click', () => this.loadBatches());
    }

    showCreateModal() {
        document.getElementById('batch-modal').classList.remove('hidden');
    }

    async handleBatchSubmit(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('/api/v1/batch-schedule', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) throw new Error(await response.text());
            this.loadBatches();
            this.closeModal();
        } catch (error) {
            console.error('Batch creation failed:', error);
            alert('Batch creation failed: ' + error.message);
        }
    }

    async loadBatches() {
        try {
            const response = await fetch('/api/v1/batch-schedule/status');
            const batches = await response.json();
            this.renderBatches(batches);
        } catch (error) {
            console.error('Failed to load batches:', error);
        }
    }

    renderBatches(batches) {
        const tableBody = document.getElementById('batches-table-body');
        tableBody.innerHTML = batches.map(batch => `
            <tr>
                <td>${batch.batch_id}</td>
                <td>${batch.status}</td>
                <td>${batch.processed_items}/${batch.total_items}</td>
                <td>${new Date(batch.created_at).toLocaleString()}</td>
                <td>
                    <button class="view-btn" data-batch-id="${batch.batch_id}">View</button>
                </td>
            </tr>
        `).join('');

        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', () => this.showBatchDetails(btn.dataset.batchId));
        });
    }

    showBatchDetails(batchId) {
        // Implementation for showing batch details
        console.log('Showing details for batch:', batchId);
    }

    closeModal() {
        document.getElementById('batch-modal').classList.add('hidden');
        document.getElementById('batch-form').reset();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => new BatchSchedulerUI());