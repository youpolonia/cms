# AI Content Generation Troubleshooting Guide

## Common Issues

### Content Quality Problems
**Issue**: Generated content doesn't match expectations  
**Solutions**:
1. Make prompts more specific
2. Adjust temperature (lower for more factual, higher for creative)
3. Try different content templates
4. Provide examples in your prompt

### Rate Limit Errors
**Issue**: Receiving 429 Too Many Requests  
**Solutions**:
1. Implement exponential backoff in your code
2. Cache frequent requests
3. Contact admin for quota increase
4. Monitor usage in dashboard

### Moderation Flags
**Issue**: Content gets flagged by moderation  
**Solutions**:
1. Avoid sensitive topics
2. Use neutral language
3. Request manual review if needed
4. Check moderation guidelines

## Error Codes

| Code | Meaning | Action |
|------|---------|--------|
| 400 | Bad Request | Check request parameters |
| 401 | Unauthorized | Verify API token |
| 403 | Forbidden | Check permissions/quotas |
| 429 | Rate Limited | Reduce request frequency |
| 500 | Server Error | Retry with exponential backoff |

## Debugging Tips

### Client Side
```javascript
// Enable debug logging
AIGeneratorService.setDebug(true);

// Check last error
console.log(AIGeneratorService.lastError);
```

### Server Side
1. Check API logs for detailed errors
2. Verify OpenAI API key is valid
3. Monitor token usage patterns

## Performance Optimization

1. **Batch requests** when possible
2. **Cache responses** for similar prompts
3. **Pre-generate** common content
4. **Use webhooks** for async processing

## Support
For unresolved issues:
1. Gather request details (timestamp, prompt)
2. Check server logs if available
3. Contact support with reproduction steps