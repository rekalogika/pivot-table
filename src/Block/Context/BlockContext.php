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

namespace Rekalogika\PivotTable\Block\Context;

use Rekalogika\PivotTable\Block\Model\CubeCellDecorator;

final readonly class BlockContext
{
    private Keys $keys;

    /**
     * @param list<string> $pivotedKeys
     * @param list<string> $unpivotedKeys
     * @param list<string> $measures
     * @param list<string> $currentKeyPath
     * @param list<string> $skipLegends
     * @param list<string> $createSubtotals
     * @param int<0,max> $subtotalDepth 0 is not in subtotal, 1 is in subtotal of first level, and so on.
     * @param int<0,max> $blockDepth 0 is the root block, 1 is the child of the root block, and so on.
     */
    public function __construct(
        private CubeCellDecorator $apexCubeCell,
        array $unpivotedKeys,
        array $pivotedKeys,
        private array $measures,
        private array $skipLegends,
        private array $createSubtotals,
        private int $subtotalDepth = 0,
        private int $blockDepth = 0,
        array $currentKeyPath = [],
    ) {
        $this->keys = new Keys(
            pivotedKeys: $pivotedKeys,
            unpivotedKeys: $unpivotedKeys,
            currentKeyPath: $currentKeyPath,
        );
    }

    //
    // withers
    //

    public function incrementSubtotal(): self
    {
        return new self(
            apexCubeCell: $this->apexCubeCell,
            pivotedKeys: $this->keys->getPivotedKeys(),
            unpivotedKeys: $this->keys->getUnpivotedKeys(),
            measures: $this->measures,
            currentKeyPath: $this->keys->getCurrentKeyPath(),
            skipLegends: $this->skipLegends,
            createSubtotals: $this->createSubtotals,
            subtotalDepth: $this->subtotalDepth + 1,
            blockDepth: $this->blockDepth,
        );
    }

    /**
     * @param int<1,max> $amount
     */
    public function incrementBlockDepth(int $amount): self
    {
        return new self(
            apexCubeCell: $this->apexCubeCell,
            pivotedKeys: $this->keys->getPivotedKeys(),
            unpivotedKeys: $this->keys->getUnpivotedKeys(),
            measures: $this->measures,
            currentKeyPath: $this->keys->getCurrentKeyPath(),
            skipLegends: $this->skipLegends,
            createSubtotals: $this->createSubtotals,
            subtotalDepth: $this->subtotalDepth,
            blockDepth: $this->blockDepth + $amount,
        );
    }

    public function pushKey(): self
    {
        $newPath = $this->keys->getCurrentKeyPath();
        $newPath[] = $this->keys->getNextKey() ?? throw new \LogicException(
            'Cannot push key when there is no next key.',
        );

        return new self(
            apexCubeCell: $this->apexCubeCell,
            pivotedKeys: $this->keys->getPivotedKeys(),
            unpivotedKeys: $this->keys->getUnpivotedKeys(),
            measures: $this->measures,
            currentKeyPath: $newPath,
            skipLegends: $this->skipLegends,
            createSubtotals: $this->createSubtotals,
            subtotalDepth: $this->subtotalDepth,
            blockDepth: $this->blockDepth,
        );
    }

    //
    // keys
    //

    /**
     * @return list<string>
     */
    public function getUnpivotedKeys(): array
    {
        return $this->keys->getUnpivotedKeys();
    }

    /**
     * @return list<string>
     */
    public function getPivotedKeys(): array
    {
        return $this->keys->getPivotedKeys();
    }

    /**
     * @return list<string>
     */
    public function getKeys(): array
    {
        return $this->keys->getKeys();
    }

    public function isKeyPivoted(): bool
    {
        return $this->keys->isKeyPivoted($this->getCurrentKey());
    }

    public function isNextKeyPivoted(): bool
    {
        $next = $this->getNextKey();

        if ($next === null) {
            return false; // No next key, so cannot be pivoted.
        }

        return $this->keys->isKeyPivoted($next);
    }

    public function isKeyUnpivoted(): bool
    {
        return $this->keys->isKeyUnpivoted($this->getCurrentKey());
    }

    public function getFirstPivotedKey(): ?string
    {
        return $this->keys->getFirstPivotedKey();
    }

    /**
     * @return list<string>
     */
    public function getCurrentKeyPath(): array
    {
        return $this->keys->getCurrentKeyPath();
    }

    public function getCurrentKey(): string
    {
        return $this->keys->getCurrentKey() ?? throw new \LogicException(
            'Cannot get current key when there is no current key.',
        );
    }

    /**
     * @param int<1,max> $level 1 means gets the next key, 2 means get the next
     * after the next key, and so on.
     * @return string|null
     */
    public function getNextKey(int $level = 1): ?string
    {
        return $this->keys->getNextKey($level);
    }

    public function isLeaf(): bool
    {
        return $this->keys->isLeaf($this->getCurrentKey());
    }

    //
    // misc
    //

    public function isLegendSkipped(string $key): bool
    {
        return \in_array($key, $this->skipLegends, true);
    }

    public function doCreateSubtotalOnChildren(): bool
    {
        return \in_array($this->getNextKey(), $this->createSubtotals, true);
    }

    /**
     * @return int<0,max>
     */
    public function getSubtotalDepth(): int
    {
        return $this->subtotalDepth;
    }

    /**
     * @return int<0,max>
     */
    public function getBlockDepth(): int
    {
        return $this->blockDepth;
    }

    public function getApexCubeCell(): CubeCellDecorator
    {
        return $this->apexCubeCell;
    }
}
