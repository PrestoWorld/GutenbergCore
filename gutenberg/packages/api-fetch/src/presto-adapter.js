/**
 * presto-api-adapter.js — PrestoWorld API adapter for Gutenberg.
 *
 * Replaces @wordpress/api-fetch with PrestoWorld API endpoints:
 *   - /wp/v2/ → /pw-api/v1/
 *   - WP nonces → PrestoWorld nonces
 *   - WP REST → PW REST
 *
 * This file is injected into the Gutenberg build via webpack.presto.js
 * to make Gutenberg work with PrestoWorld backend.
 */

(function () {
  'use strict';

  const PRESTO_API = (typeof process !== 'undefined' && process.env && process.env.PRESTO_API) || '/pw-api/v1';

  /**
   * PrestoWorld API fetch adapter.
   * Wraps the original apiFetch to redirect WP REST calls to PW REST.
   */
  function createPrestoApiFetch(originalApiFetch) {
    return function prestoApiFetch(options) {
      // Normalize options
      if (typeof options === 'string') {
        options = { path: options };
      }

      // Redirect WP REST API paths to PrestoWorld API
      if (options.path) {
        options.path = redirectPath(options.path);
      }

      // Add PrestoWorld nonce
      if (typeof window !== 'undefined' && window.prestoWorldNonce) {
        options.headers = options.headers || {};
        options.headers['X-PrestoWorld-Nonce'] = window.prestoWorldNonce;
        options.headers['X-PrestoWorld-API'] = '1';
      }

      // Call original apiFetch
      return originalApiFetch(options);
    };
  }

  /**
   * Redirect WordPress REST API paths to PrestoWorld API paths.
   *
   * @param {string} path
   * @return {string}
   */
  function redirectPath(path) {
    // Already a PW API path
    if (path.startsWith('/pw-api/') || path.startsWith(PRESTO_API)) {
      return path;
    }

    // Redirect /wp/v2/ → /pw-api/v1/
    if (path.startsWith('/wp/v2/')) {
      return path.replace('/wp/v2/', '/pw-api/v1/');
    }

    // Redirect /wp-json/wp/v2/ → /pw-api/v1/
    if (path.startsWith('/wp-json/wp/v2/')) {
      return path.replace('/wp-json/wp/v2/', '/pw-api/v1/');
    }

    // Redirect /wp-json/ → /pw-api/v1/
    if (path.startsWith('/wp-json/')) {
      return path.replace('/wp-json/', '/pw-api/v1/');
    }

    // Default: prepend PW API prefix
    if (path.startsWith('/')) {
      return PRESTO_API + path;
    }

    return path;
  }

  /**
   * PrestoWorld data module adapter.
   * Overrides core-data to use PW API for entity operations.
   */
  function createPrestoDataAdapter() {
    return {
      // Entity kinds supported by PrestoWorld
      getEntityKinds: function () {
        return [
          { kind: 'postType', name: 'post' },
          { kind: 'postType', name: 'page' },
          { kind: 'taxonomy', name: 'category' },
          { kind: 'taxonomy', name: 'post_tag' },
        ];
      },

      // Get entity records from PW API
      getEntityRecords: function (kind, name, query) {
        const path = buildEntityPath(kind, name, query);
        return fetch(path)
          .then(function (res) { return res.json(); })
          .then(function (data) { return data; });
      },

      // Save entity to PW API
      saveEntityRecord: function (kind, name, record, options) {
        const path = buildEntityPath(kind, name, { id: record.id });
        const method = record.id ? 'PUT' : 'POST';
        return fetch(path, {
          method: method,
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(record),
        }).then(function (res) { return res.json(); });
      },

      // Delete entity from PW API
      deleteEntityRecord: function (kind, name, id) {
        const path = buildEntityPath(kind, name, { id: id });
        return fetch(path, { method: 'DELETE' })
          .then(function (res) { return res.json(); });
      },
    };
  }

  /**
   * Build entity API path.
   */
  function buildEntityPath(kind, name, query) {
    let path = PRESTO_API + '/' + kind + 's/' + name;
    if (query && query.id) {
      path += '/' + query.id;
    }
    return path;
  }

  // Export for webpack
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
      createPrestoApiFetch: createPrestoApiFetch,
      redirectPath: redirectPath,
      createPrestoDataAdapter: createPrestoDataAdapter,
      PRESTO_API: PRESTO_API,
    };
  }

  // Also expose globally for non-module usage
  if (typeof window !== 'undefined') {
    window.prestoApiAdapter = {
      createPrestoApiFetch: createPrestoApiFetch,
      redirectPath: redirectPath,
      createPrestoDataAdapter: createPrestoDataAdapter,
      PRESTO_API: PRESTO_API,
    };
  }
})();