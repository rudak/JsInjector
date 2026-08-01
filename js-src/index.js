/**
 * JsInjector - Modern JavaScript library for injecting values into the browser context
 * @module js-injector
 */

// Export main injection API
export { inject, injectFromJson, injectFromDom, generateInjectionCode, remove } from './injector.js';

// Export loader utilities
export { loadScript, loadJson, loadFromScriptTag } from './loader.js';

// Export error classes
export { InjectionError, LoadError, TimeoutError, ValidationError } from './errors.js';

// Export utility functions
export {
  isValidVariableName,
  validateVariableNames,
  isValidUrl,
  isJsonSerializable,
  validateOptions,
  getVariableType,
  getArrayDepth,
  deepClone,
  toJsonString,
  parseJsonSafely,
} from './utils/index.js';
