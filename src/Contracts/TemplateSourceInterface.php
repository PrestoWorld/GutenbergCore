<?php

declare(strict_types=1);

namespace PrestoWorld\Gutenberg\Contracts;

use PrestoWorld\Gutenberg\Templates\Template;
use PrestoWorld\Gutenberg\Templates\TemplatePart;

/**
 * Source of block templates and template parts (Strategy pattern).
 */
interface TemplateSourceInterface
{
    public function findTemplate(string $slug): ?Template;

    /**
     * @return array<string, Template>
     */
    public function allTemplates(): array;

    public function findPart(string $slug): ?TemplatePart;

    /**
     * @return array<string, TemplatePart>
     */
    public function allParts(): array;
}
