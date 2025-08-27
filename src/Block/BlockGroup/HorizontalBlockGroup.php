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

namespace Rekalogika\PivotTable\Block\BlockGroup;

use Rekalogika\PivotTable\Block\Model\CubeCellDecorator;
use Rekalogika\PivotTable\Implementation\DefaultHeaderCell;
use Rekalogika\PivotTable\Implementation\DefaultRows;

final class HorizontalBlockGroup extends BlockGroup
{
    #[\Override]
    public function getHeaderRows(): DefaultRows
    {
        $context = $this->getElementContext();
        $nextKey = $this->getContext()->getNextKey();

        if ($nextKey === null) {
            throw new \RuntimeException('Next key is not set in the context.');
        }

        $headerRows = new DefaultRows([], $context);

        // add a header and data column for each of the child blocks
        foreach ($this->getChildBlocks() as $childBlock) {
            $childHeaderRows = $childBlock->getHeaderRows();
            $headerRows = $headerRows->appendRight($childHeaderRows);
        }

        // add a legend if the dimension is not marked as skipped
        $child = $this->getOneChildCube();

        if (!$this->getContext()->isLegendSkipped($nextKey)) {
            $nameCell = new DefaultHeaderCell(
                name: $nextKey,
                content: $child->getLegend($nextKey),
                context: $context,
            );

            $headerRows = $nameCell->appendRowsBelow($headerRows);
        }

        return $headerRows;
    }

    #[\Override]
    public function getDataRows(): DefaultRows
    {
        $context = $this->getElementContext();
        $nextKey = $this->getContext()->getNextKey();

        if ($nextKey === null) {
            throw new \RuntimeException('Next key is not set in the context.');
        }

        $dataRows = new DefaultRows([], $context);

        foreach ($this->getChildBlocks() as $childBlock) {
            $childDataRows = $childBlock->getDataRows();
            $dataRows = $dataRows->appendRight($childDataRows);
        }

        return $dataRows;
    }

    /**
     * @return non-empty-list<CubeCellDecorator>
     */
    #[\Override]
    protected function createPrototypeCubes(): array
    {
        $firstPivoted = $this->getContext()->getFirstPivotedKey();
        $currentKeys = array_keys($this->getCube()->getCoordinates());
        $existsInCoordinates = \in_array($firstPivoted, $currentKeys, true);
        $childKey = $this->getChildKey();

        if ($firstPivoted === null || !$existsInCoordinates) {
            $result = $this->getContext()
                ->getApexCubeCell()
                ->drillDownWithoutBalancing($childKey);

            $result = iterator_to_array($result, false);

            if ($result === []) {
                $result = $this->getContext()
                    ->getApexCubeCell()
                    ->drillDown($childKey);

                $result = iterator_to_array($result, false);
            }

            if ($result === []) {
                throw new \RuntimeException(\sprintf(
                    'No prototype nodes found for child key "%s".',
                    $childKey,
                ));
            }
        } else {
            $result = $this->getCube()
                ->rollUpAllExcept([$firstPivoted])
                ->drillDownWithoutBalancing($childKey);

            $result = iterator_to_array($result, false);

            if ($result === []) {
                // fallback to apex cube if no prototype found
                $result = $this->getContext()
                    ->getApexCubeCell()
                    ->drillDownWithoutBalancing($childKey);

                $result = iterator_to_array($result, false);
            }

            if ($result === []) {
                $result = $this->getContext()
                    ->getApexCubeCell()
                    ->drillDown($childKey);

                $result = iterator_to_array($result, false);
            }

            if ($result === []) {
                throw new \RuntimeException(\sprintf(
                    'No prototype nodes found for child key "%s".',
                    $childKey,
                ));
            }
        }

        return $result;
    }
}
