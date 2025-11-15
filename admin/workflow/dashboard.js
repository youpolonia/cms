document.addEventListener('DOMContentLoaded', function() {
    // Mock data for demonstration
    const mockExecutions = [
        {
            id: 1,
            workflow: 'Content Approval',
            trigger: 'Content Published',
            startTime: '2025-06-05 09:30:15',
            duration: '2.5s',
            status: 'success'
        },
        {
            id: 2,
            workflow: 'User Notification',
            trigger: 'New Registration',
            startTime: '2025-06-05 10:15:22',
            duration: '1.8s',
            status: 'success'
        },
        {
            id: 3,
            workflow: 'Data Backup',
            trigger: 'Scheduled (Daily)',
            startTime: '2025-06-05 02:00:00',
            duration: '45.2s',
            status: 'failed'
        },
        {
            id: 4,
            workflow: 'Report Generation',
            trigger: 'Scheduled (Weekly)',
            startTime: '2025-06-04 03:00:00',
            duration: '12.3s',
            status: 'success'
        },
        {
            id: 5,
            workflow: 'Content Archiving',
            trigger: 'Content Updated',
            startTime: '2025-06-05 11:45:10',
            duration: '5.7s',
            status: 'running'
        }
    ];

    // DOM elements
    const activeCountEl = document.getElementById('active-count');
    const todayCountEl = document.getElementById('today-count');
    const successRateEl = document.getElementById('success-rate');
    const executionTable = document.getElementById('execution-table').querySelector('tbody');
    const filterStatus = document.getElementById('filter-status');
    const searchWorkflow = document.getElementById('search-workflow');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    const pageInfo = document.getElementById('page-info');

    // Pagination variables
    let currentPage = 1;
    const itemsPerPage = 10;
    let filteredData = [...mockExecutions];

    // Initialize dashboard
    function initDashboard() {
        updateStats();
        renderTable();
        setupEventListeners();
    }

    // Update statistics
    function updateStats() {
        const today = new Date().toISOString().split('T')[0];
        const todayExecutions = mockExecutions.filter(exec => 
            exec.startTime.startsWith(today)
        );
        
        activeCountEl.textContent = mockExecutions.filter(e => e.status === 'running').length;
        todayCountEl.textContent = todayExecutions.length;
        
        const successCount = todayExecutions.filter(e => e.status === 'success').length;
        const successRate = todayExecutions.length > 0 
            ? Math.round((successCount / todayExecutions.length) * 100) 
            : 0;
        successRateEl.textContent = `${successRate}%`;
    }

    // Render execution table
    function renderTable() {
        executionTable.innerHTML = '';
        
        const startIdx = (currentPage - 1) * itemsPerPage;
        const paginatedData = filteredData.slice(startIdx, startIdx + itemsPerPage);
        
        paginatedData.forEach(exec => {
            const row = document.createElement('tr');
            
            row.innerHTML = `
                <td>${exec.workflow}</td>
                <td>${exec.trigger}</td>
                <td>${exec.startTime}</td>
                <td>${exec.duration}</td>
                <td><span class="status-badge status-${exec.status}">${exec.status}</span></td>
                <td><button class="btn btn-sm" data-id="${exec.id}">Details</button></td>
            `;
            
            executionTable.appendChild(row);
        });
        
        updatePaginationControls();
    }

    // Update pagination controls
    function updatePaginationControls() {
        const totalPages = Math.ceil(filteredData.length / itemsPerPage);
        
        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage === totalPages;
        pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    }

    // Filter executions based on status and search
    function filterExecutions() {
        const statusFilter = filterStatus.value;
        const searchTerm = searchWorkflow.value.toLowerCase();
        
        filteredData = mockExecutions.filter(exec => {
            const matchesStatus = statusFilter === 'all' || exec.status === statusFilter;
            const matchesSearch = exec.workflow.toLowerCase().includes(searchTerm) || 
                                 exec.trigger.toLowerCase().includes(searchTerm);
            return matchesStatus && matchesSearch;
        });
        
        currentPage = 1;
        renderTable();
    }

    // Setup event listeners
    function setupEventListeners() {
        filterStatus.addEventListener('change', filterExecutions);
        searchWorkflow.addEventListener('input', filterExecutions);
        
        prevPageBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        });
        
        nextPageBtn.addEventListener('click', () => {
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderTable();
            }
        });
        
        // Details button click handler (delegated)
        executionTable.addEventListener('click', (e) => {
            if (e.target.tagName === 'BUTTON') {
                const execId = parseInt(e.target.dataset.id);
                const execution = mockExecutions.find(e => e.id === execId);
                alert(`Details for execution #${execId}\nWorkflow: ${execution.workflow}\nStatus: ${execution.status}`);
            }
        });
    }

    // Initialize the dashboard
    initDashboard();
});