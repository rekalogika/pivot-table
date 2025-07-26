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
use Rekalogika\PivotTable\Implementation\Table\DefaultHeaderCell;
use Rekalogika\PivotTable\Implementation\Table\DefaultRows;

final class PivotLeafBlock extends LeafBlock
{
    #[\Override]
    public function getHeaderRows(): DefaultRows
    {
        if (
            $this->getContext()->hasSuperfluousLegend($this->getTreeNode())
        ) {
            $cell = new DefaultHeaderCell(
                name: $this->getTreeNode()->getKey(),
                content: $this->getTreeNode()->getItem(),
                generatingBlock: $this,
            );
        } else {
            $cell = new DefaultDataCell(
                name: $this->getTreeNode()->getKey(),
                content: $this->getTreeNode()->getItem(),
                generatingBlock: $this,
            );
        }

        return DefaultRows::createFromCell($cell, $this);
    }

    #[\Override]
    public function getDataRows(): DefaultRows
    {
        $cell = new DefaultDataCell(
            name: $this->getTreeNode()->getKey(),
            content: $this->getTreeNode()->getValue(),
            generatingBlock: $this,
        );

        return DefaultRows::createFromCell($cell, $this);
    }

    #[\Override]
    public function getSubtotalHeaderRows(
        Subtotals $subtotals,
    ): DefaultRows {
        // $leafNode = $subtotals->takeOne();

        $cell = new DefaultHeaderCell(
            name: 'total',
            content: 'Total',
            generatingBlock: $this,
        );

        $rows = DefaultRows::createFromCell($cell, $this);

        // if ($leafNode->getKey() === '@values') {
        //     $rows = $rows->appendBelow(
        //         DefaultRows::createFromCell(
        //             new DefaultHeaderCell(
        //                 name: $leafNode->getKey(),
        //                 content: $leafNode->getItem(),
        //                 generatingBlock: $this,
        //             ),
        //             $this,
        //         ),
        //     );
        // }

        return $rows;
    }

    #[\Override]
    public function getSubtotalDataRows(
        Subtotals $subtotals,
    ): DefaultRows {
        $leafNode = $subtotals->takeOne();

        $cell = new DefaultFooterCell(
            name: $leafNode->getKey(),
            content: $leafNode->getValue(),
            generatingBlock: $this,
        );

        return DefaultRows::createFromCell($cell, $this);
    }

    #[\Override]
    public function getDataPaddingRows(): DefaultRows
    {
        $cell = new DefaultFooterCell(
            name: '',
            content: '',
            generatingBlock: $this,
        );

        return DefaultRows::createFromCell($cell, $this);
    }
}
