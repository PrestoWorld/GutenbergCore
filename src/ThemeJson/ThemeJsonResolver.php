<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\ThemeJson;

/**
 * Loads and layers theme.json (Resolver pattern).
 *
 * Layers: engine defaults → theme.json (theme origin).
 * User/global-styles customizations can be overlaid later via merge().
 */
final class ThemeJsonResolver
{
    /**
     * @param array<string, mixed> $overrides
     */
    public function resolve(string $themeDir, array $overrides = []): ThemeJson
    {
        $defaults = new ThemeJson(self::defaults());
        $theme = ThemeJson::fromFile($themeDir);

        $merged = $defaults->merge($theme);

        if ($overrides !== []) {
            $merged = $merged->merge(new ThemeJson($overrides));
        }

        return $merged;
    }

    /**
     * Baseline settings every engine expects to exist.
     *
     * @return array<string, mixed>
     */
    private static function defaults(): array
    {
        return [
            'version' => 3,
            'settings' => [
                'appearanceTools' => true,
                'layout' => [
                    'contentSize' => '840px',
                    'wideSize' => '1100px',
                ],
                'color' => [
                    'defaultPalette' => true,
                    'defaultGradients' => true,
                    'defaultDuotone' => true,
                ],
                'typography' => [
                    'fluid' => true,
                    'defaultFontSizes' => true,
                ],
                'spacing' => [
                    'defaultSpacingSizes' => true,
                ],
            ],
            'styles' => [],
        ];
    }
}
