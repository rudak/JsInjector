import { ValidationError } from '../errors.js';

/**
 * JavaScript reserved keywords that cannot be used as variable names
 * @type {Set<string>}
 */
const RESERVED_KEYWORDS = new Set([
  'arguments',
  'await',
  'break',
  'case',
  'catch',
  'class',
  'const',
  'continue',
  'debugger',
  'default',
  'delete',
  'do',
  'else',
  'enum',
  'eval',
  'export',
  'extends',
  'false',
  'finally',
  'for',
  'function',
  'if',
  'implements',
  'import',
  'in',
  'instanceof',
  'interface',
  'let',
  'new',
  'null',
  'package',
  'private',
  'protected',
  'public',
  'return',
  'static',
  'super',
  'switch',
  'this',
  'throw',
  'true',
  'try',
  'typeof',
  'var',
  'void',
  'while',
  'with',
  'yield',
]);

/**
 * Validates that a variable name is a valid JavaScript identifier
 * @param {string} name - The variable name to validate
 * @returns {boolean} True if valid, false otherwise
 */
export const isValidVariableName = (name) => {
  if (typeof name !== 'string' || name.length === 0) {
    return false;
  }
  // Check for reserved keywords
  if (RESERVED_KEYWORDS.has(name)) {
    return false;
  }
  // Valid JS identifier: starts with letter, underscore, or $, followed by letters, digits, underscores, or $
  const validIdentifierRegex = /^[a-zA-Z_$][a-zA-Z0-9_$]*$/;
  return validIdentifierRegex.test(name);
};

/**
 * Validates that all keys in an object are valid JavaScript identifiers
 * @param {Object} values - Object with variable names as keys
 * @returns {{valid: boolean, invalidKey: string|null}} Validation result
 */
export const validateVariableNames = (values) => {
  if (typeof values !== 'object' || values === null) {
    return { valid: false, invalidKey: null };
  }

  for (const key of Object.keys(values)) {
    if (!isValidVariableName(key)) {
      return { valid: false, invalidKey: key };
    }
  }

  return { valid: true, invalidKey: null };
};

/**
 * Validates that a URL is well-formed
 * @param {string} url - The URL to validate
 * @returns {boolean} True if valid, false otherwise
 */
export const isValidUrl = (url) => {
  if (typeof url !== 'string' || url.length === 0) {
    return false;
  }
  try {
    new URL(url);
    return true;
  } catch {
    return false;
  }
};

/**
 * Validates that a value can be serialized to JSON
 * @param {*} value - The value to check
 * @returns {boolean} True if serializable, false otherwise
 */
export const isJsonSerializable = (value) => {
  try {
    JSON.stringify(value);
    return true;
  } catch {
    return false;
  }
};

/**
 * Validates injection options
 * @param {Object} options - The options to validate
 * @throws {ValidationError} If options are invalid
 */
export const validateOptions = (options) => {
  if (options.timeout !== undefined) {
    if (typeof options.timeout !== 'number' || options.timeout <= 0) {
      throw new ValidationError('timeout must be a positive number', 'timeout');
    }
  }

  if (options.namespace !== undefined && options.namespace !== null) {
    if (!isValidVariableName(options.namespace)) {
      throw new ValidationError('namespace must be a valid JavaScript identifier', 'namespace');
    }
  }

  if (options.target !== undefined && options.target !== null) {
    if (typeof options.target !== 'object') {
      throw new ValidationError('target must be an object', 'target');
    }
  }
};
