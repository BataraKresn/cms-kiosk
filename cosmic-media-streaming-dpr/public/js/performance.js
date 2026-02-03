/**
 * Performance optimization utilities
 * Reduces unnecessary API calls dan improves frontend responsiveness
 */

(function() {
    'use strict';

    /**
     * Debounce function - limits how often a function can be called
     * Useful untuk: refresh calls, autocomplete, form submissions
     */
    window.debounce = function(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    };

    /**
     * Throttle function - ensures function runs at most once per interval
     * Useful untuk: scroll events, resize handlers
     */
    window.throttle = function(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    };

    /**
     * Cache buster - adds timestamp untuk force fresh data when needed
     */
    window.cacheBuster = function(url) {
        const separator = url.includes('?') ? '&' : '?';
        return url + separator + 't=' + Date.now();
    };

    /**
     * Simple request deduplication
     * Prevents duplicate simultaneous API calls
     */
    window.RequestDedup = {
        pending: new Map(),
        
        async fetch(key, fn) {
            if (this.pending.has(key)) {
                return this.pending.get(key);
            }
            
            const promise = fn().finally(() => {
                this.pending.delete(key);
            });
            
            this.pending.set(key, promise);
            return promise;
        }
    };

    /**
     * Performance monitoring
     * Logs API response times di console
     */
    window.logPerformance = function(label, fn) {
        const start = performance.now();
        const result = fn();
        
        if (result instanceof Promise) {
            return result.finally(() => {
                const time = (performance.now() - start).toFixed(2);
                console.log(`⏱️  ${label}: ${time}ms`);
            });
        }
        
        const time = (performance.now() - start).toFixed(2);
        console.log(`⏱️  ${label}: ${time}ms`);
        return result;
    };

    /**
     * Batch DOM updates untuk avoid reflows
     */
    window.batchDOM = function(fn) {
        if (window.requestAnimationFrame) {
            requestAnimationFrame(fn);
        } else {
            fn();
        }
    };

    console.log('✅ Performance utilities loaded');
})();
