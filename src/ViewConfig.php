<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg;

/**
 * Entity view configuration served at `GET /wp-json/wp/v2/view-config`.
 *
 * Port of Gutenberg's `gutenberg_get_entity_view_config()` and its per-entity
 * callbacks from `lib/compat/wordpress-7.1/view-config-api.php`, plus the
 * WordPress 7.2 layers from `lib/compat/wordpress-7.2/view-config-api.php`
 * (the wp_template form replacement, the stale wp_template view fields
 * removal, and the wp_navigation / root-site base definitions).
 *
 * Upstream reads WordPress runtime state (post type labels, the pattern
 * category registries, allowed template part areas, template authors) while
 * building the record; those inputs arrive through `$context` so this port
 * stays free of application dependencies:
 *
 *  - `allItemsLabel`     sidebar title of the base `all` view (upstream: the
 *                        post type's `all_items` label).
 *  - `patternCategories` slug => label (upstream: the registered block pattern
 *                        categories plus `wp_pattern_category` terms); sorted
 *                        alphabetically here exactly like upstream sorts them.
 *  - `partAreas`         list of `{area, label, ...}` (upstream:
 *                        `get_allowed_block_template_part_areas()`).
 *  - `templateAuthors`   ordered author labels for `wp_template` (upstream:
 *                        unique non-empty `author_text` values of the templates).
 *
 * GPL-2.0-or-later — ported from WordPress/Gutenberg.
 */
class ViewConfig
{
    /**
     * Area order upstream pins ahead of any theme/plugin-defined area.
     *
     * Mirrors the `$preferred_order` list in
     * `_gutenberg_get_entity_view_config_posttype_wp_template_part()`.
     */
    private const PART_AREA_ORDER = [
        'header',
        'footer',
        'sidebar',
        'navigation-overlay',
        'uncategorized',
    ];

    /**
     * Build the view configuration for an entity.
     *
     * Mirrors `gutenberg_get_entity_view_config( $kind, $name )` with the
     * upstream per-entity callbacks applied at their priorities: base
     * definitions first, then the entity's own.
     *
     * @param string $kind    Entity kind (e.g. `postType`).
     * @param string $name    Entity name (e.g. `wp_template`).
     * @param array<string, mixed> $context Runtime inputs, see the class doc.
     * @return array<string, mixed> `default_view`, `default_layouts`,
     *                              `view_list` and `form`.
     */
    public function for(string $kind, string $name, array $context = []): array
    {
        $config = $this->base($kind, $context);

        switch ($kind . '/' . $name) {
            case 'postType/page':
                $this->applyPage($config);
                break;
            case 'postType/wp_block':
                $this->applyBlock($config, $context);
                break;
            case 'postType/wp_template_part':
                $this->applyTemplatePart($config, $context);
                break;
            case 'postType/wp_template':
                $this->applyTemplate($config, $context);
                break;
            case 'postType/wp_navigation':
                $this->applyNavigation($config);
                break;
            case 'root/site':
                $this->applySite($config);
                break;
            // `post` and every other post type keep the base configuration.
        }

        return $config;
    }

    /**
     * The configuration shared by every entity.
     *
     * Mirrors the defaults built in `gutenberg_get_entity_view_config()`.
     *
     * @param array<string, mixed> $context Runtime inputs, see the class doc.
     * @return array<string, mixed>
     */
    private function base(string $kind, array $context): array
    {
        $defaultView = [
            'type' => 'table',
            'filters' => [],
            'sort' => [
                'field' => 'title',
                'direction' => 'asc',
            ],
            'perPage' => 20,
            'fields' => ['author', 'status'],
            'titleField' => 'title',
        ];

        $defaultLayouts = [
            'table' => [],
            'grid' => [],
            'list' => [],
        ];

        $allItemsLabel = (string) ($context['allItemsLabel'] ?? 'All items');
        $viewList = [
            [
                'title' => $allItemsLabel,
                'slug' => 'all',
            ],
        ];

        return [
            'default_view' => $defaultView,
            'default_layouts' => $defaultLayouts,
            'view_list' => $viewList,
            'form' => $kind === 'postType' ? $this->defaultPostTypeForm() : [],
        ];
    }

