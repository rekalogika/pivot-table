<?php

declare(strict_types=1);

/*
 * This file is part of rekalogika/analytics package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\PivotTable\Block;

use Rekalogika\PivotTable\Implementation\Table\DefaultDataCell;
use Rekalogika\PivotTable\Implementation\Table\DefaultHeaderCell;
use Rekalogika\PivotTable\Implementation\Table\DefaultRow;
use Rekalogika\PivotTable\Implementation\Table\DefaultRows;

final class NormalLeafBlock extends LeafBlock
{
    #[\Override]
    protected function createHeaderRows(): DefaultRows
    {
        $cell = new DefaultHeaderCell(
            name: $this->getTreeNode()->getKey(),
            content: $this->getTreeNode()->getLegend(),
            columnSpan: 2,
        );

        $row = new DefaultRow([$cell]);

        return new DefaultRows([$row]);
    }

    #[\Override]
    protected function createDataRows(): DefaultRows
    {
        $name = new DefaultDataCell(
            name: $this->getTreeNode()->getKey(),
            content: $this->getTreeNode()->getItem(),
        );

        $value = new DefaultDataCell(
            name: $this->getTreeNode()->getKey(),
            content: $this->getTreeNode()->getValue(),
        );

        $row = new DefaultRow([$name, $value]);

        return new DefaultRows([$row]);
    }
}
