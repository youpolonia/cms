class ApprovalHistory {
    static init() {
        this.bindEvents();
    }

    static bindEvents() {
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('status-filter').addEventListener('change', () => this.filterResults());
            document.getElementById('date-filter').addEventListener('change', () => this.filterResults());
        });
    }

    static filterResults() {
        const statusFilter = document.getElementById('status-filter').value;
        const dateFilter = document.getElementById('date-filter').value;
        
        document.querySelectorAll('.history-table tbody tr').forEach(row => {
            const rowStatus = row.classList.contains('status-approved') ? 'approved' : 
                             row.classList.contains('status-rejected') ? 'rejected' : 'pending';
            const rowDate = row.querySelector('td:nth-child(6)').textContent.split(' ')[0];
            
            const statusMatch = statusFilter === 'all' || rowStatus === statusFilter;
            const dateMatch = !dateFilter || rowDate === dateFilter;
            
            row.style.display = (statusMatch && dateMatch) ? '' : 'none';
        });
    }
}

ApprovalHistory.init();