    /**
     * The default `form` for post types without their own.
     *
     * Mirrors `_gutenberg_get_default_posttype_form()`.
     *
     * @return array<string, mixed>
     */
    private function defaultPostTypeForm(): array
    {
        return [
            'layout' => ['type' => 'panel'],
            'fields' => [
                [
                    'id' => 'featured_media',
                    'layout' => [
                        'type' => 'regular',
                        'labelPosition' => 'none',
                    ],
                ],
                [
                    'id' => 'post-content-info',
                    'layout' => [
                        'type' => 'regular',
                        'labelPosition' => 'none',
                    ],
                ],
                [
                    'id' => 'excerpt',
                    'layout' => [
                        'type' => 'panel',
                        'labelPosition' => 'top',
                    ],
                ],
                [
                    'id' => 'status',
                    'label' => 'Status',
                    'children' => [
                        [
                            'id' => 'status',
                            'layout' => [
                                'type' => 'regular',
                                'labelPosition' => 'none',
                            ],
                        ],
                        'scheduled_date',
                        'password',
                        'sticky',
                    ],
                ],
                'date',
                'slug',
                'author',
                'template',
                [
                    'id' => 'discussion',
                    'label' => 'Discussion',
                    'children' => [
                        [
                            'id' => 'comment_status',
                            'layout' => [
                                'type' => 'regular',
                                'labelPosition' => 'none',
                            ],
                        ],
                        'ping_status',
                    ],
                ],
                'parent',
                'format',
                'revisions',
            ],
        ];
    }

    /**
     * The `page` post type layers.
     *
     * Mirrors `_gutenberg_get_entity_view_config_posttype_page()`.
     *
     * @param array<string, mixed> $config Configuration to modify in place.
     */
    private function applyPage(array &$config): void
    {
        $tableLayout = [
            'styles' => [
                'author' => [
                    'align' => 'start',
                ],
            ],
        ];

        $config['default_view'] = [
            'type' => 'list',
            'filters' => [],
            'perPage' => 20,
            'sort' => [
                'field' => 'title',
                'direction' => 'asc',
            ],
            'showLevels' => true,
            'titleField' => 'title',
            'mediaField' => 'featured_media',
            'fields' => ['author', 'status'],
        ];

        $config['default_layouts'] = [
            'table' => ['layout' => $tableLayout],
            'grid' => [],
            'list' => [],
        ];

        // Merged onto the base list so the post type's "all items" view keeps
        // its own title, exactly like upstream appends the status views.
        $config['view_list'] = array_merge(
            $config['view_list'],
            [
                $this->statusView('Published', 'published', 'publish'),
                $this->statusView('Scheduled', 'future', 'future'),
                $this->statusView('Drafts', 'drafts', 'draft'),
                $this->statusView('Pending', 'pending', 'pending'),
                $this->statusView('Private', 'private', 'private'),
                [
                    'title' => 'Trash',
                    'slug' => 'trash',
                    'view' => [
                        'type' => 'table',
                        'layout' => $tableLayout,
                        'filters' => [$this->lockedFilter('status', 'isAny', 'trash')],
                    ],
                ],
            ],
        );
    }

    /**
     * The `wp_block` (Patterns) layers.
     *
     * Mirrors `_gutenberg_get_entity_view_config_posttype_wp_block()`, with the
     * pattern categories supplied through the context instead of the registries.
     *
     * @param array<string, mixed> $config  Configuration to modify in place.
     * @param array<string, mixed> $context Runtime inputs, see the class doc.
     */
    private function applyBlock(array &$config, array $context): void
    {
        $defaultLayouts = [
            'table' => [
                'layout' => [
                    'styles' => [
                        'author' => [
                            'width' => '1%',
                        ],
                    ],
                ],
            ],
            'grid' => [
                'layout' => [
                    'badgeFields' => ['sync-status'],
                ],
            ],
        ];

        $config['default_view'] = [
            'type' => 'grid',
            'perPage' => 20,
            'titleField' => 'title',
            'mediaField' => 'preview',
            'fields' => ['sync-status'],
            'filters' => [],
            'layout' => $defaultLayouts['grid']['layout'],
        ];
        $config['default_layouts'] = $defaultLayouts;

        $viewList = [
            [
                'title' => 'All patterns',
                'slug' => 'all-patterns',
            ],
            [
                'title' => 'My patterns',
                'slug' => 'my-patterns',
            ],
        ];

        $categories = [];
        foreach (($context['patternCategories'] ?? []) as $slug => $label) {
            $categories[(string) $slug] = (string) $label;
        }
        // Sort categories alphabetically by label, like upstream's asort().
        asort($categories, SORT_NATURAL | SORT_FLAG_CASE);
        foreach ($categories as $slug => $label) {
            $viewList[] = [
                'title' => $label,
                'slug' => $slug,
            ];
        }
        $config['view_list'] = $viewList;

        $config['form'] = [
            'layout' => ['type' => 'panel'],
            'fields' => [
                [
                    'id' => 'excerpt',
                    'layout' => [
                        'type' => 'panel',
                        'labelPosition' => 'top',
                    ],
                ],
                [
                    'id' => 'post-content-info',
                    'layout' => [
                        'type' => 'regular',
                        'labelPosition' => 'none',
                    ],
                ],
                'sync-status',
                'revisions',
            ],
        ];
    }

