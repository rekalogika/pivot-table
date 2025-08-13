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

namespace Rekalogika\PivotTable\Implementation\Table;

use Rekalogika\PivotTable\Table\Row;
use Rekalogika\PivotTable\Table\TableSection;

/**
 * @implements \IteratorAggregate<Row>
 */
abstract class DefaultRowGroup implements TableSection, \IteratorAggregate
{
    public function __construct(
        private readonly DefaultRows $rows,
    ) {}

    #[\Override]
    public function count(): int
    {
        return $this->rows->count();
    }

    /**
     * @return \Traversable<DefaultRow>
     */
    #[\Override]
    public function getIterator(): \Traversable
    {
        return $this->rows->getIterator();
    }
}
