import { useMCPTool } from '../composables/useMCP';

/**
 * MCP-backed analytics cache service
 */
export default class MCPAnalyticsCacheService {
    constructor() {
        this.mcp = useMCPTool('cms-knowledge-server');
        this.cacheConfig = {
            default_ttl: 86400 * 1000, // 24 hours
            types: {
                approval_analytics: { min: 300000, max: 86400000, default: 3600000 },
                time_spent: { min: 3600000, max: 604800000, default: 86400000 },
                user_segments: { min: 3600000, max: 604800000, default: 86400000 },
                scroll_depth: { min: 3600000, max: 604800000, default: 86400000 },
                version_comparison: { min: 300000, max: 86400000, default: 1800000 }
            }
        };
    }

    /**
     * Store data in MCP cache
     * @param {string} key - Cache key
     * @param {object} data - Data to cache
     * @param {number} [ttl] - Optional TTL in milliseconds
     */
    async set(key, data, ttl) {
        const cacheType = this.detectCacheType(key);
        ttl = ttl || this.cacheConfig.types[cacheType]?.default || this.cacheConfig.default_ttl;
        
        const cacheData = {
            data,
            expiresAt: Date.now() + ttl,
            cachedAt: Date.now()
        };

        try {
            await this.mcp.cache_file({
                path: `analytics_cache/${key}`,
                content: JSON.stringify(cacheData)
            });
        } catch (error) {
            console.error('MCP cache_file error:', error);
        }
    }

    /**
     * Get cached data from MCP
     * @param {string} key - Cache key
     * @returns {Promise<object|null>} Cached data or null if expired/missing
     */
    async get(key) {
        try {
            const response = await this.mcp.get_cached_file({
                path: `analytics_cache/${key}`
            });
            
            if (!response) return null;
            
            const entry = JSON.parse(response);
            if (Date.now() > entry.expiresAt) {
                await this.clear(key);
                return null;
            }
            return entry.data;
        } catch (error) {
            console.error('MCP get_cached_file error:', error);
            return null;
        }
    }

    /**
     * Check if valid cache exists for key
     * @param {string} key - Cache key
     * @returns {Promise<boolean>} True if valid cache exists
     */
    async has(key) {
        const data = await this.get(key);
        return data !== null;
    }

    /**
     * Clear cache for specific key or all cache
     * @param {string} [key] - Optional key to clear
     */
    async clear(key) {
        try {
            if (key) {
                await this.mcp.cache_file({
                    path: `analytics_cache/${key}`,
                    content: null // Setting to null deletes the cache
                });
            } else {
                // Clear all analytics cache
                const cacheList = await this.mcp.access_mcp_resource({
                    uri: 'cms://knowledge/cached-files'
                });
                
                const analyticsCache = cacheList.filter(file => 
                    file.path.startsWith('analytics_cache/')
                );
                
                await Promise.all(analyticsCache.map(file => 
                    this.mcp.cache_file({
                        path: file.path,
                        content: null
                    })
                ));
            }
        } catch (error) {
            console.error('MCP cache clear error:', error);
        }
    }

    /**
     * Invalidate cache entries matching a pattern
     * @param {RegExp} pattern - Regex pattern to match keys against
     */
    async invalidateMatching(pattern) {
        try {
            const cacheList = await this.mcp.access_mcp_resource({
                uri: 'cms://knowledge/cached-files'
            });
            
            const matchingKeys = cacheList
                .filter(file => file.path.startsWith('analytics_cache/'))
                .map(file => file.path.replace('analytics_cache/', ''))
                .filter(key => pattern.test(key));
            
            await Promise.all(matchingKeys.map(key => this.clear(key)));
        } catch (error) {
            console.error('MCP invalidateMatching error:', error);
        }
    }

    /**
     * Detect cache type from key
     * @param {string} key - Cache key
     * @returns {string} Detected cache type
     */
    detectCacheType(key) {
        if (key.includes('approval_analytics')) return 'approval_analytics';
        if (key.includes('time_spent')) return 'time_spent';
        if (key.includes('user_segments')) return 'user_segments';
        if (key.includes('scroll_depth')) return 'scroll_depth';
        if (key.includes('version_comparison')) return 'version_comparison';
        return 'time_spent'; // Default fallback
    }

    /**
     * Get cache metadata
     * @param {string} key - Cache key
     * @returns {Promise<object|null>} Cache metadata or null if missing
     */
    async getMetadata(key) {
        try {
            const response = await this.mcp.get_cached_file({
                path: `analytics_cache/${key}`
            });
            
            if (!response) return null;
            
            const entry = JSON.parse(response);
            const now = Date.now();
            return {
                cachedAt: entry.cachedAt,
                expiresAt: entry.expiresAt,
                ttl: entry.expiresAt - entry.cachedAt,
                isExpired: now > entry.expiresAt,
                remaining: Math.max(0, entry.expiresAt - now)
            };
        } catch (error) {
            console.error('MCP getMetadata error:', error);
            return null;
        }
    }
}
