/**
 * CMS REST API Documentation
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CMS REST API Documentation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 900px; margin: 0 auto; padding: 20px; }
        h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        h2 { color: #444; margin-top: 30px; }
        .endpoint { background: #f9f9f9; border-left: 4px solid #ddd; padding: 15px; margin: 20px 0; }
        .method { font-weight: bold; color: #fff; background: #666; padding: 3px 8px; border-radius: 3px; }
        .get { background: #61affe; }
        .post { background: #49cc90; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>CMS REST API Documentation</h1>
    
    <section id="introduction">
        <h2>Introduction</h2>
        <p>This document describes the REST API endpoints available in the CMS.</p>
        
        <h3>Base URL</h3>
        <p><code>/api/v1/</code></p>
        
        <h3>Required Headers</h3>
        <ul>
            <li><strong>X-Tenant-Context</strong> - Tenant identifier (
required)</li>
?>            <li><strong>API-Key</strong> - Authentication key (optional for public endpoints)</li>
        </ul>
    </section>

    <section id="endpoints">
        <h2>Endpoints</h2>

        <div class="endpoint">
            <h3><span class="method get">GET</span> /api/v1/content</h3>
            <p>List all published pages</p>
            
            <h4>Parameters</h4>
            <table>
                <tr><th>Name</th><th>Type</th><th>Required</th><th>Description</th></tr>
                <tr><td>limit</td><td>integer</td><td>No</td><td>Number of items to
 return (default: 20)</td></tr>
?>                <tr><td>offset</td><td>integer</td><td>No</td><td>Pagination offset</td></tr>
            </table>
            
            <h4>Example Request</h4>
            <pre>GET /api/v1/content?limit=10 HTTP/1.1
Host: example.com
X-Tenant-Context: tenant123</pre>
            
            <h4>Example Response (200 OK)</h4>
            <pre>{
    "status": "success",
    "data": [
        {
            "id": "page1",
            "title": "Home Page",
            "slug": "home",
            "published": true
        }
    ]
}</pre>
            
            <h4>Status Codes</h4>
            <ul>
                <li><strong>200</strong> - Success</li>
                <li><strong>400</strong> - Invalid parameters</li>
                <li><strong>403</strong> - Forbidden</li>
            </ul>
        </div>

        <div class="endpoint">
            <h3><span class="method get">GET</span> /api/v1/blog</h3>
            <p>List recent blog posts</p>
            
            <h4>Parameters</h4>
            <table>
                <tr><th>Name</th><th>Type</th><th>Required</th><th>Description</th></tr>
                <tr><td>category</td><td>string</td><td>No</td><td>Filter by category</td></tr>
            </table>
            
            <h4>Example Response (200 OK)</h4>
            <pre>{
    "status": "success",
    "data": [
        {
            "id": "blog1",
            "title": "First Post",
            "excerpt": "Lorem ipsum...",
            "published_date": "2025-06-01"
        }
    ]
}</pre>
        </div>

        <div class="endpoint">
            <h3><span class="method get">GET</span> /api/v1/ai/history</h3>
            <p>Show recent AI actions</p>
            
            <h4>Status Codes</h4>
            <ul>
                <li><strong>200</strong> - Success</li>
                <li><strong>429</strong> - Rate limit exceeded</li>
            </ul>
        </div>

        <div class="endpoint">
            <h3><span class="method get">GET</span> /api/v1/media/search</h3>
            <p>Semantic media search</p>
            
            <h4>Parameters</h4>
            <table>
                <tr><th>Name</th><th>Type</th><th>Required</th><th>Description</th></tr>
                <tr><td>q</td><td>string</td><td>Yes</td><td>Search query</td></tr>
            </table>
        </div>

        <div class="endpoint">
            <h3><span class="method post">POST</span> /api/v1/content</h3>
            <p>Publish new content</p>
            
            <h4>Request Body</h4>
            <pre>{
    "title": "New Page",
    "content": "Page content...",
    "type": "page"
}</pre>
            
            <h4>Example Response (201 Created)</h4>
            <pre>{
    "status": "success",
    "data": {
        "id": "new-page",
        "slug": "new-page",
        "url": "/new-page"
    }
}</pre>
            
            <h4>Status Codes</h4>
            <ul>
                <li><strong>201</strong> - Created</li>
                <li><strong>400</strong> - Invalid input</li>
                <li><strong>403</strong> - Forbidden</li>
            </ul>
        </div>
    </section>
</body>
</html>
