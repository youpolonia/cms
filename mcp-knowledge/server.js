import express from 'express';
import bodyParser from 'body-parser';
import cors from 'cors';
import redis from 'redis';
import zlib from 'zlib';
import dotenv from 'dotenv';
dotenv.config();

const app = express();
const PORT = process.env.PORT || 8084;
const redisClient = redis.createClient({
  url: process.env.REDIS_URL || 'redis://localhost:6379'
});

redisClient.on('error', (err) => console.log('Redis Client Error', err));
redisClient.connect();

app.use(cors());
app.use(bodyParser.json({ limit: '50mb' }));

// API Key validation middleware
app.use((req, res, next) => {
  const apiKey = req.headers['authorization'];
  if (!apiKey || apiKey !== `Bearer ${process.env.API_KEY}`) {
    return res.status(401).json({
      success: false,
      message: 'Invalid or missing API key'
    });
  }
  next();
});

// Standard MCP endpoints
app.get('/health', (req, res) => {
  res.json({
    status: 'healthy',
    uptime: process.uptime(),
    memory: process.memoryUsage()
  });
});

// Knowledge endpoints
app.post('/store', async (req, res) => {
  try {
    const { key, value } = req.body;
    const MAX_CHUNK_SIZE = 2 * 1024 * 1024; // 2MB
    const MAX_TOTAL_SIZE = 20 * 1024 * 1024; // 20MB
    const COMPRESSION_THRESHOLD = 1024 * 1024; // 1MB
    
    if (value.length > MAX_TOTAL_SIZE) {
      return res.status(413).json({
        success: false,
        message: `Value exceeds maximum total size of ${MAX_TOTAL_SIZE} bytes`
      });
    }

    let valueToStore = value;
    let compressed = false;
    
    if (value.length > COMPRESSION_THRESHOLD) {
      valueToStore = zlib.deflateSync(value).toString('base64');
      compressed = true;
    }

    // Split into chunks if needed
    const chunks = [];
    for (let i = 0; i < valueToStore.length; i += MAX_CHUNK_SIZE) {
      chunks.push(valueToStore.substring(i, i + MAX_CHUNK_SIZE));
    }

    // Store chunks with metadata
    const meta = {
      chunks: chunks.length,
      compressed,
      createdAt: new Date().toISOString()
    };

    await redisClient.set(`${key}:meta`, JSON.stringify(meta));
    for (let i = 0; i < chunks.length; i++) {
      await redisClient.set(`${key}:${i}`, chunks[i]);
    }
    res.json({ success: true, key });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

// Batch caching endpoint
app.post('/store-batch', async (req, res) => {
  try {
    const { files } = req.body;
    const MAX_BATCH_SIZE = 100; // Maximum files per batch
    const BATCH_RATE_LIMIT = 10 * 1024 * 1024; // 10MB per batch
    
    if (!Array.isArray(files)) {
      return res.status(400).json({
        success: false,
        message: 'Files must be an array'
      });
    }
    
    if (files.length > MAX_BATCH_SIZE) {
      return res.status(413).json({
        success: false,
        message: `Batch exceeds maximum size of ${MAX_BATCH_SIZE} files`
      });
    }
    
    // Calculate total size
    let totalSize = 0;
    for (const file of files) {
      if (!file.key || !file.value) {
        return res.status(400).json({
          success: false,
          message: 'Each file must have key and value'
        });
      }
      totalSize += file.value.length;
    }
    
    if (totalSize > BATCH_RATE_LIMIT) {
      return res.status(413).json({
        success: false,
        message: `Batch exceeds maximum size of ${BATCH_RATE_LIMIT} bytes`
      });
    }
    
    // Process each file using existing store logic
    const results = [];
    for (const file of files) {
      try {
        const { key, value } = file;
        const MAX_CHUNK_SIZE = 2 * 1024 * 1024; // 2MB
        const MAX_TOTAL_SIZE = 20 * 1024 * 1024; // 20MB
        const COMPRESSION_THRESHOLD = 1024 * 1024; // 1MB
        
        if (value.length > MAX_TOTAL_SIZE) {
          results.push({
            key,
            success: false,
            message: `Value exceeds maximum total size of ${MAX_TOTAL_SIZE} bytes`
          });
          continue;
        }

        let valueToStore = value;
        let compressed = false;
        
        if (value.length > COMPRESSION_THRESHOLD) {
          valueToStore = zlib.deflateSync(value).toString('base64');
          compressed = true;
        }

        // Split into chunks if needed
        const chunks = [];
        for (let i = 0; i < valueToStore.length; i += MAX_CHUNK_SIZE) {
          chunks.push(valueToStore.substring(i, i + MAX_CHUNK_SIZE));
        }

        // Store chunks with metadata
        const meta = {
          chunks: chunks.length,
          compressed,
          createdAt: new Date().toISOString()
        };

        await redisClient.set(`${key}:meta`, JSON.stringify(meta));
        for (let i = 0; i < chunks.length; i++) {
          await redisClient.set(`${key}:${i}`, chunks[i]);
        }
        results.push({ key, success: true });
      } catch (err) {
        results.push({
          key: file.key,
          success: false,
          message: err.message
        });
      }
    }
    
    res.json({ success: true, results });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

app.get('/retrieve/:key', async (req, res) => {
  try {
    // Get metadata first
    const metaStr = await redisClient.get(`${req.params.key}:meta`);
    if (!metaStr) {
      return res.status(404).json({
        success: false,
        message: 'Key not found'
      });
    }

    const meta = JSON.parse(metaStr);
    let chunks = [];
    
    // Retrieve all chunks
    for (let i = 0; i < meta.chunks; i++) {
      const chunk = await redisClient.get(`${req.params.key}:${i}`);
      if (!chunk) {
        return res.status(500).json({
          success: false,
          message: `Missing chunk ${i}`
        });
      }
      chunks.push(chunk);
    }

    let value = chunks.join('');
    
    // Decompress if needed
    if (meta.compressed) {
      value = zlib.inflateSync(Buffer.from(value, 'base64')).toString();
    }

    res.json({ success: true, key: req.params.key, value });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

// Simplified API endpoints that replace MCP tools
app.post('/api/cache-file', async (req, res) => {
  try {
    const { path } = req.body;
    if (!path) {
      return res.status(400).json({ success: false, message: 'Path is required' });
    }
    const response = await fetchFileFromCMS(path);
    await storeInRedis(path, response);
    res.json({ success: true, path });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

app.post('/api/cache-batch', async (req, res) => {
  try {
    const { paths } = req.body;
    if (!Array.isArray(paths)) {
      return res.status(400).json({ success: false, message: 'Paths must be an array' });
    }
    const files = await Promise.all(paths.map(async path => ({
      key: path,
      value: await fetchFileFromCMS(path)
    })));
    const storeResponse = await axios.post(`http://localhost:${PORT}/store-batch`, { files });
    res.json(storeResponse.data);
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

app.get('/api/get-cached-file', async (req, res) => {
  try {
    const { path } = req.query;
    if (!path) {
      return res.status(400).json({ success: false, message: 'Path is required' });
    }
    const retrieveResponse = await axios.get(`http://localhost:${PORT}/retrieve/${encodeURIComponent(path)}`);
    res.json(retrieveResponse.data);
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

app.listen(PORT, () => {

// Register MCP tools
const tools = {
  'cache_file': {
    description: 'Cache a single file from the CMS',
    inputSchema: {
      type: 'object',
      properties: {
        path: { type: 'string', description: 'File path to cache' }
      },
      required: ['path']
    },
    handler: async ({ path }) => {
      const response = await axios.post(`http://localhost:${PORT}/api/cache-file`, { path });
      return response.data;
    }
  },
  'cache_batch': {
    description: 'Cache multiple files from the CMS in batch',
    inputSchema: {
      type: 'object',
      properties: {
        paths: { 
          type: 'array', 
          items: { type: 'string' },
          description: 'Array of file paths to cache'
        }
      },
      required: ['paths']
    },
    handler: async ({ paths }) => {
      const response = await axios.post(`http://localhost:${PORT}/api/cache-batch`, { paths });
      return response.data;
    }
  },
  'get_cached_file': {
    description: 'Retrieve a cached file by path',
    inputSchema: {
      type: 'object',
      properties: {
        path: { type: 'string', description: 'File path to retrieve' }
      },
      required: ['path']
    },
    handler: async ({ path }) => {
      const response = await axios.get(`http://localhost:${PORT}/api/get-cached-file?path=${encodeURIComponent(path)}`);
      return response.data;
    }
  }
};

// MCP tool endpoint
app.post('/mcp/tools/:tool', async (req, res) => {
  try {
    const tool = tools[req.params.tool];
    if (!tool) {
      return res.status(404).json({
        success: false,
        message: 'Tool not found'
      });
    }
    
    const result = await tool.handler(req.body);
    res.json({
      success: true,
      result
    });
  } catch (err) {
    res.status(500).json({
      success: false,
      message: err.message
    });
  }
});

// MCP tool discovery endpoint
app.get('/mcp/tools', (req, res) => {
  const toolList = Object.entries(tools).map(([name, config]) => ({
    name,
    description: config.description,
    inputSchema: config.inputSchema
  }));
  
  res.json({
    success: true,
    tools: toolList
  });
});
  console.log(`MCP Knowledge Server running on port ${PORT}`);
});

async function fetchFileFromCMS(path) {
  // Implement actual CMS file fetching logic
  return fs.readFileSync(path, 'utf-8');
}

async function storeInRedis(key, value) {
  // Implement single file storage logic
  const response = await axios.post(`http://localhost:${PORT}/store`, { key, value });
  return response.data;
}