<?php
// Test both APIs
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head><title>API Test</title></head>
<body>
<h1>API Tests</h1>

<h2>1. Pexels Search</h2>
<button onclick="testPexels()">Test Pexels</button>
<pre id="pexels-result"></pre>

<h2>2. AI Image Generate</h2>
<button onclick="testAI()">Test AI</button>
<pre id="ai-result"></pre>

<script>
async function testPexels() {
    const res = document.getElementById('pexels-result');
    res.textContent = 'Loading...';
    try {
        const r = await fetch('/admin/api/pexels-search.php?query=dog');
        const text = await r.text();
        res.textContent = 'Status: ' + r.status + '\n' + text.substring(0, 500);
    } catch(e) {
        res.textContent = 'Error: ' + e.message;
    }
}

async function testAI() {
    const res = document.getElementById('ai-result');
    res.textContent = 'Loading...';
    try {
        const r = await fetch('/admin/api/ai-image-generate.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({prompt: 'a red apple', style: 'photorealistic', size: '1024x1024'})
        });
        const text = await r.text();
        res.textContent = 'Status: ' + r.status + '\n' + text.substring(0, 500);
    } catch(e) {
        res.textContent = 'Error: ' + e.message;
    }
}
</script>
</body>
</html>
