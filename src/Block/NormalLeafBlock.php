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

use Rekalogika\PivotTable\Block\Util\Subtotals;
use Rekalogika\PivotTable\Implementation\Table\DefaultDataCell;
use Rekalogika\PivotTable\Implementation\Table\DefaultFooterCell;
use Rekalogika\PivotTable\Implementation\Table\DefaultFooterHeaderCell;
use Rekalogika\PivotTable\Implementation\Table\DefaultHeaderCell;
use Rekalogika\PivotTable\Implementation\Table\DefaultRow;
use Rekalogika\PivotTable\Implementation\Table\DefaultRows;

final class NormalLeafBlock extends LeafBlock
{
    #[\Override]
    public function getHeaderRows(): DefaultRows
    {
        $cell = new DefaultHeaderCell(
            name: $this->getTreeNode()->getKey(),
            content: $this->getTreeNode()->getLegend(),
            columnSpan: 2,
            generatingBlock: $this,
        );

        return DefaultRows::createFromCell($cell, $this);
    }

    #[\Override]
    public function getDataRows(): DefaultRows
    {
        $name = new DefaultDataCell(
            name: $this->getTreeNode()->getKey(),
            content: $this->getTreeNode()->getItem(),
            generatingBlock: $this,
        );

        $value = new DefaultDataCell(
            name: $this->getTreeNode()->getKey(),
            content: $this->getTreeNode()->getValue(),
            generatingBlock: $this,
        );

        $row = new DefaultRow([$name, $value], $this);

        return new DefaultRows([$row], $this);
    }

    #[\Override]
    public function getSubtotalHeaderRows(
        Subtotals $subtotals,
    ): DefaultRows {
        throw new \BadMethodCallException('Not implemented yet');
    }

    #[\Override]
    public function getSubtotalDataRows(
        Subtotals $subtotals,
    ): DefaultRows {
        $leafNode = $subtotals->takeOne();

        if (\count($subtotals) > 1) {
            if ($this->getTreeNode()->getKey() === '@values') {
                $name = new DefaultFooterHeaderCell(
                    name: $leafNode->getKey(),
                    content: $leafNode->getItem(),
                    generatingBlock: $this,
                );
            } else {
                $name = new DefaultFooterHeaderCell(
                    name: '',
                    content: '',
                    generatingBlock: $this,
                );
            }
        } else {
            $name = new DefaultFooterHeaderCell(
                name: '',
                content: 'Total',
                generatingBlock: $this,
            );
        }

        $value = new DefaultFooterCell(
            name: $leafNode->getKey(),
            content: $leafNode->getValue(),
            generatingBlock: $this,
        );

        $row = new DefaultRow([$name, $value], $this);

        return new DefaultRows([$row], $this);
    }

    #[\Override]
    public function getDataPaddingRows(): DefaultRows
    {
        throw new \BadMethodCallException('Not implemented yet');
    }
}
