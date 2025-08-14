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

namespace Rekalogika\PivotTable\Implementation;

use Rekalogika\PivotTable\HtmlTable\Element;
use Rekalogika\PivotTable\HtmlTable\Row;

/**
 * @implements \IteratorAggregate<Row>
 * @internal
 */
abstract class DefaultTableSection implements \IteratorAggregate, \Countable, Element
{
    final public function __construct(
        private readonly DefaultRows $rows,
        private readonly mixed $context,
    ) {}

    #[\Override]
    final public function getContext(): mixed
    {
        return $this->context;
    }

    /**
     * @return \Traversable<DefaultRow>
     */
    #[\Override]
    final public function getIterator(): \Traversable
    {
        return $this->rows->getIterator();
    }

    #[\Override]
    final public function count(): int
    {
        return $this->rows->count();
    }
}
