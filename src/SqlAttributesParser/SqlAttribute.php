<?php

declare(strict_types=1);

namespace AareonFrance\SqlAttributesParser;

/**
 * Class SqlAttribute
 *
 * Represents a MySQL attribute in the following format:
 * #[Attribute(arg1: val1, arg2: val2, …)]
 */
final readonly class SqlAttribute
{
    /**
     * @param string $name
     * @param array<string, mixed> $arguments
     */
    public function __construct(
        public string $name,
        public array $arguments,
    ) {}
}
