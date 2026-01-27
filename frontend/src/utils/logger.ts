// Frontend logging utility for capturing errors and debug information

interface LogEntry {
  timestamp: string;
  level: 'ERROR' | 'WARNING' | 'INFO' | 'DEBUG';
  message: string;
  context?: any;
}

interface ApiError {
  timestamp: string;
  url: string;
  method: string;
  status: number;
  error: string;
  request_data?: any;
  response_data?: any;
}

class FrontendLogger {
  private maxLogEntries = 1000;
  private maxApiErrors = 500;

  private getStoredLogs(): LogEntry[] {
    try {
      const stored = localStorage.getItem('kuantan188_frontend_logs');
      return stored ? JSON.parse(stored) : [];
    } catch (e) {
      return [];
    }
  }

  private getStoredApiErrors(): ApiError[] {
    try {
      const stored = localStorage.getItem('kuantan188_api_errors');
      return stored ? JSON.parse(stored) : [];
    } catch (e) {
      return [];
    }
  }

  private saveLogEntry(entry: LogEntry): void {
    try {
      const logs = this.getStoredLogs();
      logs.push(entry);
      
      // Keep only the last N entries
      if (logs.length > this.maxLogEntries) {
        logs.splice(0, logs.length - this.maxLogEntries);
      }
      
      localStorage.setItem('kuantan188_frontend_logs', JSON.stringify(logs));
    } catch (e) {
      console.error('Failed to save log entry:', e);
    }
  }

  private saveApiError(error: ApiError): void {
    try {
      const errors = this.getStoredApiErrors();
      errors.push(error);
      
      // Keep only the last N errors
      if (errors.length > this.maxApiErrors) {
        errors.splice(0, errors.length - this.maxApiErrors);
      }
      
      localStorage.setItem('kuantan188_api_errors', JSON.stringify(errors));
    } catch (e) {
      console.error('Failed to save API error:', e);
    }
  }

  // Log levels
  error(message: string, context?: any): void {
    const entry: LogEntry = {
      timestamp: new Date().toISOString(),
      level: 'ERROR',
      message,
      context
    };
    
    console.error(`[FRONTEND ERROR] ${message}`, context);
    this.saveLogEntry(entry);
  }

  warning(message: string, context?: any): void {
    const entry: LogEntry = {
      timestamp: new Date().toISOString(),
      level: 'WARNING',
      message,
      context
    };
    
    console.warn(`[FRONTEND WARNING] ${message}`, context);
    this.saveLogEntry(entry);
  }

  info(message: string, context?: any): void {
    const entry: LogEntry = {
      timestamp: new Date().toISOString(),
      level: 'INFO',
      message,
      context
    };
    
    console.log(`[FRONTEND INFO] ${message}`, context);
    this.saveLogEntry(entry);
  }

  debug(message: string, context?: any): void {
    const entry: LogEntry = {
      timestamp: new Date().toISOString(),
      level: 'DEBUG',
      message,
      context
    };
    
    console.log(`[FRONTEND DEBUG] ${message}`, context);
    this.saveLogEntry(entry);
  }

  // API Error logging
  logApiError(
    url: string,
    method: string,
    status: number,
    error: string,
    requestData?: any,
    responseData?: any
  ): void {
    const apiError: ApiError = {
      timestamp: new Date().toISOString(),
      url,
      method,
      status,
      error,
      request_data: requestData,
      response_data: responseData
    };

    console.error(`[API ERROR] ${method} ${url} - ${status}: ${error}`, {
      requestData,
      responseData
    });

    this.saveApiError(apiError);
  }

  // Payment specific logging
  logPaymentError(
    step: string,
    error: string,
    context?: any
  ): void {
    this.error(`Payment Error - ${step}: ${error}`, {
      step,
      ...context
    });
  }

  logPaymentStart(paymentMethod: string, amount: number, context?: any): void {
    this.info(`Payment Started - Method: ${paymentMethod}, Amount: ${amount}`, {
      paymentMethod,
      amount,
      ...context
    });
  }

  logPaymentSuccess(paymentMethod: string, amount: number, bookingId: number): void {
    this.info(`Payment Success - Method: ${paymentMethod}, Amount: ${amount}, Booking: ${bookingId}`, {
      paymentMethod,
      amount,
      bookingId
    });
  }

  // Get logs for debugging
  getLogs(): LogEntry[] {
    return this.getStoredLogs();
  }

  getApiErrors(): ApiError[] {
    return this.getStoredApiErrors();
  }

  // Clear logs
  clearLogs(): void {
    localStorage.removeItem('kuantan188_frontend_logs');
    localStorage.removeItem('kuantan188_api_errors');
  }
}

// Create singleton instance
export const frontendLogger = new FrontendLogger();
export default frontendLogger;