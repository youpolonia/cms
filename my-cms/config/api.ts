/// <reference lib="dom" />

interface ApiConfig {
  baseUrl: string;
  localStorage: Storage;
}

const config: ApiConfig = {
  baseUrl: process.env.API_BASE_URL || 'http://localhost:3000/api',
  localStorage: typeof window !== 'undefined' ? window.localStorage : {
    getItem: () => null,
    setItem: () => {},
    removeItem: () => {}
  } as unknown as Storage
};

export function useApi() {
  return {
    get: async (path: string) => {
      const url = `${config.baseUrl}${path}`;
      const headers: Record<string, string> = {
        'Content-Type': 'application/json'
      };

      if (typeof window !== 'undefined') {
        const token = window.localStorage?.getItem('authToken');
        if (token) {
          headers['Authorization'] = `Bearer ${token}`;
        }
      }

      const response = await fetch(url, {
        method: 'GET',
        headers
      });

      if (!response.ok) {
        throw new Error(`API request failed: ${response.statusText}`);
      }

      return response.json();
    }
  };
}
