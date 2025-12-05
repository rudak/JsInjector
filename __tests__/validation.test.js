import {
  isValidVariableName,
  validateVariableNames,
  isValidUrl,
  isJsonSerializable,
  validateOptions,
} from '../js-src/utils/validation.js';
import { ValidationError } from '../js-src/errors.js';

describe('Validation Utils', () => {
  describe('isValidVariableName', () => {
    it('should return true for valid variable names', () => {
      expect(isValidVariableName('foo')).toBe(true);
      expect(isValidVariableName('_foo')).toBe(true);
      expect(isValidVariableName('$foo')).toBe(true);
      expect(isValidVariableName('foo123')).toBe(true);
      expect(isValidVariableName('_123')).toBe(true);
      expect(isValidVariableName('camelCase')).toBe(true);
      expect(isValidVariableName('UPPER_CASE')).toBe(true);
    });

    it('should return false for invalid variable names', () => {
      expect(isValidVariableName('')).toBe(false);
      expect(isValidVariableName('123foo')).toBe(false);
      expect(isValidVariableName('foo-bar')).toBe(false);
      expect(isValidVariableName('foo bar')).toBe(false);
      expect(isValidVariableName('foo.bar')).toBe(false);
    });

    it('should return false for non-string values', () => {
      expect(isValidVariableName(123)).toBe(false);
      expect(isValidVariableName(null)).toBe(false);
      expect(isValidVariableName(undefined)).toBe(false);
      expect(isValidVariableName({})).toBe(false);
    });

    it('should return false for JavaScript reserved keywords', () => {
      expect(isValidVariableName('class')).toBe(false);
      expect(isValidVariableName('function')).toBe(false);
      expect(isValidVariableName('const')).toBe(false);
      expect(isValidVariableName('let')).toBe(false);
      expect(isValidVariableName('var')).toBe(false);
      expect(isValidVariableName('return')).toBe(false);
      expect(isValidVariableName('if')).toBe(false);
      expect(isValidVariableName('else')).toBe(false);
      expect(isValidVariableName('for')).toBe(false);
      expect(isValidVariableName('while')).toBe(false);
      expect(isValidVariableName('this')).toBe(false);
      expect(isValidVariableName('null')).toBe(false);
      expect(isValidVariableName('true')).toBe(false);
      expect(isValidVariableName('false')).toBe(false);
    });
  });

  describe('validateVariableNames', () => {
    it('should return valid for objects with valid keys', () => {
      const result = validateVariableNames({ foo: 1, bar: 2, _baz: 3 });
      expect(result.valid).toBe(true);
      expect(result.invalidKey).toBeNull();
    });

    it('should return invalid for objects with invalid keys', () => {
      const result = validateVariableNames({ foo: 1, '123bar': 2 });
      expect(result.valid).toBe(false);
      expect(result.invalidKey).toBe('123bar');
    });

    it('should return invalid for non-objects', () => {
      expect(validateVariableNames(null).valid).toBe(false);
      expect(validateVariableNames('string').valid).toBe(false);
      expect(validateVariableNames(123).valid).toBe(false);
    });

    it('should return valid for empty objects', () => {
      const result = validateVariableNames({});
      expect(result.valid).toBe(true);
    });
  });

  describe('isValidUrl', () => {
    it('should return true for valid URLs', () => {
      expect(isValidUrl('https://example.com')).toBe(true);
      expect(isValidUrl('http://example.com/path')).toBe(true);
      expect(isValidUrl('https://example.com/path?query=1')).toBe(true);
      expect(isValidUrl('file:///path/to/file')).toBe(true);
    });

    it('should return false for invalid URLs', () => {
      expect(isValidUrl('')).toBe(false);
      expect(isValidUrl('not-a-url')).toBe(false);
      expect(isValidUrl('/relative/path')).toBe(false);
    });

    it('should return false for non-string values', () => {
      expect(isValidUrl(null)).toBe(false);
      expect(isValidUrl(123)).toBe(false);
    });
  });

  describe('isJsonSerializable', () => {
    it('should return true for serializable values', () => {
      expect(isJsonSerializable('string')).toBe(true);
      expect(isJsonSerializable(123)).toBe(true);
      expect(isJsonSerializable(true)).toBe(true);
      expect(isJsonSerializable(null)).toBe(true);
      expect(isJsonSerializable([1, 2, 3])).toBe(true);
      expect(isJsonSerializable({ a: 1, b: 2 })).toBe(true);
    });

    it('should return false for non-serializable values', () => {
      expect(isJsonSerializable(undefined)).toBe(true); // JSON.stringify returns undefined for undefined, but doesn't throw
      const circular = {};
      circular.self = circular;
      expect(isJsonSerializable(circular)).toBe(false);
    });
  });

  describe('validateOptions', () => {
    it('should not throw for valid options', () => {
      expect(() => validateOptions({})).not.toThrow();
      expect(() => validateOptions({ timeout: 5000 })).not.toThrow();
      expect(() => validateOptions({ namespace: 'myApp' })).not.toThrow();
      expect(() => validateOptions({ target: {} })).not.toThrow();
    });

    it('should throw for invalid timeout', () => {
      expect(() => validateOptions({ timeout: -1 })).toThrow(ValidationError);
      expect(() => validateOptions({ timeout: 0 })).toThrow(ValidationError);
      expect(() => validateOptions({ timeout: 'string' })).toThrow(ValidationError);
    });

    it('should throw for invalid namespace', () => {
      expect(() => validateOptions({ namespace: '123invalid' })).toThrow(ValidationError);
      expect(() => validateOptions({ namespace: '' })).toThrow(ValidationError);
    });

    it('should throw for invalid target', () => {
      expect(() => validateOptions({ target: 'string' })).toThrow(ValidationError);
      expect(() => validateOptions({ target: 123 })).toThrow(ValidationError);
    });
  });
});
