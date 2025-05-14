const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
require('dotenv').config();
const analyticsDb = require('./analytics-db');

const app = express();
const PORT = process.env.PORT || 8081;

app.use(cors());
app.use(bodyParser.json());

// Standard MCP endpoints
app.get('/ping', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

app.get('/api/v1/version', (req, res) => {
  res.json({
    version: '1.0.0',
    protocol: 'mcp-v1',
    capabilities: ['personalization', 'tracking', 'ab-testing']
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

// Personalization endpoints
const { OpenAI } = require('openai');
const openai = new OpenAI(process.env.OPENAI_API_KEY);
const collaborationController = new CollaborationController(analyticsDb);

// Content distribution helpers
async function getContentById(contentId) {
  // TODO: Implement content fetching from CMS
  return { id: contentId, title: 'Sample Content', body: '...' };
}

async function distributeToWeb(content) {
  // TODO: Implement web distribution logic
  return { status: 'published', url: `https://cms.example.com/content/${content.id}` };
}

async function distributeToMobile(content) {
  // TODO: Implement mobile push notification
  return { status: 'sent', platform: 'mobile' };
}

async function distributeToEmail(content) {
  // TODO: Implement email newsletter integration
  return { status: 'queued', newsletter: 'weekly-digest' };
}

async function distributeToSocial(content) {
  // TODO: Implement social media posting
  return { status: 'scheduled', platforms: ['twitter', 'facebook'] };
}

app.post('/recommend/content', async (req, res) => {
  try {
    const { contentId, contentType, tags } = req.body;
    
    const prompt = `Based on this content (ID: ${contentId}, Type: ${contentType}, Tags: ${tags.join(', ')}),
    recommend 3 similar pieces of content. Return as JSON array with ids and similarity scores.`;
    
    const completion = await openai.chat.completions.create({
      model: "gpt-4-turbo",
      messages: [{ role: "user", content: prompt }],
      response_format: { type: "json_object" }
    });

    res.json(JSON.parse(completion.choices[0].message.content));
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

app.get('/recommend/content/:userId', async (req, res) => {
  try {
    const { userId } = req.params;
    const { limit = 5 } = req.query;
    
    const prompt = `Generate personalized content recommendations for user ${userId}.
    Consider their reading history and preferences. Return ${limit} recommendations
    as JSON array with content ids and relevance scores.`;
    
    const completion = await openai.chat.completions.create({
      model: "gpt-4-turbo",
      messages: [{ role: "user", content: prompt }],
      response_format: { type: "json_object" }
    });

    res.json(JSON.parse(completion.choices[0].message.content));
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// Tracking endpoints
const analyticsDb = require('./analytics-db');

app.post('/track/behavior', async (req, res) => {
  try {
    const { userId, contentId, eventType, metadata } = req.body;
    
    // Store raw event
    await analyticsDb.storeEvent({
      userId,
      contentId,
      eventType,
      timestamp: new Date(),
      metadata
    });

    // Update user profile
    await analyticsDb.updateUserProfile(userId, {
      lastActivity: new Date(),
      [eventType]: (await analyticsDb.getUserProfile(userId))[eventType] + 1 || 1
    });

    // Update content stats
    await analyticsDb.updateContentStats(contentId, eventType);

    res.json({
      status: 'tracked',
      userId,
      contentId,
      eventType
    });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// Predictive analytics endpoint
app.get('/predict/behavior/:userId', async (req, res) => {
  try {
    const { userId } = req.params;
    const profile = await analyticsDb.getUserProfile(userId);
    const events = await analyticsDb.eventsCollection
      .find({ userId })
      .sort({ timestamp: -1 })
      .limit(100)
      .toArray();

    // Simple prediction algorithm (can be enhanced with ML)
    const prediction = {
      likelyNextContent: events[0]?.contentId, // Most recent content
      preferredContentType: events.reduce((acc, event) => {
        acc[event.metadata?.contentType] = (acc[event.metadata?.contentType] || 0) + 1;
        return acc;
      }, {}),
      activityPeakHours: events.reduce((acc, event) => {
        const hour = new Date(event.timestamp).getHours();
        acc[hour] = (acc[hour] || 0) + 1;
        return acc;
      }, {})
    };

    res.json({ userId, prediction });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// Content Distribution endpoints
app.post('/distribute/content', async (req, res) => {
  try {
    const { contentId, channels = ['web'] } = req.body;
    const content = await getContentById(contentId);

    const distributionResults = {};
    
    // Distribute to each requested channel
    for (const channel of channels) {
      switch(channel) {
        case 'web':
          distributionResults.web = await distributeToWeb(content);
          break;
        case 'mobile':
          distributionResults.mobile = await distributeToMobile(content);
          break;
        case 'email':
          distributionResults.email = await distributeToEmail(content);
          break;
        case 'social':
          distributionResults.social = await distributeToSocial(content);
          break;
        default:
          distributionResults[channel] = { error: 'Unsupported channel' };
      }
    }

    res.json({
      contentId,
      distributionResults
    });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// Collaboration endpoints
app.post('/collaboration/session', async (req, res) => {
  try {
    const { contentId, userIds } = req.body;
    const session = await collaborationController.createSession(contentId, userIds);
    res.json(session);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

app.post('/collaboration/join', async (req, res) => {
  try {
    const { sessionId, userId } = req.body;
    const session = await collaborationController.joinSession(sessionId, userId);
    res.json(session);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

app.post('/collaboration/save', async (req, res) => {
  try {
    const { sessionId, changes } = req.body;
    const versionId = await collaborationController.saveChanges(sessionId, changes);
    res.json({ versionId });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

app.get('/collaboration/history/:sessionId', async (req, res) => {
  try {
    const history = await collaborationController.getHistory(req.params.sessionId);
    res.json(history);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// A/B Testing endpoints
app.post('/abtest/create', (req, res) => {
  // TODO: Implement A/B test creation
  res.json({ status: 'received', data: req.body });
});

app.get('/abtest/status/:testId', (req, res) => {
  // TODO: Implement A/B test status check
  res.json({ status: 'received', testId: req.params.testId });
});

app.listen(PORT, () => {
  console.log(`MCP Personalization Engine running on port ${PORT}`);
  console.log('Available endpoints:');
  console.log(`- POST /recommend/content - AI-powered content recommendations`);
  console.log(`- GET /recommend/content/:userId - Personalized recommendations`);
  console.log(`- POST /track/behavior - User behavior tracking`);
  console.log(`- GET /predict/behavior/:userId - Predictive analytics`);
  console.log(`- POST /collaboration/session - Create collaboration session`);
  console.log(`- POST /collaboration/join - Join collaboration session`);
  console.log(`- POST /collaboration/save - Save content changes`);
  console.log(`- GET /collaboration/history/:sessionId - Get session history`);
  console.log(`- POST /distribute/content - Multi-channel distribution`);
  console.log(`- POST /abtest/create - A/B test creation`);
  console.log(`- GET /abtest/status/:testId - A/B test status`);
});