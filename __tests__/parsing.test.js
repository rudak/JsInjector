import {
  getVariableType,
  getArrayDepth,
  deepClone,
  toJsonString,
  parseJsonSafely,
} from '../js-src/utils/parsing.js';

describe('Parsing Utils', () => {
  describe('getVariableType', () => {
    it('should return correct type for primitives', () => {
      expect(getVariableType('string')).toBe('string');
      expect(getVariableType(123)).toBe('number');
      expect(getVariableType(true)).toBe('boolean');
      expect(getVariableType(undefined)).toBe('undefined');
    });

    it('should return "null" for null', () => {
      expect(getVariableType(null)).toBe('null');
    });

    it('should return detailed info for arrays', () => {
      expect(getVariableType([1, 2, 3])).toBe('array [Elements: 3, Max depth: 1]');
      expect(getVariableType([])).toBe('array [Elements: 0, Max depth: 1]');
    });

    it('should return detailed info for nested arrays', () => {
      expect(
        getVariableType([
          [1, 2],
          [3, 4],
        ])
      ).toBe('array [Elements: 2, Max depth: 2]');
      expect(getVariableType([[[1]]])).toBe('array [Elements: 1, Max depth: 3]');
    });

    it('should return detailed info for objects', () => {
      expect(getVariableType({ a: 1, b: 2 })).toBe('object [Keys: 2]');
      expect(getVariableType({})).toBe('object [Keys: 0]');
    });
  });

  describe('getArrayDepth', () => {
    it('should return 0 for non-arrays', () => {
      expect(getArrayDepth('string')).toBe(0);
      expect(getArrayDepth(123)).toBe(0);
      expect(getArrayDepth({})).toBe(0);
    });

    it('should return 1 for flat arrays', () => {
      expect(getArrayDepth([1, 2, 3])).toBe(1);
      expect(getArrayDepth([])).toBe(1);
    });

    it('should return correct depth for nested arrays', () => {
      expect(getArrayDepth([[1]])).toBe(2);
      expect(getArrayDepth([[[1]]])).toBe(3);
      expect(getArrayDepth([[1], [[2]]])).toBe(3);
    });
  });

  describe('deepClone', () => {
    it('should clone primitive values', () => {
      expect(deepClone('string')).toBe('string');
      expect(deepClone(123)).toBe(123);
      expect(deepClone(true)).toBe(true);
      expect(deepClone(null)).toBe(null);
    });

    it('should return undefined for undefined', () => {
      expect(deepClone(undefined)).toBe(undefined);
    });

    it('should create independent copies of objects', () => {
      const original = { a: 1, b: { c: 2 } };
      const cloned = deepClone(original);

      expect(cloned).toEqual(original);
      expect(cloned).not.toBe(original);
      expect(cloned.b).not.toBe(original.b);

      cloned.b.c = 999;
      expect(original.b.c).toBe(2);
    });

    it('should create independent copies of arrays', () => {
      const original = [1, [2, 3]];
      const cloned = deepClone(original);

      expect(cloned).toEqual(original);
      expect(cloned).not.toBe(original);

      cloned[1][0] = 999;
      expect(original[1][0]).toBe(2);
    });
  });

  describe('toJsonString', () => {
    it('should convert values to JSON strings', () => {
      expect(toJsonString({ a: 1 })).toBe('{"a":1}');
      expect(toJsonString([1, 2, 3])).toBe('[1,2,3]');
      expect(toJsonString('string')).toBe('"string"');
    });
  });

  describe('parseJsonSafely', () => {
    it('should parse valid JSON', () => {
      const result = parseJsonSafely('{"a":1}');
      expect(result.success).toBe(true);
      expect(result.data).toEqual({ a: 1 });
      expect(result.error).toBeNull();
    });

    it('should handle invalid JSON', () => {
      const result = parseJsonSafely('invalid json');
      expect(result.success).toBe(false);
      expect(result.data).toBeNull();
      expect(result.error).toBeInstanceOf(Error);
    });

    it('should parse arrays', () => {
      const result = parseJsonSafely('[1,2,3]');
      expect(result.success).toBe(true);
      expect(result.data).toEqual([1, 2, 3]);
    });
  });
});
