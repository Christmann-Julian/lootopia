import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: './src/setupTests.ts',
    include: ['src/_tests_/**/*.{test,spec}.{js,ts,jsx,tsx}'],
  },
});
