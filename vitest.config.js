import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    globals: true,
    environment: 'node',
    include: ['__tests__/**/*.test.js'],
    coverage: {
      provider: 'v8',
      include: ['js-src/**/*.js'],
      exclude: ['js-src/index.js'],
      thresholds: {
        branches: 70,
        functions: 70,
        lines: 70,
        statements: 70,
      },
    },
  },
});
