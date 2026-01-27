document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('clientSearchInput');
    const searchButton = document.getElementById('clientSearchButton');
    const resultsContainer = document.getElementById('searchResults');
    const loadingIndicator = document.querySelector('.loading-indicator');
    const errorContainer = document.getElementById('searchError');

    // Handle search when button is clicked or Enter is pressed
    searchButton.addEventListener('click', performSearch);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    function performSearch() {
        const searchTerm = searchInput.value.trim();
        
        if (!searchTerm) {
            showError('Please enter a search term');
            return;
        }

        // Clear previous results and errors
        resultsContainer.innerHTML = '';
        errorContainer.style.display = 'none';
        
        // Show loading indicator
        loadingIndicator.style.display = 'flex';

        // Make AJAX request
        fetch(`/admin/clients/search.php?q=${encodeURIComponent(searchTerm)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displayResults(data.data);
                } else {
                    showError(data.error || 'An error occurred during search');
                }
            })
            .catch(error => {
                showError(error.message);
            })
            .finally(() => {
                loadingIndicator.style.display = 'none';
            });
    }

    function displayResults(clients) {
        resultsContainer.innerHTML = '';
        
        if (clients.length === 0) {
            resultsContainer.innerHTML = '<div class="no-results">No clients found matching your search</div>';
            return;
        }

        clients.forEach(client => {
            const clientElement = document.createElement('div');
            clientElement.className = 'result-item';
            clientElement.innerHTML = `
                <h3>${escapeHtml(client.name)}</h3>
                <p><strong>Email:</strong> ${escapeHtml(client.email || 'N/A')}</p>
                <p><strong>Phone:</strong> ${escapeHtml(client.phone || 'N/A')}</p>
                <p><strong>Status:</strong> <span class="status-${client.status}">${escapeHtml(client.status)}</span></p>
            `;
            resultsContainer.appendChild(clientElement);
        });
    }

    function showError(message) {
        errorContainer.textContent = message;
        errorContainer.style.display = 'block';
    }

    // Basic HTML escaping for security
    function escapeHtml(unsafe) {
        if (!unsafe) return unsafe;
        const text = unsafe.toString();
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});