import { LoadError, TimeoutError } from './errors.js';
import { isValidUrl } from './utils/validation.js';

/**
 * Default loader options
 * @type {Object}
 */
const DEFAULT_OPTIONS = {
  timeout: 10000,
  async: true,
  defer: false,
};

/**
 * Loads a script from a URL and injects it into the document
 * @param {string} url - The URL of the script to load
 * @param {Object} [options] - Loader options
 * @param {number} [options.timeout=10000] - Timeout in milliseconds
 * @param {boolean} [options.async=true] - Whether to load async
 * @param {boolean} [options.defer=false] - Whether to defer loading
 * @returns {Promise<HTMLScriptElement>} Resolves with the script element
 * @throws {LoadError} If the URL is invalid or script fails to load
 * @throws {TimeoutError} If loading times out
 */
export const loadScript = (url, options = {}) => {
  const opts = { ...DEFAULT_OPTIONS, ...options };

  return new Promise((resolve, reject) => {
    if (!isValidUrl(url)) {
      reject(new LoadError(`Invalid URL: ${url}`, url));
      return;
    }

    const script = document.createElement('script');
    script.src = url;
    script.async = opts.async;
    script.defer = opts.defer;

    let timeoutId = null;

    const cleanup = () => {
      if (timeoutId) {
        clearTimeout(timeoutId);
      }
      script.onload = null;
      script.onerror = null;
    };

    script.onload = () => {
      cleanup();
      resolve(script);
    };

    script.onerror = () => {
      cleanup();
      reject(new LoadError(`Failed to load script: ${url}`, url));
    };

    if (opts.timeout > 0) {
      timeoutId = setTimeout(() => {
        cleanup();
        script.remove();
        reject(new TimeoutError(`Script load timed out after ${opts.timeout}ms`, opts.timeout));
      }, opts.timeout);
    }

    document.head.appendChild(script);
  });
};

/**
 * Loads a JSON resource from a URL
 * @param {string} url - The URL of the JSON resource
 * @param {Object} [options] - Loader options
 * @param {number} [options.timeout=10000] - Timeout in milliseconds
 * @returns {Promise<Object>} Resolves with the parsed JSON data
 * @throws {LoadError} If the URL is invalid or resource fails to load
 * @throws {TimeoutError} If loading times out
 */
export const loadJson = async (url, options = {}) => {
  const opts = { ...DEFAULT_OPTIONS, ...options };

  if (!isValidUrl(url)) {
    throw new LoadError(`Invalid URL: ${url}`, url);
  }

  const controller = new AbortController();
  let timeoutId = null;

  if (opts.timeout > 0) {
    timeoutId = setTimeout(() => {
      controller.abort();
    }, opts.timeout);
  }

  try {
    const response = await fetch(url, { signal: controller.signal });

    if (!response.ok) {
      throw new LoadError(`Failed to load JSON: ${response.status} ${response.statusText}`, url);
    }

    return await response.json();
  } catch (error) {
    if (error.name === 'AbortError') {
      throw new TimeoutError(`JSON load timed out after ${opts.timeout}ms`, opts.timeout);
    }
    throw new LoadError(`Failed to load JSON: ${error.message}`, url);
  } finally {
    if (timeoutId) {
      clearTimeout(timeoutId);
    }
  }
};

/**
 * Loads values from a JSON string embedded in a script tag
 * @param {string} selector - CSS selector for the script tag
 * @returns {Object|null} Parsed JSON data or null if not found
 */
export const loadFromScriptTag = (selector) => {
  const scriptTag = document.querySelector(selector);

  if (!scriptTag) {
    return null;
  }

  const content = scriptTag.textContent?.trim();

  if (!content) {
    return null;
  }

  try {
    return JSON.parse(content);
  } catch {
    return null;
  }
};
