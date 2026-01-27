class ApprovalQueue {
    static init() {
        this.bindEvents();
    }

    static bindEvents() {
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.approve-btn').forEach(btn => {
                btn.addEventListener('click', (e) => this.handleApproval(e, 'approve'));
            });

            document.querySelectorAll('.reject-btn').forEach(btn => {
                btn.addEventListener('click', (e) => this.handleApproval(e, 'reject'));
            });
        });
    }

    static async handleApproval(event, action) {
        const button = event.target;
        const id = button.dataset.id;
        button.disabled = true;

        try {
            const response = await fetch(`/api/v1/approval/${id}/${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) throw new Error('Action failed');
            
            const row = button.closest('tr');
            row.classList.add('fade-out');
            setTimeout(() => row.remove(), 300);
            
        } catch (error) {
            console.error(`Error ${action}ing content:`, error);
            button.disabled = false;
            alert(`Failed to ${action} content. Please try again.`);
        }
    }
}

ApprovalQueue.init();