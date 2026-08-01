/**
 * @vitest-environment jsdom
 */

import { describe, expect, it, beforeEach } from 'vitest';
import { injectFromDom } from '../js-src/injector.js';

describe('injectFromDom', () => {
  beforeEach(() => {
    document.body.innerHTML = '';
  });

  const addJsonTag = (id, json) => {
    const tag = document.createElement('script');
    tag.type = 'application/json';
    tag.id = id;
    tag.textContent = json;
    document.body.appendChild(tag);
  };

  it('should return null when no script tag is found', () => {
    expect(injectFromDom('#js-injector-dynamic')).toBeNull();
  });

  it('should inject values found in the JSON script tag', () => {
    addJsonTag('js-injector-dynamic', '{"API_URL":"https://api.example.com","DEBUG":true}');

    const result = injectFromDom('#js-injector-dynamic', { target: globalThis });

    expect(result).not.toBeNull();
    expect(result.success).toBe(true);
    expect(result.injected).toContain('API_URL');
    expect(globalThis.API_URL).toBe('https://api.example.com');
    expect(globalThis.DEBUG).toBe(true);
  });

  it('should decode hex-escaped content', () => {
    addJsonTag('js-injector-dynamic', '{"MESSAGE":"\\u003C/script>","URL":"https:\\/\\/api.example.com"}');

    const result = injectFromDom('#js-injector-dynamic');

    expect(result).not.toBeNull();
    expect(globalThis.MESSAGE).toBe('</script>');
    expect(globalThis.URL).toBe('https://api.example.com');
  });

  it('should pass options to inject', () => {
    addJsonTag('js-injector-dynamic', '{"timeout":5000}');

    injectFromDom('#js-injector-dynamic', { namespace: 'MyApp' });

    expect(globalThis.MyApp.timeout).toBe(5000);
  });
});
