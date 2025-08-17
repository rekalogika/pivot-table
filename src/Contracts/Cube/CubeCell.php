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
 * CubeCell represents a cell in a cube. It contains a tuple of dimensions that
 * uniquely identifies the cell and its value.
 */
interface CubeCell
{
    /**
     * Tuple is a collection of dimensions that uniquely identifies this cell in
     * the cube. Key is the dimension name.
     *
     * @return array<string,Dimension>
     */
    public function getTuple(): array;

    /**
     * The value of this cell. It can be of any type.
     */
    public function getValue(): mixed;

    /**
     * If null, then the cell does not exist in the result set, but created by
     * the framework to balance the cube.
     */
    public function isNull(): bool;

    /**
     * Slice the cube by a specific dimension and member. Slicing adds a new
     * dimension having a specific member to the tuple of the resulting cell.
     */
    public function slice(string $dimensionName, mixed $member): CubeCell;

    /**
     * Drill down the cube by a specific dimension. Drilling down adds a new
     * dimension to the tuple of the resulting cells.
     *
     * @return iterable<CubeCell>
     */
    public function drillDown(string $dimensionName): iterable;

    /**
     * Roll up the cube by a specific dimension. Rolling up removes the
     * specified dimension from the tuple of the resulting cell.
     */
    public function rollUp(string $dimensionName): CubeCell;
}