    /**
     * The `wp_template_part` layers.
     *
     * Mirrors `_gutenberg_get_entity_view_config_posttype_wp_template_part()`,
     * with the allowed areas supplied through the context instead of
     * `get_allowed_block_template_part_areas()`.
     *
     * @param array<string, mixed> $config  Configuration to modify in place.
     * @param array<string, mixed> $context Runtime inputs, see the class doc.
     */
    private function applyTemplatePart(array &$config, array $context): void
    {
        $defaultLayouts = [
            'table' => [
                'layout' => [
                    'styles' => [
                        'author' => [
                            'width' => '1%',
                        ],
                    ],
                ],
            ],
            'grid' => [
                'layout' => [],
            ],
        ];

        $config['default_view'] = [
            'type' => 'grid',
            'perPage' => 20,
            'titleField' => 'title',
            'mediaField' => 'preview',
            'fields' => ['author'],
            'filters' => [],
            'layout' => $defaultLayouts['grid']['layout'],
        ];
        $config['default_layouts'] = $defaultLayouts;

        $viewList = [
            [
                'title' => 'All template parts',
                'slug' => 'all-parts',
            ],
        ];

        foreach ($this->orderedPartAreas($context['partAreas'] ?? []) as $area) {
            $slug = (string) ($area['area'] ?? '');
            if ($slug === '') {
                continue;
            }

            $viewList[] = [
                'title' => (string) ($area['label'] ?? $slug),
                'slug' => $slug,
                'view' => [
                    'filters' => [$this->lockedFilter('area', 'is', $slug)],
                ],
            ];
        }
        $config['view_list'] = $viewList;

        $config['form'] = [
            'layout' => ['type' => 'panel'],
            'fields' => [
                [
                    'id' => 'last_edited_date',
                    'layout' => [
                        'type' => 'panel',
                        'labelPosition' => 'none',
                    ],
                ],
                'revisions',
            ],
        ];
    }

    /**
     * The `wp_template` layers.
     *
     * Mirrors `_gutenberg_get_entity_view_config_posttype_wp_template()` with
     * the 7.2 layers applied: `_gutenberg_remove_stale_fields_from_wp_template_view_config()`
     * drops `active`/`slug`/`theme` from the visible fields (no field with
     * those ids is registered for templates anymore) and
     * `_gutenberg_add_reading_settings_to_wp_template_view_config()` replaces
     * the form fields wholesale.
     *
     * @param array<string, mixed> $config  Configuration to modify in place.
     * @param array<string, mixed> $context Runtime inputs, see the class doc.
     */
    private function applyTemplate(array &$config, array $context): void
    {
        $config['default_view'] = [
            'type' => 'grid',
            'perPage' => 20,
            'sort' => [
                'field' => 'title',
                'direction' => 'asc',
            ],
            'titleField' => 'title',
            'descriptionField' => 'description',
            'mediaField' => 'preview',
            'fields' => ['author'],
            'filters' => [],
            'showMedia' => true,
        ];

        $config['default_layouts'] = [
            'table' => ['showMedia' => false],
            'grid' => ['showMedia' => true],
            'list' => ['showMedia' => false],
        ];

        $viewList = [
            [
                'title' => 'All templates',
                'slug' => 'all',
            ],
        ];

        foreach ($this->uniqueLabels($context['templateAuthors'] ?? []) as $author) {
            $viewList[] = [
                'title' => $author,
                'slug' => $author,
                'view' => [
                    'filters' => [$this->lockedFilter('author', 'is', $author)],
                ],
            ];
        }
        $config['view_list'] = $viewList;

        $config['form'] = [
            'layout' => ['type' => 'panel'],
            'fields' => [
                [
                    'id' => 'description',
                    'layout' => [
                        'type' => 'panel',
                        'labelPosition' => 'top',
                    ],
                ],
                [
                    'id' => 'description_readonly',
                    'layout' => [
                        'type' => 'regular',
                        'labelPosition' => 'none',
                    ],
                ],
                [
                    'id' => 'reading_settings',
                    'layout' => [
                        'type' => 'regular',
                        'labelPosition' => 'none',
                    ],
                ],
                [
                    'id' => 'last_edited_date',
                    'layout' => [
                        'type' => 'panel',
                        'labelPosition' => 'none',
                    ],
                ],
                'revisions',
                // The following fields are only meaningful in the `home`/`index`
                // template summary. They edit other entities (`root/site` and
                // the posts page); the editor merges those records into the
                // form data under a namespace and controls when they show.
                'posts_page_title',
                'posts_per_page',
                'default_comment_status',
            ],
        ];
    }

