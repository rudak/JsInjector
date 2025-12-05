import { InjectionError, LoadError, TimeoutError, ValidationError } from '../js-src/errors.js';

describe('Errors', () => {
  describe('InjectionError', () => {
    it('should create an error with message and default code', () => {
      const error = new InjectionError('test message');
      expect(error.message).toBe('test message');
      expect(error.name).toBe('InjectionError');
      expect(error.code).toBe('INJECTION_ERROR');
    });

    it('should create an error with custom code', () => {
      const error = new InjectionError('test message', 'CUSTOM_CODE');
      expect(error.code).toBe('CUSTOM_CODE');
    });

    it('should be an instance of Error', () => {
      const error = new InjectionError('test');
      expect(error).toBeInstanceOf(Error);
    });
  });

  describe('LoadError', () => {
    it('should create an error with message and default url', () => {
      const error = new LoadError('test message');
      expect(error.message).toBe('test message');
      expect(error.name).toBe('LoadError');
      expect(error.url).toBeNull();
    });

    it('should create an error with custom url', () => {
      const error = new LoadError('test message', 'https://example.com/script.js');
      expect(error.url).toBe('https://example.com/script.js');
    });
  });

  describe('TimeoutError', () => {
    it('should create an error with message and default timeout', () => {
      const error = new TimeoutError('test message');
      expect(error.message).toBe('test message');
      expect(error.name).toBe('TimeoutError');
      expect(error.timeout).toBeNull();
    });

    it('should create an error with custom timeout', () => {
      const error = new TimeoutError('test message', 5000);
      expect(error.timeout).toBe(5000);
    });
  });

  describe('ValidationError', () => {
    it('should create an error with message and default field', () => {
      const error = new ValidationError('test message');
      expect(error.message).toBe('test message');
      expect(error.name).toBe('ValidationError');
      expect(error.field).toBeNull();
    });

    it('should create an error with custom field', () => {
      const error = new ValidationError('test message', 'username');
      expect(error.field).toBe('username');
    });
  });
});
