import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

import MiniCssExtractPlugin from 'mini-css-extract-plugin';

const root = dirname(fileURLToPath(import.meta.url));

/**
 * Builds the Site Editor (Context Editor) JS/CSS into `dist/`.
 *
 * Output contract consumed by SiteEditorController::asset():
 *   dist/main.js  -> /site-editor/assets/main.js
 *   dist/main.css -> /site-editor/assets/main.css
 */
export default (_env, argv) => {
  const isProduction = argv.mode === 'production';

  return {
    entry: resolve(root, 'editor/main.js'),
    mode: isProduction ? 'production' : 'development',
    target: ['web', 'es2020'],
    devtool: isProduction ? false : 'source-map',
    output: {
      path: resolve(root, 'dist'),
      filename: 'main.js',
      clean: true,
    },
    module: {
      rules: [
        {
          test: /\.css$/,
          use: [MiniCssExtractPlugin.loader, 'css-loader'],
        },
      ],
    },
    plugins: [
      new MiniCssExtractPlugin({ filename: 'main.css' }),
    ],
    performance: { hints: false },
  };
};
