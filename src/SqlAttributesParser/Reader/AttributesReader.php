<?php

declare(strict_types=1);

namespace AareonFrance\SqlAttributesParser\Reader;

use AareonFrance\SqlAttributesParser\SqlAttribute;

use function array_combine;
use function array_filter;
use function array_map;
use function array_shift;
use function array_values;
use function preg_match_all;
use function trim;

/**
 * Class AttributesReader
 *
 * Reads all attributes found in a given string and extract it as an object.
 */
final class AttributesReader implements AttributesReaderInterface
{
    /**
     * {@inheritDoc}
     */
    public function search(string $attributeName, array $contents): array
    {
        $list = array_map(static function (array $attributes) use ($attributeName): array {
            $matchAttribute = static fn(SqlAttribute $attr): bool => $attributeName === $attr->name;
            /** @var list<SqlAttribute> */
            return array_values(array_filter($attributes, $matchAttribute));
        }, array_map($this->read(...), $contents));

        return array_filter($list);
    }

    /**
     * {@inheritDoc}
     */
    public function read(string $string): array
    {
        preg_match_all('/#\[(?<name>\w+)(?:\((?<args>.*)\))?]/msU', $string, $matches);
        return array_map($this->buildSqlAttribute(...), $matches['name'], $matches['args']);
    }

    /**
     * {@inheritDoc}
     */
    public function readSingle(string $string): null|SqlAttribute
    {
        $attributes = $this->read($string);
        return array_shift($attributes);
    }

    /**
     * Build the SQL attribute object from the given info.
     *
     * @param string $name
     * @param string $args
     * @return SqlAttribute
     */
    private function buildSqlAttribute(string $name, string $args): SqlAttribute
    {
        preg_match_all('#(?:,\s*)*(?<key>\w+):\s*(?<value>"[^"]*"|\'[^\']*\'|[^,]+)#', $args, $matches);
        $arguments = array_combine($matches['key'], $matches['value']);
        // Trim simple and double quotes, if any.
        $arguments = array_map(static fn(string $_): string => trim($_, "\"'"), $arguments);
        return new SqlAttribute($name, $arguments);
    }
}
