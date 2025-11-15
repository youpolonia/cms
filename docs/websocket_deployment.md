# WebSocket Server Deployment Guide

## Shared Hosting Setup Options

### Option 1: SSH with nohup (Temporary Solution)
```bash
nohup php /var/www/html/cms/scripts/websocket_server.php > /dev/null 2>&1 &
```

### Option 2: Cron Job (Persistent Solution)
Add to crontab (`crontab -e`):
```bash
* * * * * pgrep -f websocket_server.php || nohup php /var/www/html/cms/scripts/websocket_server.php > /dev/null 2>&1 &
```

### Option 3: Systemd Service (Recommended for VPS)
Create `/etc/systemd/system/websocket.service`:
```ini
[Unit]
Description=Analytics WebSocket Server
After=network.target

[Service]
User=www-data
WorkingDirectory=/var/www/html/cms
ExecStart=/usr/bin/php /var/www/html/cms/scripts/websocket_server.php
Restart=always

[Install]
WantedBy=multi-user.target
```

Then enable and start:
```bash
sudo systemctl enable websocket
sudo systemctl start websocket
```

## Security Considerations

1. **Firewall Rules**:
   ```bash
   sudo ufw allow 8080/tcp
   ```

2. **SSL Configuration**:
   - Uncomment SSL settings in `config/websockets.php`
   - Ensure certificate paths are correct

3. **Authentication**:
   - Implement token-based auth in WebSocketHandler
   - Validate origin headers

## Monitoring

1. Check server status:
   ```bash
   pgrep -f websocket_server.php
   ```

2. View logs:
   ```bash
   tail -f /var/log/websocket.log
   ```

3. Basic health check:
   ```bash
   curl -I http://localhost:8080
   ```

## Troubleshooting

**Issue**: Port not accessible  
**Solution**: Check firewall and hosting provider's port restrictions

**Issue**: High memory usage  
**Solution**: Reduce `update_interval` in config

**Issue**: Connection drops  
**Solution**: Implement reconnection logic in client