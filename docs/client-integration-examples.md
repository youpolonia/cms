# Client Integration Examples

## Basic Usage

```javascript
// Start async job
async function startContentGeneration(prompt) {
  const response = await fetch('/api/jobs/generate-content', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${authToken}`
    },
    body: JSON.stringify({
      prompt,
      model: 'gpt-4',
      cache_key: `content-gen-${Date.now()}`
    })
  });
  return await response.json();
}

// Check status
async function checkJobStatus(cacheKey) {
  const response = await fetch(`/api/jobs/status?cache_key=${cacheKey}`, {
    headers: {
      'Authorization': `Bearer ${authToken}`
    }
  });
  return await response.json();
}
```

## Polling Implementation

```javascript
async function waitForJobCompletion(cacheKey, interval = 2000, timeout = 60000) {
  const startTime = Date.now();
  
  return new Promise((resolve, reject) => {
    const check = async () => {
      try {
        const { status, result } = await checkJobStatus(cacheKey);
        
        if (status === 'completed') {
          resolve(result);
        } else if (Date.now() - startTime > timeout) {
          reject(new Error('Job timed out'));
        } else {
          setTimeout(check, interval);
        }
      } catch (error) {
        reject(error);
      }
    };
    
    check();
  });
}
```

## React Hook Example

```jsx
import { useState, useEffect } from 'react';

function useAsyncJob(cacheKey) {
  const [status, setStatus] = useState('idle');
  const [result, setResult] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!cacheKey) return;

    const interval = setInterval(async () => {
      try {
        const response = await checkJobStatus(cacheKey);
        if (response.status === 'completed') {
          setStatus('completed');
          setResult(response.result);
          clearInterval(interval);
        }
      } catch (err) {
        setError(err);
        setStatus('failed');
        clearInterval(interval);
      }
    }, 2000);

    return () => clearInterval(interval);
  }, [cacheKey]);

  return { status, result, error };
}