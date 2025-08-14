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
use Rekalogika\PivotTable\ArrayTable\ArrayTable;
use Rekalogika\PivotTable\ArrayTable\ArrayTableFactory;

final class PivotTableTest extends TestCase
{
    private ArrayTable $table;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $inputFile = __DIR__ . '/resultset/cube.json';
        $this->assertFileExists($inputFile);

        $fileContent = file_get_contents($inputFile);
        $this->assertNotFalse($fileContent);

        $data = json_decode($fileContent, true);
        $this->assertIsArray($data);

        /** @var list<array<string,mixed>> $data */

        $tableFactory = new ArrayTableFactory(
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
        );

        /**
         * @psalm-suppress ArgumentTypeCoercion
         * @phpstan-ignore argument.type
         */
        $this->table = $tableFactory->create($data);
    }

    public function testTree(): void
    {
        $table = $this->table;
        $rows = iterator_to_array($table->getRows());

        $this->assertNotEmpty($rows);
    }
}
