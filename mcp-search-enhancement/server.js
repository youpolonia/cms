const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const { connectToVectorDB } = require('./vector-db');
const ContentReuseService = require('../../app/Services/ContentReuseService');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 8080;

// Initialize services
let vectorDB;
let reuseService;

async function initializeServices() {
  vectorDB = await connectToVectorDB();
  reuseService = new ContentReuseService(vectorDB);
}

app.use(cors());
app.use(bodyParser.json());

// Standard MCP endpoints
app.get('/ping', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

app.get('/api/v1/version', (req, res) => {
  res.json({
    version: '1.1.0',
    protocol: 'mcp-v1',
    capabilities: ['search-enhancement', 'semantic-search', 'content-reuse']
  });
});

// Search Enhancement endpoints
app.post('/search/semantic', async (req, res) => {
  try {
    const { query, threshold = 0.7, limit = 10 } = req.body;
    const results = await reuseService.findSimilarContent(query, threshold, limit);
    res.json({
      status: 'success',
      data: results.map(r => ({
        content_id: r.content_id,
        version_id: r.version_id,
        similarity: r.similarity,
        text_fragment: r.text_fragment,
        metadata: r.metadata
      }))
    });
  } catch (error) {
    res.status(500).json({ status: 'error', message: error.message });
  }
});

app.get('/search/suggestions', async (req, res) => {
  try {
    const { q: query } = req.query;
    const suggestions = await reuseService.getReuseSuggestions(query);
    res.json({
      status: 'success',
      suggestions: suggestions.map(s => ({
        id: s.id,
        content: s.content,
        usage_count: s.usage_count,
        last_used: s.last_used
      }))
    });
  } catch (error) {
    res.status(500).json({ status: 'error', message: error.message });
  }
});

app.post('/search/personalized', async (req, res) => {
  try {
    const { query, userId } = req.body;
    const results = await reuseService.getPersonalizedResults(query, userId);
    res.json({
      status: 'success',
      data: results
    });
  } catch (error) {
    res.status(500).json({ status: 'error', message: error.message });
  }
});

initializeServices().then(() => {
  app.listen(PORT, () => {
    console.log(`MCP Search Enhancement Server running on port ${PORT}`);
    console.log('Connected to vector database');
  });
}).catch(err => {
  console.error('Failed to initialize services:', err);
  process.exit(1);
});