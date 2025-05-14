const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const OpenAI = require('openai');
const { HfInference } = require('@huggingface/inference');
const { GoogleGenerativeAI } = require('@google/generative-ai');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 8080;

// Model configurations
const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY,
});

const hf = new HfInference(process.env.HF_API_KEY);
const genAI = new GoogleGenerativeAI(process.env.GOOGLE_API_KEY);
const gemini = genAI.getGenerativeModel({ model: "gemini-pro" });

// Testing utilities
async function runIntegrationTests() {
  try {
    const testCases = [
      {
        name: 'Content Generation',
        endpoint: '/content/generate',
        method: 'POST',
        body: {
          prompt: 'Test content',
          content_type: 'plain_text',
          tone: 'neutral',
          length: 'short'
        }
      },
      {
        name: 'Content Personalization',
        endpoint: '/content/personalize',
        method: 'POST',
        body: {
          content: 'Test content',
          user_id: 'test_user'
        }
      },
      {
        name: 'Media Processing',
        endpoint: '/media/process',
        method: 'POST',
        body: {
          content: '<p>Test content</p>'
        }
      }
    ];

    const results = await Promise.all(testCases.map(async (test) => {
      const response = await fetch(`http://localhost:${process.env.PORT || 3000}${test.endpoint}`, {
        method: test.method,
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(test.body)
      });
      
      return {
        name: test.name,
        status: response.status,
        success: response.ok
      };
    }));

    return results;
  } catch (error) {
    console.error('Integration test error:', error);
    throw error;
  }
}

// Database optimization
async function optimizeDatabaseQueries() {
  try {
    await redisClient.sendCommand([
      'CONFIG',
      'SET',
      'maxmemory-policy',
      'allkeys-lru'
    ]);
    
    await redisClient.sendCommand([
      'CONFIG',
      'SET',
      'timeout',
      '300'
    ]);
    
    // Enable Redis query caching
    await redisClient.sendCommand([
      'CONFIG',
      'SET',
      'query-cache',
      'on'
    ]);
  } catch (error) {
    console.error('Database optimization error:', error);
  }
}

// Initialize with optimizations
optimizeDatabaseQueries();

// Media processing pipeline
async function processMedia(content) {
  try {
    const response = await fetch(`${process.env.MEDIA_PROCESSING_API}/process`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${process.env.MEDIA_PROCESSING_API_KEY}`
      },
      body: JSON.stringify({
        content,
        operations: [
          'compress_images',
          'optimize_videos',
          'generate_thumbnails'
        ]
      })
    });
    
    if (!response.ok) {
      throw new Error('Media processing failed');
    }
    
    return await response.json();
  } catch (error) {
    console.error('Media processing error:', error);
    throw error;
  }
}

// Analytics tracking
async function trackContentEvent(eventType, data) {
  try {
    await fetch(`${process.env.ANALYTICS_API}/events`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${process.env.ANALYTICS_API_KEY}`
      },
      body: JSON.stringify({
        event_type: eventType,
        timestamp: new Date().toISOString(),
        data
      })
    });
  } catch (error) {
    console.error('Analytics tracking error:', error);
  }
}

