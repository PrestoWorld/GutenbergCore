<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg;

/**
 * WP-DERIVED REFERENCE DATA — kept inside this GPL package so app code stays
 * license-clean. Mirrors the static values WordPress core exposes through
 * `/wp/v2/types`, `/wp/v2/taxonomies` and the theme endpoint's
 * `default_template_types` / `default_template_part_areas` fields
 * (WordPress, GPL-2.0-or-later).
 *
 * Presto-specific addition: `defaultTemplatePartAreas()` ships the `sidebar`
 * area the active theme uses; core only knows four areas.
 */
class WpDefaults
{
    /**
     * `default_template_types` — keyed by slug, each `{title, description}`.
     *
     * Mirrors WP's `get_default_block_template_types()`. The REST layer adds
     * the `slug` key to each entry before emitting.
     *
     * @return array<string, array{title: string, description: string}>
     */
    public static function defaultTemplateTypes(): array
    {
        return [
            'index' => [
                'title' => 'Index',
                'description' => 'The main template file.',
            ],
            'home' => [
                'title' => 'Home',
                'description' => 'The latest blog posts and other content.',
            ],
            'front-page' => [
                'title' => 'Front Page',
                'description' => 'The template used for the front page of the site.',
            ],
            'singular' => [
                'title' => 'Single Entries',
                'description' => 'The template used for single posts, pages, and other custom post types.',
            ],
            'single' => [
                'title' => 'Single Entries',
                'description' => 'The template used for single posts.',
            ],
            'page' => [
                'title' => 'Pages',
                'description' => 'The template used for individual pages.',
            ],
            'archive' => [
                'title' => 'Archives',
                'description' => 'The template used for archive pages.',
            ],
            'author' => [
                'title' => 'Author Archives',
                'description' => 'The template used for author archives.',
            ],
            'category' => [
                'title' => 'Category Archives',
                'description' => 'The template used for category archives.',
            ],
            'taxonomy' => [
                'title' => 'Taxonomy Archives',
                'description' => 'The template used for taxonomy archives.',
            ],
            'date' => [
                'title' => 'Date Archives',
                'description' => 'The template used for date archives.',
            ],
            'tag' => [
                'title' => 'Tag Archives',
                'description' => 'The template used for tag archives.',
            ],
            'attachment' => [
                'title' => 'Media',
                'description' => 'The template used for single media attachments.',
            ],
            'search' => [
                'title' => 'Search Results',
                'description' => 'The template used for search results pages.',
            ],
            'privacy-policy' => [
                'title' => 'Privacy Policy',
                'description' => 'The template used for the privacy policy page.',
            ],
            '404' => [
                'title' => '404',
                'description' => 'The template used when a post or page cannot be found.',
            ],
        ];
    }

    /**
     * `default_template_part_areas` — WP core's four plus the theme's `sidebar`.
     *
     * @return list<array{area: string, label: string, icon: string, description: string, area_tag: string}>
     */
    public static function defaultTemplatePartAreas(): array
    {
        return [
            [
                'area' => 'uncategorized',
                'label' => 'General',
                'icon' => 'layout',
                'description' => 'General template part areas.',
                'area_tag' => 'div',
            ],
            [
                'area' => 'header',
                'label' => 'Header',
                'icon' => 'header',
                'description' => 'Header template part areas.',
                'area_tag' => 'header',
            ],
            [
                'area' => 'footer',
                'label' => 'Footer',
                'icon' => 'footer',
                'description' => 'Footer template part areas.',
                'area_tag' => 'footer',
            ],
            [
                'area' => 'navigation-overlay',
                'label' => 'Navigation Overlay',
                'icon' => 'navigation-overlay',
                'description' => 'Navigation overlay template part areas.',
                'area_tag' => 'div',
            ],
            [
                'area' => 'sidebar',
                'label' => 'Sidebar',
                'icon' => 'layout',
                'description' => 'Sidebar template part areas.',
                'area_tag' => 'aside',
            ],
        ];
    }

