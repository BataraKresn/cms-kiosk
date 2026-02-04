/**
 * Connection State Manager for Remote Control
 * 
 * Manages WebSocket connection states, auto-reconnection, and error handling
 * with a clean state machine approach.
 * 
 * @author Cosmic Development Team
 * @version 2.0.0
 */

class ConnectionStateManager {
    /**
     * Connection states enum
     */
    static States = {
        DISCONNECTED: 'disconnected',
        CONNECTING: 'connecting',
        CONNECTED: 'connected',
        RECONNECTING: 'reconnecting',
        ERROR: 'error'
    };

    /**
     * Error types
     */
    static ErrorTypes = {
        AUTH_FAILED: 'auth_failed',
        DEVICE_OFFLINE: 'device_offline',
        TIMEOUT: 'timeout',
        NETWORK_ERROR: 'network_error',
        UNKNOWN: 'unknown'
    };

    constructor(config = {}) {
        // Configuration
        this.maxReconnectAttempts = config.maxReconnectAttempts || 5;
        this.reconnectDelayMs = config.reconnectDelayMs || 3000;
        this.reconnectBackoffMultiplier = config.reconnectBackoffMultiplier || 1.5;
        this.maxReconnectDelayMs = config.maxReconnectDelayMs || 30000;
        this.autoReconnect = config.autoReconnect !== false;
        
        // State
        this.currentState = ConnectionStateManager.States.DISCONNECTED;
        this.previousState = null;
        this.reconnectAttempts = 0;
        this.currentReconnectDelay = this.reconnectDelayMs;
        this.lastError = null;
        this.reconnectTimerId = null;
        this.countdownTimerId = null;
        this.countdownSeconds = 0;
        
        // Callbacks
        this.onStateChange = config.onStateChange || (() => {});
        this.onReconnectCountdown = config.onReconnectCountdown || (() => {});
        this.onReconnectAttempt = config.onReconnectAttempt || (() => {});
        this.onMaxReconnectAttemptsReached = config.onMaxReconnectAttemptsReached || (() => {});
    }

    /**
     * Get current connection state
     */
    getState() {
        return this.currentState;
    }

    /**
     * Check if currently connected
     */
    isConnected() {
        return this.currentState === ConnectionStateManager.States.CONNECTED;
    }

    /**
     * Check if can send commands (only when connected)
     */
    canSendCommands() {
        return this.isConnected();
    }

    /**
     * Transition to a new state
     */
    setState(newState, error = null) {
        if (this.currentState === newState && !error) {
            return; // No change
        }

        console.log(`🔄 State transition: ${this.currentState} → ${newState}`);
        
        this.previousState = this.currentState;
        this.currentState = newState;
        this.lastError = error;

        // Clear any pending reconnect timers
        this.clearReconnectTimer();

        // Handle state-specific logic
        switch (newState) {
            case ConnectionStateManager.States.CONNECTED:
                this.reconnectAttempts = 0;
                this.currentReconnectDelay = this.reconnectDelayMs;
                break;

            case ConnectionStateManager.States.DISCONNECTED:
            case ConnectionStateManager.States.ERROR:
                if (this.autoReconnect && this.reconnectAttempts < this.maxReconnectAttempts) {
                    this.scheduleReconnect();
                } else if (this.reconnectAttempts >= this.maxReconnectAttempts) {
                    console.error('❌ Max reconnection attempts reached');
                    this.onMaxReconnectAttemptsReached();
                }
                break;
        }

        // Notify listeners
        this.onStateChange(newState, this.lastError);
    }

    /**
     * Handle successful connection
     */
    handleConnected() {
        this.setState(ConnectionStateManager.States.CONNECTED);
    }

    /**
     * Handle disconnection
     */
    handleDisconnected(error = null) {
        this.setState(ConnectionStateManager.States.DISCONNECTED, error);
    }

    /**
     * Handle connection error
     */
    handleError(errorType, errorMessage) {
        const error = {
            type: errorType,
            message: errorMessage,
            timestamp: Date.now()
        };
        this.setState(ConnectionStateManager.States.ERROR, error);
    }

