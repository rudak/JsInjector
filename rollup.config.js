import { defineConfig } from 'rollup';

const banner = `/**
 * JsInjector - inject PHP constants into the browser context
 * @module js-injector
 */`;

export default defineConfig({
  input: 'js-src/index.js',
  output: [
    {
      file: 'dist/index.esm.js',
      format: 'es',
      banner,
      sourcemap: true,
    },
    {
      file: 'dist/index.cjs.js',
      format: 'cjs',
      banner,
      sourcemap: true,
    },
  ],
});