    /**
     * Post types exposed through `/wp/v2/types`, keyed by slug.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function postTypes(): array
    {
        return [
            'post' => [
                'slug' => 'post',
                'name' => 'Posts',
                'description' => '',
                'rest_base' => 'posts',
                'rest_namespace' => 'wp/v2',
                'viewable' => true,
                'has_archive' => true,
                'icon' => null,
                'taxonomies' => ['category', 'post_tag'],
                'supports' => [
                    'author' => true,
                    'comments' => true,
                    'editor' => true,
                    'excerpt' => true,
                    'revisions' => true,
                    'thumbnail' => true,
                    'title' => true,
                    'trackbacks' => true,
                    'post-formats' => true,
                ],
                'labels' => [
                    'name' => 'Posts',
                    'singular_name' => 'Post',
                    'add_new' => 'Add New',
                    'add_new_item' => 'Add New Post',
                    'edit_item' => 'Edit Post',
                    'new_item' => 'New Post',
                    'view_item' => 'View Post',
                    'search_items' => 'Search Posts',
                    'not_found' => 'No posts found.',
                    'not_found_in_trash' => 'No posts found in Trash.',
                    'featured_image' => 'Featured Image',
                    'set_featured_image' => 'Set featured image',
                    'remove_featured_image' => 'Remove featured image',
                    'use_featured_image' => 'Use as featured image',
                    'archives' => 'Post archives',
                    'insert_into_item' => 'Insert into post',
                    'uploaded_to_this_item' => 'Uploaded to this post',
                    'filter_items_list' => 'Filter posts list',
                    'items_list_navigation' => 'Posts list navigation',
                    'items_list' => 'Posts list',
                ],
            ],
            'page' => [
                'slug' => 'page',
                'name' => 'Pages',
                'description' => '',
                'rest_base' => 'pages',
                'rest_namespace' => 'wp/v2',
                'viewable' => true,
                'has_archive' => false,
                'icon' => null,
                'taxonomies' => [],
                'supports' => [
                    'author' => true,
                    'comments' => true,
                    'editor' => true,
                    'excerpt' => true,
                    'page-attributes' => true,
                    'revisions' => true,
                    'thumbnail' => true,
                    'title' => true,
                ],
                'labels' => [
                    'name' => 'Pages',
                    'singular_name' => 'Page',
                    'add_new' => 'Add New',
                    'add_new_item' => 'Add New Page',
                    'edit_item' => 'Edit Page',
                    'new_item' => 'New Page',
                    'view_item' => 'View Page',
                    'search_items' => 'Search Pages',
                    'not_found' => 'No pages found.',
                    'not_found_in_trash' => 'No pages found in Trash.',
                    'featured_image' => 'Featured Image',
                    'set_featured_image' => 'Set featured image',
                    'remove_featured_image' => 'Remove featured image',
                    'use_featured_image' => 'Use as featured image',
                    'archives' => 'Page archives',
                    'insert_into_item' => 'Insert into page',
                    'uploaded_to_this_item' => 'Uploaded to this page',
                    'filter_items_list' => 'Filter pages list',
                    'items_list_navigation' => 'Pages list navigation',
                    'items_list' => 'Pages list',
                    'parent_item_colon' => 'Parent Page:',
                ],
            ],
            'attachment' => [
                'slug' => 'attachment',
                'name' => 'Media',
                'description' => '',
                'rest_base' => 'media',
                'rest_namespace' => 'wp/v2',
                'viewable' => true,
                'has_archive' => false,
                'icon' => null,
                'taxonomies' => [],
                'supports' => [
                    'author' => true,
                    'comments' => true,
                    'editor' => true,
                    'title' => true,
                ],
                'labels' => [
                    'name' => 'Media',
                    'singular_name' => 'Media',
                    'add_new' => 'Add New',
                    'add_new_item' => 'Add New Media',
                    'edit_item' => 'Edit Media',
                    'new_item' => 'New Media',
                    'view_item' => 'View Media',
                    'search_items' => 'Search Media',
                    'not_found' => 'No media found.',
                    'not_found_in_trash' => 'No media found in Trash.',
                ],
            ],
            'wp_template' => [
                'slug' => 'wp_template',
                'name' => 'Templates',
                'description' => '',
                'rest_base' => 'templates',
                'rest_namespace' => 'wp/v2',
                'viewable' => true,
                'has_archive' => false,
                'icon' => null,
                'taxonomies' => [],
                'supports' => [
                    'author' => true,
                    'editor' => false,
                    'revisions' => true,
                    'title' => true,
                ],
                'labels' => [
                    'name' => 'Templates',
                    'singular_name' => 'Template',
                    'add_new' => 'Add New',
                    'add_new_item' => 'Add New Template',
                    'edit_item' => 'Edit Template',
                    'new_item' => 'New Template',
                    'view_item' => 'View Template',
                    'search_items' => 'Search Templates',
                    'not_found' => 'No templates found.',
                    'not_found_in_trash' => 'No templates found in Trash.',
                    'template_name' => 'Template',
                    'item_published' => 'Template published.',
                    'item_updated' => 'Template updated.',
                ],
            ],
            'wp_template_part' => [
                'slug' => 'wp_template_part',
                'name' => 'Template Parts',
                'description' => '',
                'rest_base' => 'template-parts',
                'rest_namespace' => 'wp/v2',
                'viewable' => true,
                'has_archive' => false,
                'icon' => null,
                'taxonomies' => [],
                'supports' => [
                    'author' => true,
                    'editor' => false,
                    'revisions' => true,
                    'title' => true,
                ],
                'labels' => [
                    'name' => 'Template Parts',
                    'singular_name' => 'Template Part',
                    'add_new' => 'Add New',
                    'add_new_item' => 'Add New Template Part',
                    'edit_item' => 'Edit Template Part',
                    'new_item' => 'New Template Part',
                    'view_item' => 'View Template Part',
                    'search_items' => 'Search Template Parts',
                    'not_found' => 'No template parts found.',
                    'not_found_in_trash' => 'No template parts found in Trash.',
                    'template_name' => 'Template',
                    'template_part_name' => 'Template Part',
                    'item_published' => 'Template Part published.',
                    'item_updated' => 'Template Part updated.',
                ],
            ],
            'wp_global_styles' => [
                'slug' => 'wp_global_styles',
                'name' => 'Global Styles',
                'description' => '',
                'rest_base' => 'global-styles',
                'rest_namespace' => 'wp/v2',
                'viewable' => false,
                'has_archive' => false,
                'icon' => null,
                'taxonomies' => [],
                'supports' => [
                    'author' => true,
                    'editor' => false,
                    'revisions' => true,
                    'title' => false,
                ],
                'labels' => [
                    'name' => 'Global Styles',
                    'singular_name' => 'Global Styles',
                    'add_new' => 'Add New',
                    'add_new_item' => 'Add New Global Styles',
                    'edit_item' => 'Edit Global Styles',
                    'new_item' => 'New Global Styles',
                    'view_item' => 'View Global Styles',
                    'search_items' => 'Search Global Styles',
                    'not_found' => 'No global styles found.',
                    'not_found_in_trash' => 'No global styles found in Trash.',
                ],
            ],
            'wp_navigation' => [
                'slug' => 'wp_navigation',
                'name' => 'Navigation Menus',
                'description' => '',
                'rest_base' => 'navigation',
                'rest_namespace' => 'wp/v2',
                'viewable' => true,
                'has_archive' => false,
                'icon' => null,
                'taxonomies' => [],
                'supports' => [
                    'author' => true,
                    'editor' => true,
                    'revisions' => true,
                    'title' => true,
                ],
                'labels' => [
                    'name' => 'Navigation Menus',
                    'singular_name' => 'Navigation Menu',
                    'add_new' => 'Add New',
                    'add_new_item' => 'Add New Navigation Menu',
                    'edit_item' => 'Edit Navigation Menu',
                    'new_item' => 'New Navigation Menu',
                    'view_item' => 'View Navigation Menu',
                    'search_items' => 'Search Navigation Menus',
                    'not_found' => 'No navigation menus found.',
                    'not_found_in_trash' => 'No navigation menus found in Trash.',
                ],
            ],
            'wp_block' => [
                'slug' => 'wp_block',
                'name' => 'Reusable Blocks',
                'description' => '',
                'rest_base' => 'blocks',
                'rest_namespace' => 'wp/v2',
                'viewable' => false,
                'has_archive' => false,
                'icon' => null,
                'taxonomies' => [],
                'supports' => [
                    'author' => true,
                    'editor' => false,
                    'revisions' => true,
                    'title' => true,
                ],
                'labels' => [
                    'name' => 'Reusable Blocks',
                    'singular_name' => 'Reusable Block',
                    'add_new' => 'Add New',
                    'add_new_item' => 'Add New Reusable Block',
                    'edit_item' => 'Edit Reusable Block',
                    'new_item' => 'New Reusable Block',
                    'view_item' => 'View Reusable Block',
                    'search_items' => 'Search Reusable Blocks',
                    'not_found' => 'No reusable blocks found.',
                    'not_found_in_trash' => 'No reusable blocks found in Trash.',
                ],
            ],
        ];
    }

    /**
     * Taxonomies exposed through `/wp/v2/taxonomies`, keyed by slug.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function taxonomies(): array
    {
        return [
            'category' => [
                'slug' => 'category',
                'name' => 'Categories',
                'description' => '',
                'rest_base' => 'categories',
                'rest_namespace' => 'wp/v2',
                'types' => ['post'],
                'visibility' => ['public' => true],
                'hierarchical' => true,
                'labels' => [
                    'name' => 'Categories',
                    'singular_name' => 'Category',
                    'search_items' => 'Search Categories',
                    'all_items' => 'All Categories',
                    'parent_item' => 'Parent Category',
                    'parent_item_colon' => 'Parent Category:',
                    'edit_item' => 'Edit Category',
                    'update_item' => 'Update Category',
                    'add_new_item' => 'Add New Category',
                    'new_item_name' => 'New Category Name',
                    'not_found' => 'No categories found.',
                ],
            ],
            'post_tag' => [
                'slug' => 'post_tag',
                'name' => 'Tags',
                'description' => '',
                'rest_base' => 'tags',
                'rest_namespace' => 'wp/v2',
                'types' => ['post'],
                'visibility' => ['public' => true],
                'hierarchical' => false,
                'labels' => [
                    'name' => 'Tags',
                    'singular_name' => 'Tag',
                    'search_items' => 'Search Tags',
                    'all_items' => 'All Tags',
                    'edit_item' => 'Edit Tag',
                    'update_item' => 'Update Tag',
                    'add_new_item' => 'Add New Tag',
                    'new_item_name' => 'New Tag Name',
                    'not_found' => 'No tags found.',
                ],
            ],
            'post_format' => [
                'slug' => 'post_format',
                'name' => 'Formats',
                'description' => '',
                'rest_base' => 'post-formats',
                'rest_namespace' => 'wp/v2',
                'types' => ['post'],
                'visibility' => ['public' => true],
                'hierarchical' => false,
                'labels' => [
                    'name' => 'Formats',
                    'singular_name' => 'Format',
                    'search_items' => 'Search Formats',
                    'all_items' => 'All Formats',
                    'edit_item' => 'Edit Format',
                    'update_item' => 'Update Format',
                    'add_new_item' => 'Add New Format',
                    'not_found' => 'No formats found.',
                ],
            ],
        ];
    }
}
