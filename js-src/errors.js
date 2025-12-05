/**
 * Custom error for injection failures
 * @extends Error
 */
export class InjectionError extends Error {
  /**
   * Creates an InjectionError
   * @param {string} message - Error message
   * @param {string} [code] - Optional error code
   */
  constructor(message, code = 'INJECTION_ERROR') {
    super(message);
    this.name = 'InjectionError';
    this.code = code;
  }
}

/**
 * Error thrown when a resource cannot be loaded
 * @extends Error
 */
export class LoadError extends Error {
  /**
   * Creates a LoadError
   * @param {string} message - Error message
   * @param {string} [url] - The URL that failed to load
   */
  constructor(message, url = null) {
    super(message);
    this.name = 'LoadError';
    this.url = url;
  }
}

/**
 * Error thrown when a timeout occurs
 * @extends Error
 */
export class TimeoutError extends Error {
  /**
   * Creates a TimeoutError
   * @param {string} message - Error message
   * @param {number} [timeout] - The timeout duration in milliseconds
   */
  constructor(message, timeout = null) {
    super(message);
    this.name = 'TimeoutError';
    this.timeout = timeout;
  }
}

/**
 * Error thrown when validation fails
 * @extends Error
 */
export class ValidationError extends Error {
  /**
   * Creates a ValidationError
   * @param {string} message - Error message
   * @param {string} [field] - The field that failed validation
   */
  constructor(message, field = null) {
    super(message);
    this.name = 'ValidationError';
    this.field = field;
  }
}
