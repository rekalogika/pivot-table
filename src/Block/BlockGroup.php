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

use Rekalogika\PivotTable\Contracts\TreeNode;

abstract class BlockGroup extends Block
{
    /**
     * @var array<int,list<TreeNode>>
     */
    private array $rawChildNodes = [];

    /**
     * @var array<int,list<TreeNode>>
     */
    private array $childNodes = [];

    /**
     * @var array<int,non-empty-list<TreeNode>>
     */
    private array $balancedChildNodes = [];

    /**
     * @var array<int,list<Block>>
     */
    private array $childBlocks = [];

    /**
     * @var array<int,list<Block>>
     */
    private array $balancedChildBlocks = [];

    /**
     * @param int<0,max> $level
     */
    public function __construct(
        private readonly TreeNode $node,
        private readonly ?TreeNode $parentNode,
        int $level,
        BlockContext $context,
    ) {
        parent::__construct($level, $context);
    }

    /**
     * @param int<1,max> $level
     * @return list<Block>
     */
    public function getChildBlocks(int $level = 1): array
    {
        if (isset($this->childBlocks[$level])) {
            return $this->childBlocks[$level];
        }

        $childBlocks = [];

        foreach ($this->getChildNodes($level) as $childNode) {
            $childBlocks[] = $this->createBlock(
                node: $childNode,
                parentNode: $this->node,
                level: $this->getLevel() + $level,
            );
        }

        return $this->childBlocks[$level] = $childBlocks;
    }

    /**
     * @param int<1,max> $level
     * @return list<Block>
     */
    public function getBalancedChildBlocks(int $level = 1): array
    {
        if (isset($this->balancedChildBlocks[$level])) {
            return $this->balancedChildBlocks[$level];
        }

        $balancedChildBlocks = [];

        foreach ($this->getBalancedChildNodes($level) as $childNode) {
            $balancedChildBlocks[] = $this->createBlock(
                node: $childNode,
                parentNode: $this->node,
                level: $this->getLevel() + $level,
            );
        }

        return $this->balancedChildBlocks[$level] = $balancedChildBlocks;
    }

    /**
     * @param int<1,max> $level
     */
    public function getOneChildBlock(int $level = 1): Block
    {
        return $this->getChildBlocks($level)[0]
            ?? throw new \RuntimeException('No child blocks found in the current node.');
    }

    /**
     * @param int<1,max> $level
     */
    public function getOneBalancedChildBlock(int $level = 1): Block
    {
        return $this->getBalancedChildBlocks($level)[0]
            ?? throw new \RuntimeException('No child blocks found in the current node.');
    }

    final public function getNode(): TreeNode
    {
        return $this->node;
    }

    final public function getParentNode(): ?TreeNode
    {
        return $this->parentNode;
    }

    /**
     * @param int<1,max> $level
     * @return list<TreeNode>
     */
    private function getRawChildNodes(int $level = 1): array
    {
        if (isset($this->rawChildNodes[$level])) {
            return $this->rawChildNodes[$level];
        }

        $children = array_values(iterator_to_array($this->node->getChildren($level)));

        return $this->rawChildNodes[$level] = $children;
    }

    /**
     * @param int<1,max> $level
     */
    private function getSubtotalNode(int $level = 1): ?TreeNode
    {
        return SubtotalTreeNode::create(
            node: $this->node,
            level: $level,
            context: $this->getContext(),
        );
    }

    /**
     * @param int<1,max> $level
     * @return list<TreeNode>
     */
    private function getChildNodes(int $level = 1): array
    {
        if (isset($this->childNodes[$level])) {
            return $this->childNodes[$level];
        }

        $children = $this->getRawChildNodes($level);
        $subtotalNode = $this->getSubtotalNode($level);

        if ($subtotalNode !== null) {
            $children[] = $subtotalNode;
        }

        return $this->childNodes[$level] = $children;
    }

    /**
     * @param int<1,max> $level
     * @return non-empty-list<TreeNode>
     */
    private function getBalancedChildNodes(int $level = 1): array
    {
        if (isset($this->balancedChildNodes[$level])) {
            return $this->balancedChildNodes[$level];
        }

        $children = $this->getChildNodes($level);
        $children = $this->balanceNodes($children, $this->getLevel() + $level - 1);

        $subtotalNode = $this->getSubtotalNode($level);

        if ($subtotalNode !== null) {
            $children[] = $subtotalNode;
        }

        /** @var non-empty-list<TreeNode> $children */
        return $this->balancedChildNodes[$level] = $children;
    }

    /**
     * @param int<1,max> $level
     */
    final public function getOneChild(int $level = 1): TreeNode
    {
        return $this->getChildNodes($level)[0]
            ?? $this->getBalancedChildNodes($level)[0]
            ?? throw new \RuntimeException('No child nodes found in the current node.');
    }
}
