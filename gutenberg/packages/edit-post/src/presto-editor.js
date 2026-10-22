/**
 * presto-editor.js — PrestoWorld Gutenberg Editor entry point.
 *
 * This is the main entry point for the PrestoWorld Gutenberg editor.
 * It initializes the Gutenberg editor with PrestoWorld API adapters.
 *
 * Usage:
 *   Load this script in the PrestoWorld admin dashboard to get
 *   the full Gutenberg editing experience.
 */

(function () {
  'use strict';

  // Wait for DOM ready
  function init() {
    // Check if Gutenberg is loaded
    if (typeof window.wp === 'undefined') {
      console.error('[PrestoWorld] Gutenberg not loaded. Make sure wp-editor.js is loaded before presto-editor.js');
      return;
    }

    // Initialize PrestoWorld API adapter
    initPrestoApiAdapter();

    // Initialize PrestoWorld data layer
    initPrestoDataLayer();

    // Initialize the editor
    initEditor();

    // Fire event for other scripts
    document.dispatchEvent(new CustomEvent('prestoworld:editor-ready'));
  }

  /**
   * Initialize PrestoWorld API adapter.
   * Wraps apiFetch to redirect WP REST calls to PW REST.
   */
  function initPrestoApiAdapter() {
    if (typeof window.wp.apiFetch === 'undefined') {
      return;
    }

    const originalApiFetch = window.wp.apiFetch;
    const adapter = window.prestoApiAdapter;

    if (adapter && adapter.createPrestoApiFetch) {
      window.wp.apiFetch = adapter.createPrestoApiFetch(originalApiFetch);
      console.log('[PrestoWorld] API adapter initialized. REST calls redirected to /pw-api/v1/');
    }
  }

  /**
   * Initialize PrestoWorld data layer.
   * Registers PW entity kinds and overrides data selectors.
   */
  function initPrestoDataLayer() {
    if (typeof window.wp.data === 'undefined') {
      return;
    }

    // Register PrestoWorld entity kinds
    const adapter = window.prestoApiAdapter;
    if (adapter && adapter.createPrestoDataAdapter) {
      const prestoData = adapter.createPrestoDataAdapter();

      // Override core-data store
      window.wp.data.dispatch('core').addEntities(prestoData.getEntityKinds());

      console.log('[PrestoWorld] Data layer initialized.');
    }
  }

  /**
   * Initialize the Gutenberg editor.
   */
  function initEditor() {
    if (typeof window.wp.editPost === 'undefined') {
      console.warn('[PrestoWorld] editPost module not loaded.');
      return;
    }

    // Get editor settings from PrestoWorld
    const settings = getPrestoEditorSettings();

    // Initialize editor
    try {
      window.wp.editPost.initializeEditor({
        target: document.getElementById('editor'),
        settings: settings,
      });
      console.log('[PrestoWorld] Editor initialized.');
    } catch (error) {
      console.error('[PrestoWorld] Editor initialization failed:', error);
    }
  }

  /**
   * Get PrestoWorld editor settings.
   */
  function getPrestoEditorSettings() {
    // Get settings from global config
    const config = window.prestoWorldConfig || {};

    return {
      // Post data
      postId: config.postId || 0,
      postType: config.postType || 'post',
      postTitle: config.postTitle || '',
      postContent: config.postContent || '',
      postExcerpt: config.postExcerpt || '',

      // Available blocks
      availableTemplates: config.availableTemplates || [],
      availableBlockPatterns: config.availableBlockPatterns || [],

      // API settings
      apiPrefix: '/pw-api/v1',
      nonce: window.prestoWorldNonce || '',

      // Editor features
      supportsTemplates: true,
      supportsTemplateParts: true,
      supportsBlockPatterns: true,

      // Theme settings
      theme: config.theme || 'jankx',
      themeStyles: config.themeStyles || '',

      // User settings
      user: config.user || {},
      userCan: config.userCan || {},

      // Media settings
      mediaUpload: config.mediaUpload || null,
      mediaLibrary: config.mediaLibrary || null,

      // Callbacks
      onSave: config.onSave || null,
      onAutosave: config.onAutosave || null,
    };
  }

  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();