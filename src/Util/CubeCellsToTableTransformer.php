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

namespace Rekalogika\PivotTable\Util;

use Rekalogika\PivotTable\Contracts\Cube\CubeCell;
use Rekalogika\PivotTable\Contracts\Table\Table;
use Rekalogika\PivotTable\Util\Implementation\CubeCellsTable;

final class CubeCellsToTableTransformer
{
    /**
     * @var list<CubeCell>
     */
    private array $cubes;

    /**
     * @var array<string,mixed>
     */
    private array $dimensionNames = [];

    /**
     * Transforms a Cube into a Table.
     *
     * @param iterable<CubeCell> $cubes The cube to transform.
     * @return Table The transformed table.
     */
    public static function transform(
        iterable $cubes,
    ): Table {
        $self = new self($cubes);

        return $self->getTable();
    }

    /**
     * @param iterable<CubeCell> $cubes The cube to transform.
     */
    private function __construct(iterable $cubes)
    {
        $this->cubes = array_values(\is_array($cubes) ? $cubes : iterator_to_array($cubes));
        $this->analyzeDimensionNames();
    }

    private function analyzeDimensionNames(): void
    {
        if (empty($this->cubes)) {
            return;
        }

        // Analyze the first cube cell to get dimension names
        $firstCube = $this->cubes[0];
        $tuple = $firstCube->getTuple();

        foreach ($tuple as $dimensionName => $dimension) {
            $this->dimensionNames[$dimensionName] = $dimension->getLegend();
        }
    }

    private function getTable(): Table
    {
        return new CubeCellsTable($this->cubes, $this->dimensionNames);
    }
}
