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
app.post('/generate/content', async (req, res) => {
  try {
    const { prompt, model = 'gpt-4-turbo', max_tokens = 1000, seo_optimized = false } = req.body;
    
    const systemPrompt = seo_optimized
      ? 'You are an expert SEO content writer. Generate content that is: ' +
        '- Well-structured with headings\n' +
        '- Contains relevant keywords naturally\n' +
        '- Optimized for search intent\n' +
        '- Engaging and informative'
      : 'You are a helpful content generator.';

    let response;
    if (MODEL_PROVIDERS['openai'][model]) {
      response = await openai.chat.completions.create({
        model,
        messages: [
          { role: 'system', content: systemPrompt },
          { role: 'user', content: prompt }
        ],
        max_tokens,
        temperature: seo_optimized ? 0.5 : 0.7,
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

    res.json({
      status: 'success',
      content: response.content,
      model_used: response.model_used
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

app.post('/improve/content', async (req, res) => {
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