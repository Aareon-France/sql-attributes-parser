<?php

declare(strict_types=1);

namespace AareonFrance\SqlAttributesParser\Reader;

use AareonFrance\SqlAttributesParser\SqlAttribute;

/**
 * Interface AttributesReaderInterface
 *
 * Provides methods to read a given input and try to extract attributes from it.
 */
interface AttributesReaderInterface
{
    /**
     * Search for occurrences of a specific attribute in a given list of strings.
     *
     * @template T of array-key
     * @param string $attributeName
     * @param array<T, string> $contents
     * @return array<T, non-empty-list<SqlAttribute>>
     */
    public function search(string $attributeName, array $contents): array;

    /**
     * Reads the given string and tries to build a list of attributes from it.
     *
     * @param string $string
     * @return list<SqlAttribute>
     */
    public function read(string $string): array;

    /**
     * Reads the given string and tries to build a single attribute (first one found) from it.
     *
     * @param string $string
     * @return null|SqlAttribute
     */
    public function readSingle(string $string): null|SqlAttribute;
}
