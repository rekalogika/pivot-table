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
use Rekalogika\PivotTable\TableToCubeAdapter\TableToCubeAdapter;

final class PivotTableTest extends TestCase
{
    /**
     * @param string $inputFile
     * @param list<string> $unpivoted
     * @param list<string> $pivoted
     * @param list<string> $measureFields
     * @param string $expectedFile
     * @dataProvider dataProvider
     */
    public function testPivotTable(
        string $inputFile,
        array $unpivoted,
        array $pivoted,
        array $measureFields,
        string $expectedFile,
    ): void {
        $inputFilePath = __DIR__ . '/resultset/' . $inputFile;
        $this->assertFileExists($inputFilePath);

        $fileContent = file_get_contents($inputFilePath);
        $this->assertNotFalse($fileContent);

        $data = json_decode($fileContent, true);
        $this->assertIsArray($data);

        /** @var list<array<string,mixed>> $data */

        $legends = [
            '@values' => 'Values',
            'name' => 'Name',
            'country' => 'Country',
            'month' => 'Month',
            'count' => 'Count',
            'sum' => 'Sum',
        ];

        $table = ArrayTableFactory::createTable(
            input: $data,
            dimensionFields: ['name', 'country', 'month'],
            measureFields: ['count', 'sum'],
            groupingField: 'grouping',
            legends: $legends,
        );

        $cube = TableToCubeAdapter::adapt($table);

        $htmlTable = PivotTableTransformer::transform(
            cube: $cube,
            unpivotedNodes: $unpivoted,
            pivotedNodes: $pivoted,
            skipLegends: ['@values'],
            createSubtotals: [],
        );

        $string = (new BasicTableRenderer())->getHtml($htmlTable);

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        /** @psalm-suppress ArgumentTypeCoercion */
        $dom->loadXML($string, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);

        $string = $dom->saveXML();
        $this->assertIsString($string);
        $string = str_replace('<?xml version="1.0"?>', '', $string);
        $string = trim($string);

        $expectedFilePath = __DIR__ . '/expectation/' . $expectedFile;
        $this->assertFileExists($expectedFilePath);

        $expectedContent = file_get_contents($expectedFilePath);
        $this->assertNotFalse($expectedContent);

        // Compare the generated HTML with the expected HTML
        $this->assertEquals(trim($expectedContent), trim($string));
    }

    /**
     * Data provider for testPivotTable.
     *
     * @return iterable<string,array{inputFile:string,unpivoted:list<string>,pivoted:list<string>,measureFields:list<string>,expectedFile:string}>
     */
    public static function dataProvider(): iterable
    {
        yield 'Basic' => [
            'inputFile' => 'cube.json',
            'unpivoted' => ['name'],
            'pivoted' => ['@values'],
            'measureFields' => ['count', 'sum'],
            'expectedFile' => 'basic.html',
        ];
    }
}
