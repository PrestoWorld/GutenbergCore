import { defineConfig } from 'vite';
import { resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('.', import.meta.url));

/**
 * Builds the Site Editor (Context Editor) JS/CSS into `dist/`.
 *
 * Output contract consumed by SiteEditorController::asset():
 *   dist/main.js  -> /site-editor/assets/main.js
 *   dist/main.css -> /site-editor/assets/main.css
 */
export default defineConfig({
  root: resolve(root, 'editor'),
  base: '/site-editor/assets/',
  build: {
    outDir: resolve(root, 'dist'),
    emptyOutDir: true,
    target: 'es2020',
    cssCodeSplit: false,
    manifest: true,
    rollupOptions: {
      input: resolve(root, 'editor/main.js'),
      output: {
        entryFileNames: 'main.js',
        chunkFileNames: '[name].js',
        assetFileNames: (info) => {
          const name = info.names?.[0] ?? info.name ?? '';
          return name.endsWith('.css') ? 'main.css' : 'assets/[name][extname]';
        },
      },
    },
  },
});
