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

use Rekalogika\PivotTable\Contracts\BranchNode;
use Rekalogika\PivotTable\Contracts\TreeNode;

abstract class BlockGroup extends Block
{
    final protected function __construct(
        private readonly BranchNode $parentNode,
        int $level,
        BlockContext $context,
    ) {
        parent::__construct($level, $context);
    }

    final protected function getParentNode(): BranchNode
    {
        return $this->parentNode;
    }

    /**
     * @return list<TreeNode>
     */
    final protected function getChildren(): array
    {
        /** @var \Traversable<array-key,TreeNode> */
        $children = $this->parentNode->getChildren();

        return array_values(iterator_to_array($children));
    }

    /**
     * @return non-empty-list<TreeNode>
     */
    final protected function getBalancedChildren(): array
    {
        $children = $this->getChildren();

        /** @var non-empty-list<BranchNode> $children */
        return $this->balanceBranchNodes($children, $this->getLevel());
    }
}
