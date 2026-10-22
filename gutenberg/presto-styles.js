/**
 * presto-styles.js — single stylesheet entry for the Site Editor page.
 *
 * The webpack build extracts CSS into real files (mini-css-extract-plugin),
 * so this entry exists purely to pull every package stylesheet the WordPress
 * Site Editor needs; the page links the emitted `presto-styles.css` before
 * any editor JS runs (same cascade as WP's `wp_edit_site` style dependency
 * tree: components → block-editor → editor → edit-site).
 *
 * Packages whose styles are already `@use`d by an entry below (interface,
 * dataviews, fields, global-styles-ui, media-editor) are not imported twice.
 */

import './packages/components/src/style.scss';
import './packages/notices/src/style.scss';
import './packages/preferences/src/style.scss';
import './packages/commands/src/style.scss';
import './packages/block-library/src/style.scss';
import './packages/block-editor/src/style.scss';
import './packages/patterns/src/style.scss';
import './packages/format-library/src/style.scss';
import './packages/editor/src/style.scss';
import './packages/edit-site/src/style.scss';
