<?php

declare(strict_types=1);

namespace AareonFrance\SqlAttributesParser\Tests\Reader;

use AareonFrance\SqlAttributesParser\Reader\AttributesReader;
use AareonFrance\SqlAttributesParser\SqlAttribute;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AttributesReaderTest extends TestCase
{
    /**
     * @return Generator<string, array{string, list<SqlAttribute>}>
     */
    public static function provideRead(): Generator
    {
        yield 'Empty string' => ['', []];
        yield 'String but no attribute' => ['FooBar', []];
        yield 'Attribute with syntax error' => ['#[FooBar', []];
        yield 'Attribute with numbers and underscore' => ['#[Attribute_1]', [new SqlAttribute('Attribute_1', [])]];
        yield 'Invalid attribute name' => ['#[Invalid.attribute.name]', []];
        yield 'Attribute without argument' => ['#[MetaData]', [new SqlAttribute('MetaData', [])]];
        yield 'Attribute with no argument' => ['#[MetaData()]', [new SqlAttribute('MetaData', [])]];
        yield 'Attribute with named argument' => [
            '#[MetaData(arg1: value1)]',
            [new SqlAttribute('MetaData', ['arg1' => 'value1'])],
        ];
        yield 'Attribute with named arguments' => [
            '#[MetaData(arg1: value1, arg2: value2, arg3: value3)]',
            [new SqlAttribute('MetaData', ['arg1' => 'value1', 'arg2' => 'value2', 'arg3' => 'value3'])],
        ];
        yield 'Attribute with spaces, columns and commas, double quoted' => [
            '#[MetaData(comment: "Here, you can find what you hated the most: special chars…")]',
            [new SqlAttribute('MetaData', ['comment' => 'Here, you can find what you hated the most: special chars…'])],
        ];
        yield 'Attribute with spaces, columns and commas, simple quoted' => [
            "#[MetaData(comment: 'Here, you can find what you hated the most: special chars…')]",
            [new SqlAttribute('MetaData', ['comment' => 'Here, you can find what you hated the most: special chars…'])],
        ];
        yield 'Attribute with same named arguments' => [
            '#[MetaData(arg: a, arg: b, arg: c)]',
            [new SqlAttribute('MetaData', ['arg' => 'c'])],
        ];
        yield 'Many attributes' => [
            <<<'TXT'
            #[MetaData(foo: a, bar: b, baz: c)]
            #[MetaData(foo: A, bar: B, baz: C)]
            #[MetaData(foo: α, bar: β, baz: γ)]
            TXT,
            [
                new SqlAttribute('MetaData', ['foo' => 'a', 'bar' => 'b', 'baz' => 'c']),
                new SqlAttribute('MetaData', ['foo' => 'A', 'bar' => 'B', 'baz' => 'C']),
                new SqlAttribute('MetaData', ['foo' => 'α', 'bar' => 'β', 'baz' => 'γ']),
            ],
        ];
        yield 'Many attributes with specific wrapping comments' => [
            <<<'TXT'
            -- Ignored as starts with "##" so it is a bash-comment of an attribute. 
            ##[MetaData(foo: a, bar: b, baz: c)]
            -- With spaces before, it must work.
                #[MetaData(foo: A, bar: B, baz: C)]
            -- With comments after the attribute definition, it must work too.
            #[MetaData(foo: α, bar: β, baz: γ)] -- With comments.
            TXT,
            [
                new SqlAttribute('MetaData', ['foo' => 'A', 'bar' => 'B', 'baz' => 'C']),
                new SqlAttribute('MetaData', ['foo' => 'α', 'bar' => 'β', 'baz' => 'γ']),
            ],
        ];
    }

    /**
     * @param string $string
     * @param list<SqlAttribute> $sqlAttributes
     */
    #[DataProvider('provideRead')]
    public function testRead(string $string, array $sqlAttributes): void
    {
        $actual = (new AttributesReader())->read($string);
        self::assertEquals($sqlAttributes, $actual);
    }

    /**
     * @param string $string
     * @param list<SqlAttribute> $sqlAttributes
     */
    #[DataProvider('provideRead')]
    public function testReadSingle(string $string, array $sqlAttributes): void
    {
        $actual = (new AttributesReader())->readSingle($string);
        if ([] === $sqlAttributes) {
            self::assertNull($actual);
        } else {
            self::assertEquals($sqlAttributes[0], $actual);
        }
    }

    /**
     * @return Generator<string, array{string, list<SqlAttribute>}>
     */
    public static function provideSearch(): Generator
    {
        yield 'Empty string' => [[''], []];
        yield 'Not an attribute' => [['FooBar'], []];
        yield 'Attribute with syntax error' => [['#[FooBar'], []];

        yield 'All single attributes matches (list)' => [
            [
                '#[MetaData(foo: a, bar: b, baz: c)]',
                '#[MetaData(foo: A, bar: B, baz: C)]',
                '#[MetaData(foo: α, bar: β, baz: γ)]',
            ],
            [
                [new SqlAttribute('MetaData', ['foo' => 'a', 'bar' => 'b', 'baz' => 'c'])],
                [new SqlAttribute('MetaData', ['foo' => 'A', 'bar' => 'B', 'baz' => 'C'])],
                [new SqlAttribute('MetaData', ['foo' => 'α', 'bar' => 'β', 'baz' => 'γ'])],
            ],
        ];

        yield 'Some single attributes matches (list)' => [
            [
                '#[Unknown(foo: a, bar: b, baz: c)]',
                '#[MetaData(foo: A, bar: B, baz: C)]',
                '#[Unknown(foo: α, bar: β, baz: γ)]',
            ],
            [
                1 => [new SqlAttribute('MetaData', ['foo' => 'A', 'bar' => 'B', 'baz' => 'C'])],
            ],
        ];

        yield 'None single attribute matches (list)' => [
            [
                '#[Unknown(foo: a, bar: b, baz: c)]',
                '#[Unknown(foo: A, bar: B, baz: C)]',
                '#[Unknown(foo: α, bar: β, baz: γ)]',
            ],
            [],
        ];

        yield 'All single attributes matches (array)' => [
            [
                'first' => '#[MetaData(foo: a, bar: b, baz: c)]',
                'second' => '#[MetaData(foo: A, bar: B, baz: C)]',
                'third' => '#[MetaData(foo: α, bar: β, baz: γ)]',
            ],
            [
                'first' => [new SqlAttribute('MetaData', ['foo' => 'a', 'bar' => 'b', 'baz' => 'c'])],
                'second' => [new SqlAttribute('MetaData', ['foo' => 'A', 'bar' => 'B', 'baz' => 'C'])],
                'third' => [new SqlAttribute('MetaData', ['foo' => 'α', 'bar' => 'β', 'baz' => 'γ'])],
            ],
        ];

        yield 'Some single attributes matches (array)' => [
            [
                'first' => '#[Unknown(foo: a, bar: b, baz: c)]',
                'second' => '#[MetaData(foo: A, bar: B, baz: C)]',
                'third' => '#[Unknown(foo: α, bar: β, baz: γ)]',
            ],
            [
                'second' => [new SqlAttribute('MetaData', ['foo' => 'A', 'bar' => 'B', 'baz' => 'C'])],
            ],
        ];

        yield 'None single attribute matches (array)' => [
            [
                'first' => '#[Unknown(foo: a, bar: b, baz: c)]',
                'second' => '#[Unknown(foo: A, bar: B, baz: C)]',
                'third' => '#[Unknown(foo: α, bar: β, baz: γ)]',
            ],
            [],
        ];

        yield 'With strings having multiple attributes' => [
            [
                'one_not_filtered' => <<<'TXT'
                    #[MetaData(foo: a, bar: b, baz: c)]
                    TXT,
                'one_filtered' => <<<'TXT'
                    #[Unknown(foo: A, bar: B, baz: C)]
                    TXT,
                'many_keep_all' => <<<'TXT'
                    #[MetaData(foo: a, bar: b, baz: c)]
                    #[MetaData(foo: A, bar: B, baz: C)]
                    #[MetaData(foo: α, bar: β, baz: γ)]
                    TXT,
                'many_keep_some' => <<<'TXT'
                    #[Unknown(foo: a, bar: b, baz: c)]
                    #[MetaData(foo: A, bar: B, baz: C)]
                    #[Unknown(foo: α, bar: β, baz: γ)]
                    TXT,
                'many_keep_none' => <<<'TXT'
                    #[Unknown(foo: a, bar: b, baz: c)]
                    #[Unknown(foo: A, bar: B, baz: C)]
                    #[Unknown(foo: α, bar: β, baz: γ)]
                    TXT,
            ],
            [
                'one_not_filtered' => [
                    new SqlAttribute('MetaData', ['foo' => 'a', 'bar' => 'b', 'baz' => 'c']),
                ],
                'many_keep_all' => [
                    new SqlAttribute('MetaData', ['foo' => 'a', 'bar' => 'b', 'baz' => 'c']),
                    new SqlAttribute('MetaData', ['foo' => 'A', 'bar' => 'B', 'baz' => 'C']),
                    new SqlAttribute('MetaData', ['foo' => 'α', 'bar' => 'β', 'baz' => 'γ']),
                ],
                'many_keep_some' => [
                    new SqlAttribute('MetaData', ['foo' => 'A', 'bar' => 'B', 'baz' => 'C']),
                ],
            ],
        ];
    }

    /**
     * @param array<string> $strings
     * @param list<SqlAttribute> $sqlAttributes
     */
    #[DataProvider('provideSearch')]
    public function testSearch(array $strings, array $sqlAttributes): void
    {
        $actual = (new AttributesReader())->search('MetaData', $strings);
        self::assertEquals($sqlAttributes, $actual);
    }
}
