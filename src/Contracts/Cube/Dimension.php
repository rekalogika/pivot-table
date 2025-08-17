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
 * Dimension represents a dimension in a cube.
 */
interface Dimension
{
    /**
     * Get the identifier of the dimension. Example: 'country'. If the dimension
     * is a measure dimension, the name must be `@values`.
     */
    public function getName(): string;

    /**
     * Get the legend of the dimension. The legend is a human-readable name for
     * the dimension. Example: 'Country of Origin'
     */
    public function getLegend(): mixed;

    /**
     * Get the member of the dimension. The member is a specific value of the
     * dimension. Example: 'Papua New Guinea'. If the dimension is a measure
     * dimension, the member must be an instance of `MeasureMember`.
     */
    public function getMember(): mixed;
}
