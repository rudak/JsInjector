/**
 * @jest-environment jsdom
 */

import { jest } from '@jest/globals';
import { loadScript, loadJson, loadFromScriptTag } from '../js-src/loader.js';
import { LoadError, TimeoutError } from '../js-src/errors.js';

describe('Loader', () => {
  describe('loadScript', () => {
    beforeEach(() => {
      document.head.innerHTML = '';
    });

    it('should reject with LoadError for invalid URL', async () => {
      await expect(loadScript('not-a-valid-url')).rejects.toThrow(LoadError);
    });

    it('should reject with LoadError for empty URL', async () => {
      await expect(loadScript('')).rejects.toThrow(LoadError);
    });

    it('should create a script element with correct attributes', async () => {
      // We can't actually load scripts in jest, but we can test the setup
      const promise = loadScript('https://example.com/script.js', { timeout: 100 });

      // Give it a moment to create the script element
      await new Promise((r) => setTimeout(r, 10));

      const scripts = document.querySelectorAll('script');
      expect(scripts.length).toBeGreaterThan(0);

      const script = scripts[scripts.length - 1];
      expect(script.src).toBe('https://example.com/script.js');
      expect(script.async).toBe(true);

      // Clean up - the promise will timeout
      await expect(promise).rejects.toThrow(TimeoutError);
    });

    it('should respect async and defer options', async () => {
      const promise = loadScript('https://example.com/script.js', {
        async: false,
        defer: true,
        timeout: 100,
      });

      await new Promise((r) => setTimeout(r, 10));

      const scripts = document.querySelectorAll('script');
      const script = scripts[scripts.length - 1];
      expect(script.async).toBe(false);
      expect(script.defer).toBe(true);

      await expect(promise).rejects.toThrow(TimeoutError);
    });
  });

  describe('loadJson', () => {
    beforeEach(() => {
      global.fetch = jest.fn();
    });

    afterEach(() => {
      jest.resetAllMocks();
    });

    it('should reject with LoadError for invalid URL', async () => {
      await expect(loadJson('not-a-valid-url')).rejects.toThrow(LoadError);
    });

    it('should fetch and parse JSON successfully', async () => {
      const mockData = { foo: 1, bar: 'hello' };
      global.fetch.mockResolvedValue({
        ok: true,
        json: () => Promise.resolve(mockData),
      });

      const result = await loadJson('https://example.com/data.json');

      expect(result).toEqual(mockData);
      expect(global.fetch).toHaveBeenCalledWith(
        'https://example.com/data.json',
        expect.objectContaining({ signal: expect.any(AbortSignal) })
      );
    });

    it('should throw LoadError for non-ok response', async () => {
      global.fetch.mockResolvedValue({
        ok: false,
        status: 404,
        statusText: 'Not Found',
      });

      await expect(loadJson('https://example.com/data.json')).rejects.toThrow(LoadError);
    });

    it('should throw TimeoutError when fetch is aborted', async () => {
      global.fetch.mockRejectedValue(new DOMException('Aborted', 'AbortError'));

      await expect(loadJson('https://example.com/data.json', { timeout: 100 })).rejects.toThrow(
        TimeoutError
      );
    });
  });

  describe('loadFromScriptTag', () => {
    beforeEach(() => {
      document.body.innerHTML = '';
    });

    it('should return null when script tag is not found', () => {
      const result = loadFromScriptTag('#non-existent');
      expect(result).toBeNull();
    });

    it('should parse JSON from script tag', () => {
      document.body.innerHTML = `
        <script id="data" type="application/json">{"foo": 1, "bar": "hello"}</script>
      `;

      const result = loadFromScriptTag('#data');
      expect(result).toEqual({ foo: 1, bar: 'hello' });
    });

    it('should return null for empty script tag', () => {
      document.body.innerHTML = '<script id="data" type="application/json"></script>';

      const result = loadFromScriptTag('#data');
      expect(result).toBeNull();
    });

    it('should return null for invalid JSON', () => {
      document.body.innerHTML = '<script id="data" type="application/json">invalid json</script>';

      const result = loadFromScriptTag('#data');
      expect(result).toBeNull();
    });
  });
});
