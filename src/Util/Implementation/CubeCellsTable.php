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

namespace Rekalogika\PivotTable\Util\Implementation;

use Rekalogika\PivotTable\Contracts\Cube\CubeCell;
use Rekalogika\PivotTable\Contracts\Table\Table;

/**
 * @internal
 */
final readonly class CubeCellsTable implements Table
{
    /**
     * @param list<CubeCell> $cubes
     * @param array<string,mixed> $dimensionNames
     */
    public function __construct(
        private array $cubes,
        private array $dimensionNames,
    ) {}

    #[\Override]
    public function getRows(): iterable
    {
        foreach ($this->cubes as $cube) {
            if (!$cube->isNull()) {
                yield new CubeCellsRow($cube);
            }
        }
    }

    #[\Override]
    public function getLegend(string $key): mixed
    {
        return $this->dimensionNames[$key] ?? null;
    }

    #[\Override]
    public function getSubtotalLegend(string $key): mixed
    {
        /** @var mixed $legend */
        $legend = $this->dimensionNames[$key] ?? $key;
        if (\is_string($legend) || is_numeric($legend)) {
            return "Subtotal: " . (string) $legend;
        }

        return "Subtotal: " . $key;
    }
}
