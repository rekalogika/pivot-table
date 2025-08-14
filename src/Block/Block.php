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

namespace Rekalogika\PivotTable\Block;

use Rekalogika\PivotTable\Block\BranchBlock\NormalBlock;
use Rekalogika\PivotTable\Block\BranchBlock\PivotBlock;
use Rekalogika\PivotTable\Block\BranchBlock\RootBlock;
use Rekalogika\PivotTable\Block\Context\BlockContext;
use Rekalogika\PivotTable\Block\LeafBlock\NormalLeafBlock;
use Rekalogika\PivotTable\Block\LeafBlock\PivotLeafBlock;
use Rekalogika\PivotTable\Block\Model\CubeCellDecorator;
use Rekalogika\PivotTable\Block\Result\DefaultRows;
use Rekalogika\PivotTable\Block\Result\DefaultTable;
use Rekalogika\PivotTable\Block\Result\DefaultTableBody;
use Rekalogika\PivotTable\Block\Result\DefaultTableFooter;
use Rekalogika\PivotTable\Block\Result\DefaultTableHeader;

abstract class Block implements \Stringable
{
    private ?DefaultContext $elementContext = null;

    protected function __construct(
        private readonly BlockContext $context,
    ) {}

    #[\Override]
    public function __toString(): string
    {
        return \sprintf(
            '%s(level: %d)',
            static::class,
            $this->getLevel(),
        );
    }

    protected function getElementContext(): DefaultContext
    {
        return $this->elementContext ??= new DefaultContext(
            depth: $this->getLevel(),
            subtotalDepth: $this->getContext()->getSubtotalDepth(),
            generatingBlock: $this,
        );
    }

    /**
     * @return int<0,max>
     */
    final protected function getLevel(): int
    {
        return $this->context->getBlockDepth();
    }

    final protected function createBlock(CubeCellDecorator $cube): Block
    {
        $context = $this->getContext();
        $context = $context->pushKey();

        if ($cube->isSubtotal()) {
            $context = $context->incrementSubtotal();
        }

        if (!$context->isLeaf()) {
            if ($context->isKeyPivoted()) {
                return new PivotBlock(
                    cube: $cube,
                    parent: $this,
                    context: $context,
                );
            } else {
                return new NormalBlock(
                    cube: $cube,
                    parent: $this,
                    context: $context,
                );
            }
        } else {
            if ($context->isKeyPivoted()) {
                return new PivotLeafBlock(
                    cube: $cube,
                    parent: $this,
                    context: $context,
                );
                // @todo restore functionality
                // } elseif (
                //     $parentNode !== null
                //     && $level > 0
                //     && \count($parentNode->getBalancedChildren(1, $level - 1)) === 1
                // ) {
                //     return new SingleNodeLeafBlock(
                //         cube: $cube,
                //         parent: $this,
                //         context: $context,
                //     );
            } else {
                return new NormalLeafBlock(
                    cube: $cube,
                    parent: $this,
                    context: $context,
                );
            }
        }
    }

    /**
     * @param list<string> $unpivotedNodes
     * @param list<string> $pivotedNodes
     * @param list<string> $skipLegends
     * @param list<string> $createSubtotals
     */
    final public static function new(
        CubeCellDecorator $cubeCell,
        array $unpivotedNodes = [],
        array $pivotedNodes = [],
        array $skipLegends = ['@values'],
        array $createSubtotals = [],
    ): Block {
        $context = new BlockContext(
            apexCubeCell: $cubeCell,
            unpivotedKeys: $unpivotedNodes,
            pivotedKeys: $pivotedNodes,
            skipLegends: $skipLegends,
            createSubtotals: $createSubtotals,
        );

        return new RootBlock($cubeCell, $context);
    }

    final protected function getContext(): BlockContext
    {
        return $this->context;
    }

    abstract public function getHeaderRows(): DefaultRows;

    abstract public function getDataRows(): DefaultRows;

    final public function generateTable(): DefaultTable
    {
        $context = $this->getElementContext();

        return new DefaultTable(
            [
                new DefaultTableHeader($this->getHeaderRows(), $context),
                new DefaultTableBody($this->getDataRows(), $context),
                new DefaultTableFooter(new DefaultRows([], $context), $context),
            ],
            context: $context,
        );
    }
}