    /**
     * The `wp_navigation` layers.
     *
     * Mirrors `_gutenberg_get_entity_view_config_posttype_wp_navigation()`: a
     * base definition that only replaces the default view and layouts, keeping
     * the base `view_list` (titled from the post type label) and form.
     *
     * @param array<string, mixed> $config Configuration to modify in place.
     */
    private function applyNavigation(array &$config): void
    {
        $config['default_view'] = [
            'type' => 'list',
            'filters' => [],
            'perPage' => 20,
            'sort' => [
                'field' => 'date',
                'direction' => 'desc',
            ],
            'titleField' => 'title',
            'fields' => [],
        ];
        $config['default_layouts'] = [
            'list' => [],
        ];
    }

    /**
     * The `root/site` layers.
     *
     * Mirrors `_gutenberg_get_entity_view_config_root_site()`: the site
     * settings are a singleton edited through a form, so only `form` changes.
     *
     * @param array<string, mixed> $config Configuration to modify in place.
     */
    private function applySite(array &$config): void
    {
        $config['form'] = [
            'layout' => [
                'type' => 'regular',
                'labelPosition' => 'top',
            ],
            'fields' => [
                'title',
                'description',
                'site_logo',
                'site_icon',
            ],
        ];
    }

    /**
     * A `view_list` entry filtering a post status, locked for the user.
     *
     * @param string $title    Sidebar title.
     * @param string $slug     View slug.
     * @param string $value    Status value the filter pins.
     * @return array<string, mixed>
     */
    private function statusView(string $title, string $slug, string $value): array
    {
        return [
            'title' => $title,
            'slug' => $slug,
            'view' => [
                'filters' => [$this->lockedFilter('status', 'isAny', $value)],
            ],
        ];
    }

    /**
     * A locked (user-removable: no) filter, as the upstream views declare.
     *
     * @param string $field    Field the filter applies to.
     * @param string $operator Filter operator.
     * @param string $value    Filter value.
     * @return array{field: string, operator: string, value: string, isLocked: bool}
     */
    private function lockedFilter(string $field, string $operator, string $value): array
    {
        return [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'isLocked' => true,
        ];
    }

    /**
     * Template part areas with the preferred ones first.
     *
     * Mirrors the ordering block in
     * `_gutenberg_get_entity_view_config_posttype_wp_template_part()`.
     *
     * @param mixed $areas Raw context value.
     * @return array<int, array<string, mixed>>
     */
    private function orderedPartAreas(mixed $areas): array
    {
        if (!is_array($areas)) {
            return [];
        }

        $preferredOrder = self::PART_AREA_ORDER;
        $ordered = [];
        $remaining = [];
        foreach ($areas as $area) {
            if (!is_array($area)) {
                continue;
            }

            $position = array_search((string) ($area['area'] ?? ''), $preferredOrder, true);
            if ($position !== false) {
                $ordered[$position] = $area;
            } else {
                $remaining[] = $area;
            }
        }
        ksort($ordered);

        return array_merge(array_values($ordered), $remaining);
    }

    /**
     * Deduplicate labels, preserving their order.
     *
     * @param mixed $labels Raw context value.
     * @return array<int, string>
     */
    private function uniqueLabels(mixed $labels): array
    {
        if (!is_array($labels)) {
            return [];
        }

        $unique = [];
        foreach ($labels as $label) {
            $label = trim((string) $label);
            if ($label !== '') {
                $unique[$label] = true;
            }
        }

        return array_keys($unique);
    }
}
