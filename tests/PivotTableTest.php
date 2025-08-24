<?php

declare(strict_types=1);

/*
 * This file is part of rekalogika/pivot-table package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\Analytics\Tests\UnitTests\PivotTable;

use PHPUnit\Framework\TestCase;
use Rekalogika\PivotTable\ArrayTable\ArrayTableFactory;
use Rekalogika\PivotTable\PivotTableTransformer;
use Rekalogika\PivotTable\TableRenderer\BasicTableRenderer;

final class PivotTableTest extends TestCase
{
    /**
     * @param string $inputFile
     * @param list<string> $unpivoted
     * @param list<string> $pivoted
     * @param list<string> $measures
     * @param list<string> $subtotals
     * @param string $expectedFile
     * @dataProvider dataProvider
     */
    public function testPivotTable(
        string $inputFile,
        array $unpivoted,
        array $pivoted,
        array $measures,
        string $expectedFile,
        ?array $subtotals = null,
    ): void {
        $inputFilePath = __DIR__ . '/resultset/' . $inputFile;
        $this->assertFileExists($inputFilePath);

        $fileContent = file_get_contents($inputFilePath);
        $this->assertNotFalse($fileContent);

        $data = json_decode($fileContent, true);
        $this->assertIsArray($data);

        /** @var list<array<string,mixed>> $data */

        // convert result set to cube
        $cube = ArrayTableFactory::createCube(
            input: $data,
            dimensionFields: ['name', 'country', 'month'],
            measureFields: ['count', 'sum'],
            groupingField: 'grouping',
            legends: [
                '@values' => 'Values',
                'name' => 'Name',
                'country' => 'Country',
                'month' => 'Month',
                'count' => 'Count',
                'sum' => 'Sum',
            ],
            subtotalLabels: [
                'name' => 'All names',
                'country' => 'All countries',
                'month' => 'All months',
            ],
        );

        // convert cube to html table object
        $htmlTable = PivotTableTransformer::transform(
            cube: $cube,
            unpivoted: $unpivoted,
            pivoted: $pivoted,
            measures: $measures,
            skipLegends: ['@values'],
            withSubtotal: $subtotals ?? [],
        );

        // convert html table object to html string
        $string = BasicTableRenderer::render($htmlTable);

        // pretty print the HTML
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        if ($string !== '') {
            $dom->loadXML($string, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);

            $string = $dom->saveXML(options: LIBXML_NOEMPTYTAG | LIBXML_NOXMLDECL);
            $this->assertIsString($string);
            $string = trim($string);
        }

        // remove XML preamble
        $string = preg_replace('/^<\?xml.*?\?>\s*/', '', $string);
        $this->assertIsString($string);

        // save to output directory
        $outputFilePath = __DIR__ . '/output/' . $expectedFile;
        file_put_contents($outputFilePath, $string);

        // check if the expected file exists
        $expectedFilePath = __DIR__ . '/expectation/' . $expectedFile;
        $this->assertFileExists($expectedFilePath);

        // load the expected content
        $expectedContent = file_get_contents($expectedFilePath);
        $this->assertNotFalse($expectedContent);

        // Compare the generated HTML with the expected HTML
        $this->assertEquals(trim($expectedContent), trim($string));
    }

    /**
     * Data provider for testPivotTable.
     *
     * @return iterable<string,array{inputFile:string,unpivoted:list<string>,pivoted:list<string>,measures:list<string>,expectedFile:string,subtotals?:list<string>}>
     */
    public static function dataProvider(): iterable
    {
        // 1u 2p 3m = 1 unpivoted dimensions, 2 pivoted dimensions, 3 measures

        yield 'empty' => [
            'inputFile' => 'empty.json',
            'unpivoted' => ['name'],
            'pivoted' => ['@values'],
            'measures' => ['count', 'sum'],
            'expectedFile' => 'empty.md',
        ];

        yield '1m, pivoted values' => [
            'inputFile' => 'cube.json',
            'unpivoted' => [],
            'pivoted' => ['@values'],
            'measures' => ['count'],
            'expectedFile' => '1m-pivoted-values.md',
        ];

        yield '1m, unpivoted values' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['@values'],
            'pivoted' => [],
            'measures' => ['count'],
            'expectedFile' => '1m-unpivoted-values.md',
        ];

        yield '2m, pivoted values' => [
            'inputFile' => 'cube.json',
            'unpivoted' => [],
            'pivoted' => ['@values'],
            'measures' => ['count', 'sum'],
            'expectedFile' => '2m-pivoted-values.md',
        ];

        yield '2m, unpivoted values' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['@values'],
            'pivoted' => [],
            'measures' => ['count', 'sum'],
            'expectedFile' => '2m-unpivoted-values.md',
        ];

        // maybe revisit?
        yield '1u, unpivoted values' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['name', '@values'],
            'pivoted' => [],
            'measures' => [],
            'expectedFile' => '1u-unpivoted-values.md',
        ];

        yield '1p, unpivoted values' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['@values'],
            'pivoted' => ['name'],
            'measures' => [],
            'expectedFile' => '1p-unpivoted-values.md',
        ];

        yield '1u1m, pivoted values' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['name'],
            'pivoted' => ['@values'],
            'measures' => ['count'],
            'expectedFile' => '1u1m-pivoted-values.md',
        ];

        yield '1u1m, unpivoted values' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['name', '@values'],
            'pivoted' => [],
            'measures' => ['count'],
            'expectedFile' => '1u1m-unpivoted-values.md',
        ];

        yield '1u1m, pivoted values, subtotal' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['name'],
            'pivoted' => ['@values'],
            'measures' => ['count'],
            'subtotals' => ['name'],
            'expectedFile' => '1u1m-pivoted-values-subtotals.md',
        ];

        yield '1u1m, unpivoted values, subtotal' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['name', '@values'],
            'pivoted' => [],
            'measures' => ['count'],
            'subtotals' => ['name'],
            'expectedFile' => '1u1m-unpivoted-values-subtotals.md',
        ];

        yield '1u2m, pivoted values' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['name'],
            'pivoted' => ['@values'],
            'measures' => ['count', 'sum'],
            'expectedFile' => '1u2m-pivoted-values.md',
        ];

        yield '1u2m, unpivoted values' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['name', '@values'],
            'pivoted' => [],
            'measures' => ['count', 'sum'],
            'expectedFile' => '1u2m-unpivoted-values.md',
        ];

        yield '1u2m, pivoted values, subtotal' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['name'],
            'pivoted' => ['@values'],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name'],
            'expectedFile' => '1u2m-pivoted-values-subtotals.md',
        ];

        yield '1u2m, unpivoted values, subtotal' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['name', '@values'],
            'pivoted' => [],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name'],
            'expectedFile' => '1u2m-unpivoted-values-subtotals.md',
        ];

        yield '1u2m, value first unpivoted, subtotal' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['@values', 'name'],
            'pivoted' => [],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name'],
            'expectedFile' => '1u2m-values-first-unpivoted-subtotals.md',
        ];

        yield '1p2m, value first pivoted, subtotal' => [
            'inputFile' => 'cube.json',
            'unpivoted' => [],
            'pivoted' => ['@values', 'name'],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name'],
            'expectedFile' => '1p2m-values-first-pivoted-subtotals.md',
        ];

        yield '1p2u1m, value first pivoted, subtotal' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['country', 'month'],
            'pivoted' => ['name', '@values'],
            'measures' => ['count'],
            'subtotals' => ['name', 'country', 'month'],
            'expectedFile' => '1p2u1m-values-pivoted-subtotals.md',
        ];

        yield '1p2u2m, value last pivoted, subtotal' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['country', 'month'],
            'pivoted' => ['name', '@values'],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name', 'country', 'month'],
            'expectedFile' => '1p2u2m-values-last-pivoted-subtotals.md',
        ];

        yield '1p2u2m, value first pivoted, subtotal' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['country', 'month'],
            'pivoted' => ['@values', 'name'],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name', 'country', 'month'],
            'expectedFile' => '1p2u2m-values-first-pivoted-subtotals.md',
        ];
    }
}
