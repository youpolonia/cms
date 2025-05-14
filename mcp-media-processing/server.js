const express = require('express');
const bodyParser = require('body-parser');
const { LocalStorageAdapter } = require('./src/storage/StorageAdapter');
const MediaProcessingService = require('./src/MediaProcessingService');
const CDNService = require('./src/cdn/CDNService');
const MonitoringServer = require('./src/monitoring/MonitoringServer');
const config = require('./config');

class MediaProcessingServer {
  constructor() {
    this.app = express();
    this.setupMiddleware();
    this.initServices();
    this.setupRoutes();
  }

  setupMiddleware() {
    this.app.use(bodyParser.json());
    this.app.use(bodyParser.urlencoded({ extended: true }));
  }

  initServices() {
    // Initialize storage adapter
    this.storageAdapter = new LocalStorageAdapter(config.storage);
    
    // Initialize CDN service
    this.cdnService = new CDNService(config.cdn);
    
    // Initialize media processing service
    this.mediaService = new MediaProcessingService(
      this.storageAdapter,
      this.cdnService
    );
    
    // Initialize monitoring
    this.monitoringServer = new MonitoringServer(
      this.mediaService,
      config.monitoring
    );
  }

  setupRoutes() {
    // Image optimization endpoint
    this.app.post('/api/images/optimize', async (req, res) => {
      try {
        const result = await this.mediaService.optimizeImage(
          req.body.filePath,
          req.body.options
        );
        res.json(result);
      } catch (error) {
        res.status(500).json({ error: error.message });
      }
    });

    // Video transcoding endpoint
    this.app.post('/api/videos/transcode', async (req, res) => {
      try {
        const result = await this.mediaService.transcodeVideo(
          req.body.filePath,
          req.body.options
        );
        res.json(result);
      } catch (error) {
        res.status(500).json({ error: error.message });
      }
    });

    // Health check endpoint
    this.app.get('/health', (req, res) => {
      res.json({ status: 'ok' });
    });
  }

  start() {
    // Start monitoring server
    this.monitoringServer.start();
    
    // Start main server
    return this.app.listen(config.port, () => {
      console.log(`Media processing server running on port ${config.port}`);
    });
  }
}

// Start the server
const server = new MediaProcessingServer();
server.start();