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

namespace Rekalogika\PivotTable\Contracts\Cube;

/**
 * Cube represents a multidimensional data structure that contains cells.
 */
interface Cube
{
    /**
     * Get the apex cell of the cube. The apex cell is the root cell that
     * contains no dimensions in its tuple. It is the starting point of the
     * cube.
     */
    public function getApexCell(): CubeCell;

    /**
     * Returns the description of the subtotal field for a specific dimension.
     */
    public function getSubtotalDescription(string $dimensionName): mixed;
}
