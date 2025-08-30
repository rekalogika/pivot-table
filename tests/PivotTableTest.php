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
     * @param list<string> $rows
     * @param list<string> $columns
     * @param list<string> $measures
     * @param list<string> $subtotals
     * @param string $expectedFile
     * @dataProvider dataProvider
     */
    public function testPivotTable(
        string $inputFile,
        array $rows,
        array $columns,
        array $measures,
        string $expectedFile,
        ?array $subtotals = null,
        bool $hasGrouping = true,
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
            groupingField: $hasGrouping ? 'grouping' : null,
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
            rows: $rows,
            columns: $columns,
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
     * @return iterable<string,array{inputFile:string,rows:list<string>,columns:list<string>,measures:list<string>,expectedFile:string,subtotals?:list<string>,hasGrouping?:bool}>
     */
    public static function dataProvider(): iterable
    {
        // 1r 2c 3m = 1 rows dimensions, 2 columns dimensions, 3 measures

        yield 'empty no dimension no measures' => [
            'inputFile' => 'empty.json',
            'rows' => [],
            'columns' => [],
            'measures' => [],
            'expectedFile' => 'empty-no-dimension-no-measures.md',
        ];

        yield 'non-empty no dimension no measures' => [
            'inputFile' => 'empty.json',
            'rows' => [],
            'columns' => [],
            'measures' => [],
            'expectedFile' => 'non-empty-no-dimension-no-measures.md',
        ];

        yield 'empty no dimension' => [
            'inputFile' => 'empty.json',
            'rows' => [],
            'columns' => ['@values'],
            'measures' => ['count', 'sum'],
            'expectedFile' => 'empty-no-dimension.md',
        ];

        yield 'empty with rows' => [
            'inputFile' => 'empty.json',
            'rows' => ['name'],
            'columns' => ['@values'],
            'measures' => ['count', 'sum'],
            'expectedFile' => 'empty-with-rows.md',
        ];

        yield '1m, columns values' => [
            'inputFile' => 'cube.json',
            'rows' => [],
            'columns' => ['@values'],
            'measures' => ['count'],
            'expectedFile' => '1m-columns-values.md',
        ];

        yield '1m, rows values' => [
            'inputFile' => 'cube.json',
            'rows' => ['@values'],
            'columns' => [],
            'measures' => ['count'],
            'expectedFile' => '1m-rows-values.md',
        ];

        yield '2m, columns values' => [
            'inputFile' => 'cube.json',
            'rows' => [],
            'columns' => ['@values'],
            'measures' => ['count', 'sum'],
            'expectedFile' => '2m-columns-values.md',
        ];

        yield '2m, rows values' => [
            'inputFile' => 'cube.json',
            'rows' => ['@values'],
            'columns' => [],
            'measures' => ['count', 'sum'],
            'expectedFile' => '2m-rows-values.md',
        ];

        // maybe revisit?
        yield '1r, rows values' => [
            'inputFile' => 'cube.json',
            'rows' => ['name', '@values'],
            'columns' => [],
            'measures' => [],
            'expectedFile' => '1r-rows-values.md',
        ];

        yield '1c, rows values' => [
            'inputFile' => 'cube.json',
            'rows' => ['@values'],
            'columns' => ['name'],
            'measures' => [],
            'expectedFile' => '1c-rows-values.md',
        ];

        yield '1r1m, columns values' => [
            'inputFile' => 'cube.json',
            'rows' => ['name'],
            'columns' => ['@values'],
            'measures' => ['count'],
            'expectedFile' => '1r1m-columns-values.md',
        ];

        yield '1r1m, rows values' => [
            'inputFile' => 'cube.json',
            'rows' => ['name', '@values'],
            'columns' => [],
            'measures' => ['count'],
            'expectedFile' => '1r1m-rows-values.md',
        ];

        yield '1r1m, columns values, subtotal' => [
            'inputFile' => 'cube.json',
            'rows' => ['name'],
            'columns' => ['@values'],
            'measures' => ['count'],
            'subtotals' => ['name'],
            'expectedFile' => '1r1m-columns-values-subtotals.md',
        ];

        yield '1r1m, rows values, subtotal' => [
            'inputFile' => 'cube.json',
            'rows' => ['name', '@values'],
            'columns' => [],
            'measures' => ['count'],
            'subtotals' => ['name'],
            'expectedFile' => '1r1m-rows-values-subtotals.md',
        ];

        yield '1r2m, columns values' => [
            'inputFile' => 'cube.json',
            'rows' => ['name'],
            'columns' => ['@values'],
            'measures' => ['count', 'sum'],
            'expectedFile' => '1r2m-columns-values.md',
        ];

        yield '1r2m, rows values' => [
            'inputFile' => 'cube.json',
            'rows' => ['name', '@values'],
            'columns' => [],
            'measures' => ['count', 'sum'],
            'expectedFile' => '1r2m-rows-values.md',
        ];

        yield '1r2m, columns values, subtotal' => [
            'inputFile' => 'cube.json',
            'rows' => ['name'],
            'columns' => ['@values'],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name'],
            'expectedFile' => '1r2m-columns-values-subtotals.md',
        ];

        yield '1r2m, rows values, subtotal' => [
            'inputFile' => 'cube.json',
            'rows' => ['name', '@values'],
            'columns' => [],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name'],
            'expectedFile' => '1r2m-rows-values-subtotals.md',
        ];

        yield '1r2m, value first rows, subtotal' => [
            'inputFile' => 'cube.json',
            'rows' => ['@values', 'name'],
            'columns' => [],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name'],
            'expectedFile' => '1r2m-values-first-rows-subtotals.md',
        ];

        yield '1c2m, value first columns, subtotal' => [
            'inputFile' => 'cube.json',
            'rows' => [],
            'columns' => ['@values', 'name'],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name'],
            'expectedFile' => '1c2m-values-first-columns-subtotals.md',
        ];

        yield '1c2r1m, value first columns, subtotal' => [
            'inputFile' => 'cube.json',
            'rows' => ['country', 'month'],
            'columns' => ['name', '@values'],
            'measures' => ['count'],
            'subtotals' => ['name', 'country', 'month'],
            'expectedFile' => '1c2r1m-values-columns-subtotals.md',
        ];

        yield '1c2r2m, value last columns, subtotal' => [
            'inputFile' => 'cube.json',
            'rows' => ['country', 'month'],
            'columns' => ['name', '@values'],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name', 'country', 'month'],
            'expectedFile' => '1c2r2m-values-last-columns-subtotals.md',
        ];

        yield '1c2r2m, value first columns, subtotal' => [
            'inputFile' => 'cube.json',
            'rows' => ['country', 'month'],
            'columns' => ['@values', 'name'],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name', 'country', 'month'],
            'expectedFile' => '1c2r2m-values-first-columns-subtotals.md',
        ];

        yield '1c2r2m, rollup, value last columns, subtotal' => [
            'inputFile' => 'rollup.json',
            'rows' => ['name', 'country'],
            'columns' => ['month', '@values'],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name', 'country', 'month'],
            'expectedFile' => '1c2r2m-rollup-values-last-columns-subtotals.md',
        ];

        yield '1c2r2m, rollup, value first columns, subtotal' => [
            'inputFile' => 'rollup.json',
            'rows' => ['name', 'country'],
            'columns' => ['@values', 'month'],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name', 'country', 'month'],
            'expectedFile' => '1c2r2m-rollup-values-first-columns-subtotals.md',
        ];

        yield '1c2r2m, no grouping, value last columns, subtotal' => [
            'inputFile' => 'nogrouping.json',
            'rows' => ['name', 'country'],
            'columns' => ['month', '@values'],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name', 'country', 'month'],
            'expectedFile' => '1c2r2m-no-grouping-values-last-columns-subtotals.md',
            'hasGrouping' => false,
        ];

        yield '1c2r2m, no grouping, value first columns, subtotal' => [
            'inputFile' => 'nogrouping.json',
            'rows' => ['name', 'country'],
            'columns' => ['@values', 'month'],
            'measures' => ['count', 'sum'],
            'subtotals' => ['name', 'country', 'month'],
            'expectedFile' => '1c2r2m-no-grouping-values-first-columns-subtotals.md',
            'hasGrouping' => false,
        ];
    }
}
