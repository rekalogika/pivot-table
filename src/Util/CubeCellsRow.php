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
use Rekalogika\PivotTable\Contracts\Table\Row;

/**
 * @internal
 */
final readonly class CubeCellsRow implements Row
{
    public function __construct(
        private CubeCell $cube,
    ) {}

    #[\Override]
    public function getDimensions(): iterable
    {
        $tuple = $this->cube->getTuple();

        foreach ($tuple as $dimensionName => $dimension) {
            yield $dimensionName => $dimension->getMember();
        }
    }

    #[\Override]
    public function getMeasures(): iterable
    {
        // The cube value is treated as a measure
        // In a real implementation, you might want to extract multiple measures
        // from the cube, but this basic implementation treats the cube value as a single measure
        yield 'value' => $this->cube->getValue();
    }
}
