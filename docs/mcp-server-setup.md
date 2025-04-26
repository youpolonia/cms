# MCP Knowledge Server Setup Guide

## Prerequisites
- Node.js 18+ or Python 3.9+
- Redis (for caching)
- PostgreSQL (for data storage)

## Installation Options

### Option 1: Node.js Server
```bash
# Clone the MCP knowledge server repository
git clone https://github.com/mcp-platform/knowledge-server.git
cd knowledge-server

# Install dependencies
npm install

# Configure environment
cp .env.example .env
# Edit .env with your configuration

# Start server
npm start
```

### Option 2: Python Server
```bash
# Clone the MCP knowledge server repository
git clone https://github.com/mcp-platform/knowledge-server-python.git
cd knowledge-server-python

# Create virtual environment
python -m venv venv
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt

# Configure environment
cp .env.example .env
# Edit .env with your configuration

# Start server
python main.py
```

## Configuration

Add these to your CMS `.env` file:
```ini
MCP_KNOWLEDGE_HOST=127.0.0.1
MCP_KNOWLEDGE_PORT=8080
MCP_KNOWLEDGE_API_KEY=your-secret-key-here
MCP_KNOWLEDGE_TIMEOUT=30
```

## API Endpoints
The server should expose these endpoints:
- `GET /api/v1/version` - Get protocol version
- `GET /api/v1/health` - Health check
- `GET /api/v1/auth/verify` - Authentication verification
- `GET /api/v1/resources` - Resource availability
- `GET /api/v1/metrics` - Performance metrics
- `GET /api/v1/cache` - Get cached content

## Deployment
For production deployment, use:
- PM2 (Node.js) or Gunicorn (Python)
- Nginx as reverse proxy
- Systemd service for automatic restarts

## Troubleshooting
- Check server logs for errors
- Verify network connectivity between CMS and MCP server
- Ensure API key matches in both configurations