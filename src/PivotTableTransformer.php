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

namespace Rekalogika\PivotTable;

use Rekalogika\PivotTable\Block\Block;
use Rekalogika\PivotTable\Block\Model\CubeCellDecorator;
use Rekalogika\PivotTable\Contracts\Cube\Cube;
use Rekalogika\PivotTable\HtmlTable\Table;

final readonly class PivotTableTransformer
{
    private function __construct() {}

    /**
     * @todo rename nodes to dimensions
     *
     * @param list<string> $unpivoted
     * @param list<string> $pivoted
     * @param list<string> $skipLegends
     * @param list<string> $measures
     * @param list<string> $withSubtotal
     */
    public static function transform(
        Cube $cube,
        array $unpivoted = [],
        array $pivoted = [],
        array $measures = [],
        array $skipLegends = ['@values'],
        array $withSubtotal = [],
    ): Table {
        $cubeCell = CubeCellDecorator::new($cube, $measures);

        $block = Block::new(
            cubeCell: $cubeCell,
            unpivoted: $unpivoted,
            pivoted: $pivoted,
            skipLegends: $skipLegends,
            withSubtotal: $withSubtotal,
        );

        return $block->generateTable();
    }
}
