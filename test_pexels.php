<!DOCTYPE html>
<html>
<head><title>Pexels Test</title></head>
<body>
<h1>Pexels API Test</h1>
<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/admin/models/settingsmodel.php';
$settingsModel = new SettingsModel();
$pexelsApiKey = $settingsModel->getValue('pexels_api_key', '');
?>
<p>API Key loaded: <?= $pexelsApiKey ? 'YES (' . substr($pexelsApiKey, 0, 10) . '...)' : 'NO' ?></p>

<input type="text" id="query" value="dog">
<button onclick="search()">Search</button>
<div id="results"></div>

<script>
const API_KEY = '<?= $pexelsApiKey ?>';
console.log('API Key:', API_KEY ? 'loaded' : 'MISSING');

async function search() {
    const query = document.getElementById('query').value;
    const results = document.getElementById('results');
    results.innerHTML = 'Searching...';
    
    try {
        const resp = await fetch(`https://api.pexels.com/v1/search?query=${encodeURIComponent(query)}&per_page=5`, {
            headers: { 'Authorization': API_KEY }
        });
        console.log('Response status:', resp.status);
        const data = await resp.json();
        console.log('Data:', data);
        
        if (data.photos) {
            results.innerHTML = `Found ${data.photos.length} photos:<br>` + 
                data.photos.map(p => `<img src="${p.src.small}" style="width:100px">`).join('');
        } else {
            results.innerHTML = 'Error: ' + JSON.stringify(data);
        }
    } catch (e) {
        console.error('Fetch error:', e);
        results.innerHTML = 'Error: ' + e.message;
    }
}
</script>
</body>
</html>
