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

namespace Rekalogika\PivotTable\Implementation\TreeNode;

use Rekalogika\PivotTable\Contracts\TreeNode;

final readonly class SubtotalTreeNode implements TreeNode
{
    /**
     * @param int<1,max> $level
     */
    public function __construct(
        private TreeNode $node,
        private string $childrenKey,
        private bool $isLeaf,
        private int $level,
    ) {}

    #[\Override]
    public function isLeaf(): bool
    {
        return $this->isLeaf;
    }


    #[\Override]
    public function getKey(): string
    {
        return $this->childrenKey;
    }

    #[\Override]
    public function getLegend(): mixed
    {
        return 'Total';
    }

    #[\Override]
    public function getItem(): mixed
    {
        return 'Total';
    }

    #[\Override]
    public function getValue(): mixed
    {
        return $this->node->getValue();
    }

    #[\Override]
    public function getChildren(int $level = 1): iterable
    {
        return $this->node->getChildren($this->level + $level);
    }
}
