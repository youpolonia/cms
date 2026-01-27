<div class="client-search-container">
    <h2>Client Search</h2>
    
    <div class="search-box">
        <input type="text" id="clientSearchInput" placeholder="Search clients by name, email, phone or address...">
        <button id="clientSearchButton">Search</button>
    </div>

    <div id="searchResultsContainer" class="results-container">
        <div class="loading-indicator" style="display: none;">
            <div class="spinner"></div>
            <span>Searching...</span>
        </div>
        
        <div id="searchResults" class="results-list"></div>
        
        <div id="searchError" class="error-message" style="display: none;"></div>
    </div>
</div>

<style>
.client-search-container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.search-box {
    display: flex;
    margin-bottom: 20px;
}

.search-box input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px 0 0 4px;
}

.search-box button {
    padding: 10px 20px;
    background: #0073aa;
    color: white;
    border: none;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
}

.search-box button:hover {
    background: #005d8c;
}

.results-container {
    border: 1px solid #ddd;
    border-radius: 4px;
    min-height: 200px;
    position: relative;
}

.loading-indicator {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.8);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    animation: spin 1s linear infinite;
    margin-bottom: 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.results-list {
    padding: 10px;
}

.result-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.result-item:last-child {
    border-bottom: none;
}

.result-item h3 {
    margin: 0 0 5px 0;
    color: #0073aa;
}

.result-item p {
    margin: 5px 0;
    color: #666;
}

.error-message {
    color: #dc3232;
    padding: 10px;
}
</style>
