/**
 * webpack.presto.js — Gutenberg fork build config for PrestoWorld.
 *
 * Builds Gutenberg editor for PrestoWorld's WP-compatible REST backend:
 *   - REST API: /wp-json/ root + /wp/v2/* namespace (WP core contract)
 *   - Authentication: `X-WP-Nonce` from window.wp.presto / host page
 *   - React is bundled (no UMD globals) so the page needs no React runtime
 *
 * Usage:
 *   NODE_ENV=production webpack --config webpack.presto.js
 *   webpack --watch --config webpack.presto.js
 */

const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

const PRESTO_API = process.env.PRESTO_API || '/pw-api/v1';
const IS_PRODUCTION = process.env.NODE_ENV === 'production';

module.exports = {
  context: __dirname,
  mode: IS_PRODUCTION ? 'production' : 'development',
  devtool: IS_PRODUCTION ? 'source-map' : 'eval-source-map',

  entry: {
    'editor': './packages/edit-post/src/index',
    'edit-site': './packages/edit-site/src/index',
    'presto': './packages/edit-site/src/presto-boot',
    'presto-styles': './presto-styles',
    'blocks': './packages/block-library/src/index',
    'block-editor': './packages/block-editor/src/index',
    'components': './packages/components/src/index',
    'data': './packages/data/src/index',
    'api-fetch': './packages/api-fetch/src/index',
    'core-data': './packages/core-data/src/index',
  },

  output: {
    path: path.resolve(__dirname, '../../../../public/assets/gutenberg'),
    filename: IS_PRODUCTION ? '[name].min.js' : '[name].js',
    chunkFilename: IS_PRODUCTION ? '[name].min.js' : '[name].js',
    publicPath: '/assets/gutenberg/',
    library: ['wp', '[name]'],
    libraryTarget: 'window',
    globalObject: 'window',
  },

  resolve: {
    extensions: ['.js', '.jsx', '.ts', '.tsx', '.json'],
    alias: {
      // scss entries normally resolved from each package's build-style output
      '@wordpress/dataviews/build-style/style.css': path.resolve(__dirname, 'packages/dataviews/src/style.scss'),
      '@wordpress/fields/build-style/style.css': path.resolve(__dirname, 'packages/fields/src/style.scss'),
      '@wordpress/global-styles-ui/build-style/style.css': path.resolve(__dirname, 'packages/global-styles-ui/src/style.scss'),
      '@wordpress/interface/build-style/style.css': path.resolve(__dirname, 'packages/interface/src/style.scss'),
      '@wordpress/media-fields/build-style/style.css': path.resolve(__dirname, 'packages/media-fields/src/style.scss'),
      '@wordpress/api-fetch': path.resolve(__dirname, 'packages/api-fetch/src/index'),
      '@wordpress/core-data': path.resolve(__dirname, 'packages/core-data/src/index'),
      '@wordpress/data': path.resolve(__dirname, 'packages/data/src/index'),
      '@wordpress/blocks': path.resolve(__dirname, 'packages/blocks/src/index'),
      '@wordpress/block-editor': path.resolve(__dirname, 'packages/block-editor/src/index'),
      '@wordpress/components': path.resolve(__dirname, 'packages/components/src/index'),
      '@wordpress/edit-post': path.resolve(__dirname, 'packages/edit-post/src/index'),
      '@wordpress/edit-site': path.resolve(__dirname, 'packages/edit-site/src/index'),
      '@wordpress/editor': path.resolve(__dirname, 'packages/editor/src/index'),
      '@wordpress/element': path.resolve(__dirname, 'packages/element/src/index'),
      '@wordpress/hooks': path.resolve(__dirname, 'packages/hooks/src/index'),
      '@wordpress/i18n': path.resolve(__dirname, 'packages/i18n/src/index'),
      '@wordpress/url': path.resolve(__dirname, 'packages/url/src/index'),
      '@wordpress/html-entities': path.resolve(__dirname, 'packages/html-entities/src/index'),
      '@wordpress/escape-html': path.resolve(__dirname, 'packages/escape-html/src/index'),
      '@wordpress/keycodes': path.resolve(__dirname, 'packages/keycodes/src/index'),
      '@wordpress/compose': path.resolve(__dirname, 'packages/compose/src/index'),
      '@wordpress/primitives': path.resolve(__dirname, 'packages/primitives/src/index'),
      '@wordpress/icons': path.resolve(__dirname, 'packages/icons/src/index'),
      '@wordpress/notices': path.resolve(__dirname, 'packages/notices/src/index'),
      '@wordpress/interface': path.resolve(__dirname, 'packages/interface/src/index'),
      '@wordpress/preferences': path.resolve(__dirname, 'packages/preferences/src/index'),
      '@wordpress/rich-text': path.resolve(__dirname, 'packages/rich-text/src/index'),
      '@wordpress/date': path.resolve(__dirname, 'packages/date/src/index'),
      '@wordpress/viewport': path.resolve(__dirname, 'packages/viewport/src/index'),
      '@wordpress/wordcount': path.resolve(__dirname, 'packages/wordcount/src/index'),
      '@wordpress/blob': path.resolve(__dirname, 'packages/blob/src/index'),
      '@wordpress/media-utils': path.resolve(__dirname, 'packages/media-utils/src/index'),
      '@wordpress/block-serialization-default-parser': path.resolve(__dirname, 'packages/block-serialization-default-parser/src/index'),
      '@wordpress/block-serialization-spec-parser': path.resolve(__dirname, 'packages/block-serialization-spec-parser/src/index'),
      '@wordpress/dom': path.resolve(__dirname, 'packages/dom/src/index'),
      '@wordpress/dom-ready': path.resolve(__dirname, 'packages/dom-ready/src/index'),
      '@wordpress/hooks': path.resolve(__dirname, 'packages/hooks/src/index'),
      '@wordpress/redux-routine': path.resolve(__dirname, 'packages/redux-routine/src/index'),
      '@wordpress/token-list': path.resolve(__dirname, 'packages/token-list/src/index'),
      '@wordpress/is-shallow-equal': path.resolve(__dirname, 'packages/is-shallow-equal/src/index'),
      '@wordpress/kebab-case': path.resolve(__dirname, 'packages/kebab-case/src/index'),
      '@wordpress/priority-queue': path.resolve(__dirname, 'packages/priority-queue/src/index'),
      '@wordpress/shortcode': path.resolve(__dirname, 'packages/shortcode/src/index'),
      '@wordpress/a11y': path.resolve(__dirname, 'packages/a11y/src/index'),
      '@wordpress/annotations': path.resolve(__dirname, 'packages/annotations/src/index'),
      '@wordpress/autop': path.resolve(__dirname, 'packages/autop/src/index'),
      '@wordpress/deprecated': path.resolve(__dirname, 'packages/deprecated/src/index'),
      '@wordpress/warning': path.resolve(__dirname, 'packages/warning/src/index'),
      '@wordpress/keyboard-shortcuts': path.resolve(__dirname, 'packages/keyboard-shortcuts/src/index'),
      '@wordpress/commands': path.resolve(__dirname, 'packages/commands/src/index'),
      '@wordpress/dataviews': path.resolve(__dirname, 'packages/dataviews/src/index'),
      '@wordpress/fields': path.resolve(__dirname, 'packages/fields/src/index'),
      '@wordpress/grid': path.resolve(__dirname, 'packages/grid/src/index'),
      '@wordpress/views': path.resolve(__dirname, 'packages/views/src/index'),
      '@wordpress/abilities': path.resolve(__dirname, 'packages/abilities/src/index'),
      '@wordpress/core-abilities': path.resolve(__dirname, 'packages/core-abilities/src/index'),
      '@wordpress/core-commands': path.resolve(__dirname, 'packages/core-commands/src/index'),
      '@wordpress/connectors': path.resolve(__dirname, 'packages/connectors/src/index'),
      '@wordpress/data-controls': path.resolve(__dirname, 'packages/data-controls/src/index'),
      '@wordpress/interactivity': path.resolve(__dirname, 'packages/interactivity/src/index'),
      '@wordpress/interactivity-router': path.resolve(__dirname, 'packages/interactivity-router/src/index'),
      '@wordpress/style-runtime': path.resolve(__dirname, 'packages/style-runtime/src/index'),
      '@wordpress/base-styles/animations': path.resolve(__dirname, 'packages/base-styles/_animations.scss'),
      '@wordpress/base-styles/breakpoints': path.resolve(__dirname, 'packages/base-styles/_breakpoints.scss'),
      '@wordpress/base-styles/colors': path.resolve(__dirname, 'packages/base-styles/_colors.scss'),
      '@wordpress/base-styles/default-custom-properties': path.resolve(__dirname, 'packages/base-styles/_default-custom-properties.scss'),
      '@wordpress/base-styles/functions': path.resolve(__dirname, 'packages/base-styles/_functions.scss'),
      '@wordpress/base-styles/long-content-fade': path.resolve(__dirname, 'packages/base-styles/_long-content-fade.scss'),
      '@wordpress/base-styles/mixins': path.resolve(__dirname, 'packages/base-styles/_mixins.scss'),
      '@wordpress/base-styles/variables': path.resolve(__dirname, 'packages/base-styles/_variables.scss'),
      '@wordpress/base-styles/z-index': path.resolve(__dirname, 'packages/base-styles/_z-index.scss'),
      '@wordpress/widget-primitives': path.resolve(__dirname, 'packages/widget-primitives/src/index'),
      '@wordpress/preferences-persistence': path.resolve(__dirname, 'packages/preferences-persistence/src/index'),
      '@wordpress/undo-manager': path.resolve(__dirname, 'packages/undo-manager/src/index'),
      '@wordpress/react-i18n': path.resolve(__dirname, 'packages/react-i18n/src/index'),
      '@wordpress/react-inert-value': path.resolve(__dirname, 'packages/react-inert-value/src/index'),
      '@wordpress/private-apis': path.resolve(__dirname, 'packages/private-apis/src/index'),
      '@wordpress/e2e-test-utils-playwright': path.resolve(__dirname, 'packages/e2e-test-utils-playwright/src/index'),
      '@wordpress/element': path.resolve(__dirname, 'packages/element/src/index'),
      '@wordpress/admin-ui': path.resolve(__dirname, 'packages/admin-ui/src/index'),
      '@wordpress/block-library': path.resolve(__dirname, 'packages/block-library/src/index'),
      '@wordpress/global-styles-engine': path.resolve(__dirname, 'packages/global-styles-engine/src/index'),
      '@wordpress/global-styles-ui': path.resolve(__dirname, 'packages/global-styles-ui/src/index'),
      '@wordpress/image-cropper': path.resolve(__dirname, 'packages/image-cropper/src/index'),
      '@wordpress/latex-to-mathml': path.resolve(__dirname, 'packages/latex-to-mathml/src/index'),
      '@wordpress/media-editor': path.resolve(__dirname, 'packages/media-editor/src/index'),
      '@wordpress/media-fields': path.resolve(__dirname, 'packages/media-fields/src/index'),
      '@wordpress/patterns': path.resolve(__dirname, 'packages/patterns/src/index'),
      '@wordpress/plugins': path.resolve(__dirname, 'packages/plugins/src/index'),
      '@wordpress/route': path.resolve(__dirname, 'packages/route/src/index'),
      '@wordpress/router': path.resolve(__dirname, 'packages/router/src/index'),
      '@wordpress/server-side-render': path.resolve(__dirname, 'packages/server-side-render/src/index'),
      '@wordpress/style-engine': path.resolve(__dirname, 'packages/style-engine/src/index'),
      '@wordpress/sync': path.resolve(__dirname, 'packages/sync/src/index'),
      '@wordpress/theme': path.resolve(__dirname, 'packages/theme/src/index'),
      '@wordpress/ui': path.resolve(__dirname, 'packages/ui/src/index'),
      '@wordpress/upload-media': path.resolve(__dirname, 'packages/upload-media/src/index'),
      '@wordpress/widgets': path.resolve(__dirname, 'packages/widgets/src/index'),
      '@wordpress/worker-threads': path.resolve(__dirname, 'packages/worker-threads/src/index'),
      '@wordpress/vips/worker': path.resolve(__dirname, 'packages/vips/src/vips-worker.ts'),
      '@wordpress/vips': path.resolve(__dirname, 'packages/vips/src/index'),
      '@wordpress/video-conversion/worker': path.resolve(__dirname, 'packages/video-conversion/src/video-conversion-worker.ts'),
      '@wordpress/video-conversion': path.resolve(__dirname, 'packages/video-conversion/src/index'),
    },
  },

  module: {
    rules: [
      {
        test: /\.(js|jsx|ts|tsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              ['@babel/preset-env', { targets: '> 0.25%, not dead' }],
              '@babel/preset-react',
              '@babel/preset-typescript',
            ],
            plugins: [
              '@babel/plugin-transform-runtime',
            ],
          },
        },
      },
      {
        test: /\.css$/,
        use: [MiniCssExtractPlugin.loader, 'css-loader'],
      },
      {
        test: /\.scss$/,
        use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
      },
      {
        test: /\.(png|jpg|jpeg|gif|svg|webp)$/,
        type: 'asset/resource',
      },
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/,
        type: 'asset/resource',
      },
    ],
  },

  plugins: [
    new MiniCssExtractPlugin({
      filename: '[name].css',
      chunkFilename: '[name].css',
    }),
    new webpack.DefinePlugin({
      'process.env.PRESTO_API': JSON.stringify(PRESTO_API),
      'process.env.IS_GUTENBERG_PLUGIN': JSON.stringify('true'),
      'process.env.GUTENBERG_VERSION': JSON.stringify('24.2.0-presto'),
    }),
    new webpack.ProvidePlugin({
      React: 'react',
      ReactDOM: 'react-dom',
    }),
  ],

  optimization: {
    minimize: IS_PRODUCTION,
    splitChunks: {
      chunks: 'all',
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendor',
          chunks: 'all',
        },
        common: {
          name: 'common',
          minChunks: 2,
          chunks: 'all',
          enforce: true,
        },
      },
    },
    runtimeChunk: 'single',
  },

  // React is bundled into every entry: React 19 ships no UMD build, and a
  // host-provided React instance would mismatch the bundled react-dom/client.

  performance: {
    hints: IS_PRODUCTION ? 'warning' : false,
    maxAssetSize: 500000,
    maxEntrypointSize: 500000,
  },

  stats: {
    colors: true,
    modules: false,
    children: false,
    chunks: false,
    chunkModules: false,
  },
};