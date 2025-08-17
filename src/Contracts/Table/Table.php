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

namespace Rekalogika\PivotTable\Contracts\Table;

/**
 * Represents a table that contains rows of data. Usually they come from a
 * database query.
 */
interface Table
{
    /**
     * Returns the rows of the table.
     *
     * @return iterable<Row>
     */
    public function getRows(): iterable;

    /**
     * Gets the legend of a dimension or a measure. The special key `@values`
     * is used to get the legend of the measure dimension.
     */
    public function getLegend(string $key): mixed;

    /**
     * Returns the subtotal description for a specific dimension. Example:
     * 'All Countries', or just 'Total'.
     */
    public function getSubtotalLabel(string $key): mixed;
}
