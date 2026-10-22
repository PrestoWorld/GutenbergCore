/**
 * presto-boot.js — PrestoWorld mount entry for the WordPress Site Editor.
 *
 * Bundles `initializeEditor` together with `@wordpress/api-fetch` from the
 * same module graph, so the host page can configure REST middlewares on the
 * exact instance the editor uses (no reliance on `window.wp.apiFetch`).
 *
 * Exposed as `window.wp.presto` (see webpack.presto.js `library` config).
 */

import apiFetch from '@wordpress/api-fetch';
import { initializeEditor, store } from './index';

let apiConfigured = false;

/**
 * Configure api-fetch middlewares for the host REST namespace.
 *
 * @param {Object} options
 * @param {string} options.rootUrl API root, e.g. `/wp-json/`.
 * @param {string} options.nonce   WP REST nonce (`X-WP-Nonce`).
 */
export function configureApiFetch( { rootUrl, nonce } = {} ) {
	if ( apiConfigured ) {
		return;
	}
	if ( typeof rootUrl === 'string' && rootUrl.length > 0 ) {
		apiFetch.use( apiFetch.createRootURLMiddleware( rootUrl ) );
	}
	if ( typeof nonce === 'string' && nonce.length > 0 ) {
		apiFetch.use( apiFetch.createNonceMiddleware( nonce ) );
	}
	apiConfigured = true;
}

/**
 * Boot the Site Editor into `id`.
 *
 * @param {string} id       DOM id of the mount element.
 * @param {Object} settings Editor settings (edit-site store).
 * @param {Object} options  { rootUrl, nonce }.
 * @return {import('react').Root} React root.
 */
export function mountSiteEditor( id, settings = {}, options = {} ) {
	configureApiFetch( options );
	return initializeEditor( id, settings );
}

export { apiFetch, initializeEditor, store };
export default { configureApiFetch, mountSiteEditor, initializeEditor, store };