    /**
     * Schedule automatic reconnection
     */
    scheduleReconnect() {
        if (!this.autoReconnect) {
            return;
        }

        console.log(`⏱️ Scheduling reconnect attempt ${this.reconnectAttempts + 1}/${this.maxReconnectAttempts} in ${this.currentReconnectDelay}ms`);

        // Set state to reconnecting
        this.setState(ConnectionStateManager.States.RECONNECTING);

        // Start countdown
        this.countdownSeconds = Math.ceil(this.currentReconnectDelay / 1000);
        this.startCountdown();

        // Schedule reconnection
        this.reconnectTimerId = setTimeout(() => {
            this.reconnectAttempts++;
            this.attemptReconnect();
        }, this.currentReconnectDelay);

        // Calculate next delay with exponential backoff
        this.currentReconnectDelay = Math.min(
            this.currentReconnectDelay * this.reconnectBackoffMultiplier,
            this.maxReconnectDelayMs
        );
    }

    /**
     * Start countdown timer for UI feedback
     */
    startCountdown() {
        this.clearCountdownTimer();
        
        this.onReconnectCountdown(this.countdownSeconds);
        
        this.countdownTimerId = setInterval(() => {
            this.countdownSeconds--;
            
            if (this.countdownSeconds >= 0) {
                this.onReconnectCountdown(this.countdownSeconds);
            } else {
                this.clearCountdownTimer();
            }
        }, 1000);
    }

    /**
     * Clear countdown timer
     */
    clearCountdownTimer() {
        if (this.countdownTimerId) {
            clearInterval(this.countdownTimerId);
            this.countdownTimerId = null;
        }
    }

    /**
     * Attempt to reconnect
     */
    attemptReconnect() {
        console.log(`🔌 Attempting reconnection (${this.reconnectAttempts}/${this.maxReconnectAttempts})...`);
        this.onReconnectAttempt(this.reconnectAttempts);
    }

    /**
     * Manually trigger reconnection
     */
    manualReconnect() {
        console.log('🔄 Manual reconnection triggered');
        this.reconnectAttempts = 0;
        this.currentReconnectDelay = this.reconnectDelayMs;
        this.clearReconnectTimer();
        this.setState(ConnectionStateManager.States.CONNECTING);
    }

    /**
     * Clear reconnect timer
     */
    clearReconnectTimer() {
        if (this.reconnectTimerId) {
            clearTimeout(this.reconnectTimerId);
            this.reconnectTimerId = null;
        }
        this.clearCountdownTimer();
    }

    /**
     * Manually disconnect (stops auto-reconnect)
     */
    manualDisconnect() {
        console.log('🔌 Manual disconnect');
        this.autoReconnect = false;
        this.clearReconnectTimer();
        this.setState(ConnectionStateManager.States.DISCONNECTED);
    }

    /**
     * Reset connection manager
     */
    reset() {
        this.reconnectAttempts = 0;
        this.currentReconnectDelay = this.reconnectDelayMs;
        this.clearReconnectTimer();
        this.lastError = null;
        this.autoReconnect = true;
    }

    /**
     * Get user-friendly error message
     */
    getErrorMessage() {
        if (!this.lastError) {
            return 'Connection lost';
        }

        switch (this.lastError.type) {
            case ConnectionStateManager.ErrorTypes.AUTH_FAILED:
                return 'Authentication failed - Invalid credentials';
            
            case ConnectionStateManager.ErrorTypes.DEVICE_OFFLINE:
                return 'Device is offline or unavailable';
            
            case ConnectionStateManager.ErrorTypes.TIMEOUT:
                return 'Connection timeout - No response from device';
            
            case ConnectionStateManager.ErrorTypes.NETWORK_ERROR:
                return 'Network error - Check your connection';
            
            default:
                return this.lastError.message || 'Unknown connection error';
        }
    }

    /**
     * Get reconnect status for UI display
     */
    getReconnectStatus() {
        return {
            isReconnecting: this.currentState === ConnectionStateManager.States.RECONNECTING,
            attempt: this.reconnectAttempts,
            maxAttempts: this.maxReconnectAttempts,
            countdown: this.countdownSeconds,
            canRetry: this.reconnectAttempts < this.maxReconnectAttempts
        };
    }

    /**
     * Cleanup resources
     */
    destroy() {
        this.clearReconnectTimer();
        this.onStateChange = () => {};
        this.onReconnectCountdown = () => {};
        this.onReconnectAttempt = () => {};
        this.onMaxReconnectAttemptsReached = () => {};
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ConnectionStateManager;
}