// Version control integration
async function createContentVersion(content, metadata = {}) {
  try {
    const versionData = {
      content,
      metadata: {
        generated_at: new Date().toISOString(),
        ...metadata
      }
    };
    
    // Store in version control system
    const response = await fetch(`${process.env.VERSION_CONTROL_API}/versions`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${process.env.VERSION_CONTROL_API_KEY}`
      },
      body: JSON.stringify(versionData)
    });
    
    if (!response.ok) {
      throw new Error('Failed to create version');
    }
    
    return await response.json();
  } catch (error) {
    console.error('Version control error:', error);
    throw error;
  }
}

// Content moderation service
async function moderateContent(content) {
  try {
    const moderation = await openai.moderations.create({
      input: content
    });
    
    if (moderation.results[0].flagged) {
      const categories = Object.entries(moderation.results[0].categories)
        .filter(([_, value]) => value)
        .map(([key]) => key);
        
      throw new Error(`Content flagged for: ${categories.join(', ')}`);
    }
    
    return true;
  } catch (error) {
    console.error('Moderation error:', error);
    throw error;
  }
}

// Cost calculation function
function calculateCost(model, tokens) {
  const COST_PER_TOKEN = {
    'gpt-3.5-turbo': 0.000002,
    'gpt-4-turbo': 0.00001,
    'gpt-4': 0.00003,
    'llama-3-8b': 0.0000015,
    'llama-3-70b': 0.0000025,
    'mistral-7b': 0.000001,
    'gemma-7b': 0.0000012
  };
  return (COST_PER_TOKEN[model] || 0.000002) * tokens;
}

const MODEL_PROVIDERS = {
  'openai': {
    'gpt-3.5-turbo': openai,
    'gpt-4': openai,
    'gpt-4-turbo': openai
  },
  'huggingface': {
    'llama-3-8b': hf,
    'llama-3-70b': hf,
    'mistral-7b': hf
  },
  'google': {
    'gemma-7b': gemini
  }
};

// Initialize Redis for usage tracking
const redis = require('redis');
const redisClient = redis.createClient({
  url: process.env.REDIS_URL || 'redis://localhost:6379'
});

redisClient.on('error', (err) => console.error('Redis error:', err));
redisClient.connect();

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
    capabilities: ['content-generation', 'ai-content']
  });
});

app.get('/api/v1/health', (req, res) => {
  res.json({
    status: 'healthy',
    uptime: process.uptime(),
    memory: process.memoryUsage()
  });
});

app.get('/api/v1/auth/verify', (req, res) => {
  const apiKey = req.headers['authorization'];
  if (apiKey === `Bearer ${process.env.API_KEY}`) {
    res.json({ valid: true });
  } else {
    res.status(401).json({ valid: false });
  }
});

// Content Generation endpoints
app.post('/content/generate', async (req, res) => {
  try {
    const {
      prompt,
      model = 'gpt-4-turbo',
      content_type = 'text',
      tone = 'professional',
      length = 'medium',
      temperature = 0.7
    } = req.body;
    
    const max_tokens = {
      short: 500,
      medium: 1000,
      long: 2000,
      extended: 4000
    }[length] || 1000;

    const systemPrompt = {
      text: 'You are a helpful content generator.',
      html: 'Generate well-structured HTML content with proper headings and semantic markup.',
      seo: 'You are an expert SEO content writer. Generate content that is well-structured with headings, contains relevant keywords naturally, optimized for search intent, and engaging.'
    }[content_type] || 'You are a helpful content generator.';

    const toneInstruction = {
      professional: 'Use a formal, business-appropriate tone.',
      casual: 'Use a conversational, informal tone.',
      friendly: 'Use a warm, approachable tone.',
      authoritative: 'Use an expert, confident tone.',
      creative: 'Use an imaginative, expressive tone.',
      technical: 'Use precise, detailed technical language.'
    }[tone] || '';

    const fullPrompt = `${systemPrompt}\n${toneInstruction}\n\n${prompt}`;

    let response;
    if (MODEL_PROVIDERS['openai'][model]) {
      response = await openai.chat.completions.create({
        model,
        messages: [
          { role: 'system', content: systemPrompt },
          { role: 'user', content: fullPrompt }
        ],
        max_tokens,
        temperature,
      });
      response = {
        content: response.choices[0].message.content.trim(),
        model_used: response.model
      };
    }
    else if (MODEL_PROVIDERS['huggingface'][model]) {
      response = await hf.textGeneration({
        model: `meta-llama/${model}`,
        inputs: `[INST] <<SYS>>\n${systemPrompt}\n<</SYS>>\n\n${prompt} [/INST]`,
        parameters: {
          max_new_tokens: max_tokens,
          temperature: seo_optimized ? 0.5 : 0.7
        }
      });
      response = {
        content: response.generated_text,
        model_used: model
      };
    }
    else if (MODEL_PROVIDERS['google'][model]) {
      const result = await gemini.generateContent({
        contents: [
          {
            parts: [
              {text: systemPrompt},
              {text: prompt}
            ]
          }
        ],
        generationConfig: {
          maxOutputTokens: max_tokens,
          temperature: seo_optimized ? 0.5 : 0.7
        }
      });
      response = {
        content: result.response.text(),
        model_used: model
      };
    }

    // Moderate, version, track, analyze and process media
    await moderateContent(response.content);
    const version = await createContentVersion(response.content, {
      model: response.model_used,
      prompt: req.body.prompt,
      parameters: {
        content_type: req.body.content_type,
        tone: req.body.tone,
        length: req.body.length
      }
    });
    
    if (req.body.content_type === 'rich_text') {
      await processMedia(response.content);
    }
    
    await trackContentEvent('content_generated', {
      version_id: version.id,
      model: response.model_used,
      tokens: response.usage?.total_tokens || 0,
      content_type: req.body.content_type,
      length: req.body.length
    });
    
    const today = new Date().toISOString().split('T')[0];
    const month = new Date().toISOString().substring(0, 7);
    const tokens = response.usage?.total_tokens || 0;
    
    await Promise.all([
      redisClient.incrBy(`usage:daily:${today}`, tokens),
      redisClient.incrBy(`usage:monthly:${month}`, tokens)
    ]);
    
    res.json({
      status: 'success',
      content: response.content,
      model_used: response.model_used,
      tokens_used: tokens,
      cost: calculateCost(response.model_used, tokens),
      version_id: version.id
    });
  } catch (error) {
    console.error('Content generation error:', error);
    res.status(500).json({
      status: 'error',
      message: 'Failed to generate content',
      error: error.message
    });
  }
});

app.post('/generate/summary', async (req, res) => {
  try {
    const { text, model = 'gpt-3.5-turbo' } = req.body;
    
    const response = await openai.chat.completions.create({
      model,
      messages: [
        { role: 'system', content: 'You are a helpful assistant that summarizes text.' },
        { role: 'user', content: `Summarize this text: ${text}` }
      ],
      temperature: 0.5,
    });

    res.json({
      status: 'success',
      summary: response.choices[0].message.content.trim()
    });
  } catch (error) {
    console.error('OpenAI error:', error);
    res.status(500).json({
      status: 'error',
      message: 'Failed to generate summary'
    });
  }
});

app.post('/generate/seo', async (req, res) => {
  try {
    const { topic, model = 'gpt-3.5-turbo' } = req.body;
    
    const response = await openai.chat.completions.create({
      model,
      messages: [
        { role: 'system', content: 'You are an SEO expert that suggests keywords and meta descriptions.' },
        { role: 'user', content: `Suggest SEO keywords and meta description for: ${topic}` }
      ],
      temperature: 0.3,
    });

    res.json({
      status: 'success',
      seo: response.choices[0].message.content.trim()
    });
  } catch (error) {
    console.error('OpenAI error:', error);
    res.status(500).json({
      status: 'error',
      message: 'Failed to generate SEO suggestions'
    });
  }
});

// Add usage tracking endpoint
app.get('/content/usage', async (req, res) => {
  try {
    const today = new Date().toISOString().split('T')[0];
    const month = new Date().toISOString().substring(0, 7);
    
    const [daily, monthly] = await Promise.all([
      redisClient.get(`usage:daily:${today}`),
      redisClient.get(`usage:monthly:${month}`)
    ]);
    
    res.json({
      status: 'success',
      daily_usage: parseInt(daily || 0),
      daily_limit: 10000,
      monthly_usage: parseInt(monthly || 0),
      monthly_limit: 100000,
      remaining: 10000 - parseInt(daily || 0)
    });
  } catch (error) {
    console.error('Usage tracking error:', error);
    res.status(500).json({
      status: 'error',
      message: 'Failed to get usage stats'
    });
  }
});

// Personalization endpoints
app.post('/content/personalize', async (req, res) => {
  try {
    const { content, user_id } = req.body;
    
    // Get user preferences from DB
    const preferences = await getUserPreferences(user_id);
    
    const response = await openai.chat.completions.create({
      model: 'gpt-4-turbo',
      messages: [
        {
          role: 'system',
          content: `Personalize this content for user with preferences: ${JSON.stringify(preferences)}`
        },
        {
          role: 'user',
          content: content
        }
      ]
    });
    
    res.json({
      status: 'success',
      personalized_content: response.choices[0].message.content
    });
  } catch (error) {
    console.error('Personalization error:', error);
    res.status(500).json({
      status: 'error',
      message: 'Failed to personalize content'
    });
  }
});

// Analytics endpoints
app.get('/analytics/summary', async (req, res) => {
  try {
    const response = await fetch(`${process.env.ANALYTICS_API}/summary`);
    const data = await response.json();
    
    res.json({
      status: 'success',
      analytics: data
    });
  } catch (error) {
    console.error('Analytics error:', error);
    res.status(500).json({
      status: 'error',
      message: 'Failed to get analytics'
    });
  }
});

// Media processing endpoints
app.post('/media/process', async (req, res) => {
  try {
    const result = await processMedia(req.body.content);
    
    res.json({
      status: 'success',
      processed_media: result
    });
  } catch (error) {
    console.error('Media processing error:', error);
    res.status(500).json({
      status: 'error',
      message: 'Failed to process media'
    });
  }
});

// Database endpoints
app.post('/database/optimize', async (req, res) => {
  try {
    await optimizeDatabaseQueries();
    
    res.json({
      status: 'success',
      message: 'Database optimizations applied'
    });
  } catch (error) {
    console.error('Database optimization error:', error);
    res.status(500).json({
      status: 'error',
      message: 'Failed to optimize database'
    });
  }
});

// Testing endpoints
app.get('/test/integration', async (req, res) => {
  try {
    const results = await runIntegrationTests();
    
    res.json({
      status: 'success',
      test_results: results
    });
  } catch (error) {
    console.error('Test error:', error);
    res.status(500).json({
      status: 'error',
      message: 'Integration tests failed'
    });
  }
});

app.post('/content/improve', async (req, res) => {
  try {
    const { content, instructions, model = 'gpt-4-turbo' } = req.body;
    
    const response = await openai.chat.completions.create({
      model,
      messages: [
        {
          role: 'system',
          content: 'You are a content editor. Improve the given content based on the instructions.'
        },
        {
          role: 'user',
          content: `Content:\n${content}\n\nInstructions:\n${instructions}`
        }
      ],
      temperature: 0.5,
    });

    res.json({
      status: 'success',
      improved_content: response.choices[0].message.content.trim()
    });
  } catch (error) {
    console.error('Content improvement error:', error);
    res.status(500).json({
      status: 'error',
      message: 'Failed to improve content'
    });
  }
});

app.listen(PORT, () => {
  console.log(`MCP Content Generation Server running on port ${PORT}`);
});