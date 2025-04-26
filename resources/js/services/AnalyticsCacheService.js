/**
 * Service for caching analytics data with expiration
 */
export default class AnalyticsCacheService {
    constructor() {
        this.cache = new Map();
        this.configLoaded = false;
        this.cacheConfig = {
            default_ttl: 86400 * 1000, // 24 hours
            ttl_milliseconds: false,
            types: {
                approval_analytics: { min: 300000, max: 86400000, default: 3600000 },
                time_spent: { min: 3600000, max: 604800000, default: 86400000 },
                user_segments: { min: 3600000, max: 604800000, default: 86400000 },
                scroll_depth: { min: 3600000, max: 604800000, default: 86400000 },
                version_comparison: { min: 300000, max: 86400000, default: 1800000 },
                template_usage: { min: 300000, max: 86400000, default: 3600000 }
            }
        };
        
        // Clean up expired entries every 5 minutes
        this.cleanupInterval = setInterval(() => this.cleanupExpired(), 5 * 60 * 1000);
    }

    /**
     * Clean up expired cache entries
     */
    cleanupExpired() {
        const now = Date.now();
        Array.from(this.cache.entries()).forEach(([key, entry]) => {
            if (now > entry.expiresAt) {
                this.cache.delete(key);
            }
        });
    }

    /**
     * Store data in cache with optional TTL
     * @param {string} key - Cache key
     * @param {object} data - Data to cache
     * @param {number} [ttl] - Optional TTL in milliseconds
     */
    set(key, data, ttl) {
        // Determine TTL based on cache type if not provided
        if (typeof ttl === 'undefined') {
            const cacheType = this.detectCacheType(key);
            ttl = this.defaultTTLs[cacheType] || this.defaultTTLs.time_spent;
        }
        const now = Date.now();
        this.cache.set(key, {
            data,
            expiresAt: now + ttl,
            cachedAt: now
        });
    }

    /**
     * Get cached data if valid
     * @param {string} key - Cache key
     * @returns {object|null} Cached data or null if expired/missing
     */
    get(key) {
        const entry = this.cache.get(key);
        if (!entry || Date.now() > entry.expiresAt) {
            this.cache.delete(key);
            return null;
        }
        return entry.data;
    }

    /**
     * Check if valid cache exists for key
     * @param {string} key - Cache key
     * @returns {boolean} True if valid cache exists
     */
    has(key) {
        const entry = this.cache.get(key);
        return entry && Date.now() <= entry.expiresAt;
    }

    /**
     * Clear cache for specific key, array of keys, or all cache
     * @param {string|Array} [keys] - Optional key(s) to clear
     */
    clear(keys) {
        if (!keys) {
            this.cache.clear();
        } else if (Array.isArray(keys)) {
            keys.forEach(key => this.cache.delete(key));
        } else {
            this.cache.delete(keys);
        }
    }

    /**
     * Get all cache entries (for cleanup purposes)
     * @returns {Array} Array of cache entries with metadata
     */
    entries() {
        return Array.from(this.cache.entries()).map(([key, entry]) => ({
            key,
            ...entry
        }));
    }

    /**
     * Invalidate cache entries matching a pattern
     * @param {RegExp} pattern - Regex pattern to match keys against
     */
    invalidateMatching(pattern) {
        Array.from(this.cache.keys()).forEach(key => {
            if (pattern.test(key)) {
                this.cache.delete(key);
            }
        });
    }

    /**
     * Invalidate all cache entries
     */
    invalidateAll() {
        this.cache.clear();
    }

    /**
     * Get cache metadata
     * @param {string} key - Cache key
     * @returns {object|null} Cache metadata or null if missing
     */
    getMetadata(key) {
        const entry = this.cache.get(key);
        if (!entry) return null;
        
        const now = Date.now();
        return {
            cachedAt: entry.cachedAt,
            expiresAt: entry.expiresAt,
            ttl: entry.expiresAt - entry.cachedAt,
            isExpired: now > entry.expiresAt,
            remaining: Math.max(0, entry.expiresAt - now)
        };
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
        if (key.includes('template_usage')) return 'template_usage';
        return 'time_spent'; // Default fallback
    }
}
