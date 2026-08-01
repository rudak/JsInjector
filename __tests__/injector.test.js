import { inject, injectFromJson, generateInjectionCode, remove } from '../js-src/injector.js';
import { InjectionError, ValidationError } from '../js-src/errors.js';

describe('Injector', () => {
  let testTarget;

  beforeEach(() => {
    testTarget = {};
  });

  describe('inject', () => {
    it('should inject values into target object', () => {
      const result = inject({ foo: 1, bar: 'hello' }, { target: testTarget });

      expect(result.success).toBe(true);
      expect(result.injected).toEqual(['foo', 'bar']);
      expect(result.errors).toEqual([]);
      expect(testTarget.foo).toBe(1);
      expect(testTarget.bar).toBe('hello');
    });

    it('should inject nested objects', () => {
      const values = { config: { api: 'url', timeout: 5000 } };
      const result = inject(values, { target: testTarget });

      expect(result.success).toBe(true);
      expect(testTarget.config).toEqual({ api: 'url', timeout: 5000 });
    });

    it('should inject arrays', () => {
      const values = { items: [1, 2, 3] };
      const result = inject(values, { target: testTarget });

      expect(result.success).toBe(true);
      expect(testTarget.items).toEqual([1, 2, 3]);
    });

    it('should use namespace when provided', () => {
      const result = inject({ foo: 1 }, { target: testTarget, namespace: 'myApp' });

      expect(result.success).toBe(true);
      expect(testTarget.myApp.foo).toBe(1);
    });

    it('should create namespace if it does not exist', () => {
      inject({ foo: 1 }, { target: testTarget, namespace: 'newNamespace' });

      expect(testTarget.newNamespace).toBeDefined();
      expect(testTarget.newNamespace.foo).toBe(1);
    });

    it('should freeze values when freeze option is true', () => {
      inject({ config: { a: 1 } }, { target: testTarget, freeze: true });

      expect(Object.isFrozen(testTarget.config)).toBe(true);
    });

    it('should not overwrite existing values when overwrite is false', () => {
      testTarget.foo = 'original';
      const result = inject({ foo: 'new' }, { target: testTarget, overwrite: false });

      expect(testTarget.foo).toBe('original');
      expect(result.errors.length).toBe(1);
      expect(result.errors[0].key).toBe('foo');
    });

    it('should overwrite existing values by default', () => {
      testTarget.foo = 'original';
      const result = inject({ foo: 'new' }, { target: testTarget });

      expect(testTarget.foo).toBe('new');
      expect(result.success).toBe(true);
    });

    it('should throw ValidationError for non-object values', () => {
      expect(() => inject(null, { target: testTarget })).toThrow(ValidationError);
      expect(() => inject('string', { target: testTarget })).toThrow(ValidationError);
    });

    it('should throw ValidationError for invalid variable names', () => {
      expect(() => inject({ '123invalid': 1 }, { target: testTarget })).toThrow(ValidationError);
    });

    it('should deep clone injected values', () => {
      const original = { nested: { value: 1 } };
      inject({ config: original.nested }, { target: testTarget });

      testTarget.config.value = 999;
      expect(original.nested.value).toBe(1);
    });

    it('should handle empty objects', () => {
      const result = inject({}, { target: testTarget });

      expect(result.success).toBe(true);
      expect(result.injected).toEqual([]);
    });
  });

  describe('injectFromJson', () => {
    it('should inject values from valid JSON string', () => {
      const result = injectFromJson('{"foo": 1, "bar": "hello"}', { target: testTarget });

      expect(result.success).toBe(true);
      expect(testTarget.foo).toBe(1);
      expect(testTarget.bar).toBe('hello');
    });

    it('should throw InjectionError for invalid JSON', () => {
      expect(() => injectFromJson('invalid json', { target: testTarget })).toThrow(InjectionError);
    });

    it('should support all inject options', () => {
      injectFromJson('{"foo": 1}', { target: testTarget, namespace: 'app' });

      expect(testTarget.app.foo).toBe(1);
    });
  });

  describe('generateInjectionCode', () => {
    it('should generate an ES module by default', () => {
      const code = generateInjectionCode({ foo: 1, bar: 'hello' });

      expect(code).toContain('export default');
      expect(code).toContain('foo');
      expect(code).toContain('bar');
      expect(code).toContain('"foo":1');
    });

    it('should generate a named export when namespace is provided', () => {
      const code = generateInjectionCode({ foo: 1 }, { namespace: 'myApp' });

      expect(code).toContain('export const myApp');
      expect(code).toContain('"foo":1');
    });

    it('should generate globals when format is globals', () => {
      const code = generateInjectionCode({ foo: 1, bar: 'hello' }, { format: 'globals' });

      expect(code).toContain('INJECTED_VALUES');
      expect(code).toContain('var { foo, bar } = INJECTED_VALUES;');
    });

    it('should generate a namespace variable when format is globals and namespace provided', () => {
      const code = generateInjectionCode({ foo: 1 }, { format: 'globals', namespace: 'myApp' });

      expect(code).toContain('var myApp');
      expect(code).toContain('"foo":1');
    });

    it('should return empty string for empty objects', () => {
      const code = generateInjectionCode({});
      expect(code).toBe('');
    });

    it('should throw ValidationError for invalid values', () => {
      expect(() => generateInjectionCode(null)).toThrow(ValidationError);
      expect(() => generateInjectionCode({ '123invalid': 1 })).toThrow(ValidationError);
    });

    it('should throw ValidationError for invalid format', () => {
      expect(() => generateInjectionCode({ foo: 1 }, { format: 'invalid' })).toThrow(ValidationError);
    });

    it('should escape closing script tags in JSON content', () => {
      const code = generateInjectionCode({ message: '</script>' });
      expect(code).not.toContain('</script>');
      expect(code).toContain('\\u003C/script>');
    });
  });

  describe('remove', () => {
    it('should remove injected values', () => {
      testTarget.foo = 1;
      testTarget.bar = 2;

      const removed = remove(['foo', 'bar'], { target: testTarget });

      expect(removed).toEqual(['foo', 'bar']);
      expect('foo' in testTarget).toBe(false);
      expect('bar' in testTarget).toBe(false);
    });

    it('should only return keys that were actually removed', () => {
      testTarget.foo = 1;

      const removed = remove(['foo', 'nonexistent'], { target: testTarget });

      expect(removed).toEqual(['foo']);
    });

    it('should remove from namespace when provided', () => {
      testTarget.myApp = { foo: 1, bar: 2 };

      const removed = remove(['foo'], { target: testTarget, namespace: 'myApp' });

      expect(removed).toEqual(['foo']);
      expect('foo' in testTarget.myApp).toBe(false);
      expect(testTarget.myApp.bar).toBe(2);
    });

    it('should handle non-existent namespace gracefully', () => {
      const removed = remove(['foo'], { target: testTarget, namespace: 'nonexistent' });

      expect(removed).toEqual([]);
    });
  });
});
