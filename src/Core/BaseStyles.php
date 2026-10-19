<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Core;

/**
 * Essential static CSS for core blocks (what wp-block-library ships for the
 * server-rendered markup this engine produces).
 */
final class BaseStyles
{
    public const CSS = <<<'CSS'
/* === Layout primitives === */
.wp-block-group {}
.wp-block-columns { display: flex; gap: var(--wp--preset--spacing--40, 2rem); flex-wrap: wrap; }
.wp-block-columns.is-layout-flex { flex-wrap: wrap; }
.wp-block-column { flex-grow: 1; min-width: 0; }
.wp-block-cover, .wp-block-cover-image { position: relative; display: flex; flex-direction: column; justify-content: center; overflow: hidden; padding: 2.5rem; color: #fff; isolation: isolate; }
.wp-block-cover .wp-block-cover__image-background, .wp-block-cover video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: -1; }
.wp-block-cover .wp-block-cover__inner-container { position: relative; z-index: 1; width: 100%; }
.wp-block-cover.has-background-dim { }
.wp-block-media-text { display: grid; grid-template-columns: 40% 1fr; gap: 0; align-items: start; }
.wp-block-media-text__media img, .wp-block-media-text__media video { width: 100%; height: auto; display: block; }

/* === Content blocks === */
.wp-block-image img, .wp-block-post-featured-image img { max-width: 100%; height: auto; display: block; }
.wp-block-post-featured-image { margin-block-end: var(--wp--preset--spacing--40, 1.5rem); }
.wp-block-post-featured-image a { display: block; }
.wp-block-gallery { display: grid; gap: .5rem; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin: 0; }
.wp-block-gallery img { width: 100%; height: 100%; object-fit: cover; }
.wp-block-audio audio { width: 100%; }
.wp-block-video video { width: 100%; height: auto; }
.wp-block-quote { margin-inline: 0; padding-inline-start: 1.25rem; border-inline-start: 4px solid currentColor; font-style: italic; }
.wp-block-pullquote { border-block: 4px solid currentColor; padding-block: 1rem; text-align: center; }
.wp-block-separator { border: none; border-block-start: 1px solid currentColor; opacity: .35; margin-block: 1.5rem; }
.wp-block-separator.is-style-dots { border: none; text-align: center; }
.wp-block-preformatted { white-space: pre-wrap; overflow-wrap: break-word; }
.wp-block-code { font-family: ui-monospace, monospace; background: rgba(125,125,125,.1); padding: .5em .75em; border-radius: 6px; }
.wp-block-details summary { cursor: pointer; font-weight: 600; }
.wp-block-file a { font-weight: 600; }
.wp-block-table table { width: 100%; border-collapse: collapse; }
.wp-block-table td, .wp-block-table th { border: 1px solid rgba(125,125,125,.4); padding: .5em .75em; }

/* === Lists === */
.wp-block-list, ul.wp-block-list, ol.wp-block-list { padding-inline-start: 1.25em; }
.wp-block-list li { margin-block: .35em; }
ul.is-style-checkmark-list { list-style-type: "\2713"; }
ul.is-style-checkmark-list li { padding-inline-start: 1ch; }

/* === Buttons === */
.wp-block-buttons { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; }
.wp-block-button a.wp-element-button, .wp-element-button, button.wp-element-button { display: inline-block; padding: .6rem 1.2rem; border-radius: 999px; background: var(--wp--preset--color--contrast, #111); color: var(--wp--preset--color--base, #fff); text-decoration: none; font-weight: 500; border: none; cursor: pointer; line-height: 1.4; transition: opacity .15s ease; }
.wp-block-button a.wp-element-button:hover, .wp-element-button:hover { opacity: .85; }
.wp-block-button.is-style-outline a.wp-element-button { background: transparent; color: currentColor; border: 2px solid currentColor; }

/* === Navigation === */
.wp-block-navigation { position: relative; }
.wp-block-navigation ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 1.5rem; flex-wrap: wrap; align-items: center; }
.wp-block-navigation a { text-decoration: none; color: inherit; font-weight: 500; transition: opacity .2s; }
.wp-block-navigation a:hover { opacity: .7; }
.wp-block-navigation .has-child > a::after { content: ""; display: inline-block; margin-inline-start: .4em; border: 4px solid transparent; border-block-start-color: currentColor; transform: translateY(2px); }
.wp-block-navigation__submenu-container { display: none; position: absolute; top: 100%; inset-inline-start: 0; flex-direction: column; background: var(--wp--preset--color--base, #fff); min-width: 200px; padding: .75rem; gap: .5rem; border: 1px solid rgba(125,125,125,.25); border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.08); z-index: 20; }
.wp-block-navigation .has-child:hover > .wp-block-navigation__submenu-container, .wp-block-navigation .has-child:focus-within > .wp-block-navigation__submenu-container { display: flex; }
.wp-block-navigation__responsive-container-open, .wp-block-navigation__responsive-container-close { display: none; }
.wp-block-navigation-item { position: relative; }

/* === Site identity === */
.wp-block-site-title { font-size: var(--wp--preset--font-size--large, 1.5rem); font-weight: 800; text-transform: uppercase; letter-spacing: -.02em; margin: 0; }
.wp-block-site-title a { text-decoration: none; color: inherit; }
.wp-block-site-tagline { margin: .25rem 0 0; opacity: .75; }
.wp-block-site-logo img { display: block; height: auto; }

/* === Query loop / posts === */
.wp-block-post-template { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--wp--preset--spacing--50, 2.5rem); }
.wp-block-post-template.is-layout-flow > * + * { margin-block-start: 0; }
.wp-block-post-title { font-size: var(--wp--preset--font-size--x-large, 2rem); line-height: 1.15; margin: 0 0 .5rem; }
.wp-block-post-title a { text-decoration: none; color: inherit; }
.wp-block-post-title a:hover { text-decoration: underline; }
.wp-block-post-terms { font-size: var(--wp--preset--font-size--small, .875rem); opacity: .8; margin-block-end: .5rem; }
.wp-block-post-terms a { color: inherit; }
.wp-block-post-date { font-size: var(--wp--preset--font-size--small, .875rem); opacity: .7; }
.wp-block-post-date a { color: inherit; text-decoration: none; }
.wp-block-post-author-name { font-size: var(--wp--preset--font-size--small, .875rem); font-weight: 600; }
.wp-block-post-author-name a { color: inherit; }
.wp-block-post-excerpt { font-size: var(--wp--preset--font-size--medium, 1rem); line-height: 1.6; opacity: .85; }
.wp-block-post-excerpt__more-link { color: inherit; font-weight: 600; }
.wp-block-post-content > * { margin-block: 0; }
.wp-block-post-content p { line-height: 1.7; }
.wp-block-post-navigation-link { font-weight: 600; }
.wp-block-post-navigation-link a { color: inherit; text-decoration: none; }
.wp-block-query-title { font-size: var(--wp--preset--font-size--x-large, 2rem); margin: 0 0 var(--wp--preset--spacing--40, 1.5rem); }

/* === Pagination === */
.wp-block-query-pagination, .wp-block-comments-pagination { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; margin-block-start: var(--wp--preset--spacing--50, 2rem); }
.wp-block-query-pagination a, .wp-block-comments-pagination a, .page-numbers { text-decoration: none; color: inherit; font-weight: 500; padding: .35rem .7rem; border-radius: 6px; }
.wp-block-query-pagination a:hover, .page-numbers:hover { background: rgba(125,125,125,.12); }
.page-numbers.current { background: rgba(125,125,125,.2); }

/* === Search === */
.wp-block-search { display: flex; gap: .5rem; align-items: stretch; }
.wp-block-search__input { flex: 1; min-width: 0; padding: .55rem .85rem; border: 1px solid rgba(125,125,125,.4); border-radius: 999px; background: transparent; color: inherit; font: inherit; }
.wp-block-search__button { padding: .55rem 1.2rem; border: none; border-radius: 999px; background: var(--wp--preset--color--contrast, #111); color: var(--wp--preset--color--base, #fff); cursor: pointer; font-weight: 500; }

/* === Social === */
.wp-block-social-links { display: flex; gap: .75rem; list-style: none; margin: 0; padding: 0; flex-wrap: wrap; }
.wp-block-social-link a { display: inline-flex; color: inherit; transition: opacity .2s; }
.wp-block-social-link a:hover { opacity: .7; }
.wp-block-social-icon { width: 1.5em; height: 1.5em; fill: currentColor; }

/* === Comments === */
.wp-block-comments { margin-block-start: var(--wp--preset--spacing--60, 3rem); }
.wp-block-comment-template { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: var(--wp--preset--spacing--40, 1.5rem); }
.wp-block-comment-template li { border-block-end: 1px solid rgba(125,125,125,.25); padding-block-end: var(--wp--preset--spacing--40, 1.5rem); }
.wp-block-comment-author-name { font-weight: 600; }
.wp-block-comment-author-name a { color: inherit; text-decoration: none; }
.wp-block-comment-date { font-size: var(--wp--preset--font-size--small, .875rem); opacity: .7; margin-block: .25rem; }
.wp-block-comment-content { line-height: 1.65; margin-block-start: .5rem; }
.wp-block-comment-content p { margin-block: .5rem; }
.comment-form { display: flex; flex-direction: column; gap: .75rem; margin-block-start: var(--wp--preset--spacing--40, 1.5rem); }
.comment-form input[type="text"], .comment-form input[type="email"], .comment-form textarea { padding: .6rem .8rem; border: 1px solid rgba(125,125,125,.4); border-radius: 8px; background: transparent; color: inherit; font: inherit; width: 100%; }
.comment-form textarea { min-height: 8rem; }
.comment-form .form-submit { margin: 0; }
.avatar.wp-block-avatar__image { border-radius: 50%; display: block; }
.wp-block-avatar__image.is-square-shape { border-radius: 0; }

/* === Template parts / misc === */
.wp-block-template-part { margin: 0; }
.wp-block-pattern { display: contents; }
.wp-block-term-description { line-height: 1.6; }
.wp-block-loginout a { font-weight: 600; }
.wp-block-read-more { font-weight: 600; }
CSS;

    public static function css(): string
    {
        return self::CSS;
    }
}
