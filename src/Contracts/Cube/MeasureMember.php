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
 * MeasureMember represents a member of a measure in a cube. A measure member
 * will be the member of a dimension if that dimension represents a measure.
 *
 * A measure dimension is an instance of `Dimension` that will have these
 * properties:
 *
 * * `getName()` always returns `@values`
 * * `getLegend()` returns the name of the measure, e.g. 'Total Sales'
 * * `getMember()` returns an instance of this interface
 */
interface MeasureMember
{
    /**
     * Get the value of the measure member. This is the value of the measure for
     * this specific member. Example: 'sumOfSales'
     */
    public function getMeasureName(): string;

    /**
     * The legend of the measure member. This is a human-readable name for
     * the measure member. Example: 'Total Sales'
     */
    public function getLegend(): mixed;
}
