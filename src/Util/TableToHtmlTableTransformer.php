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

namespace Rekalogika\PivotTable\Util;

use Rekalogika\PivotTable\Contracts\Table\Row;
use Rekalogika\PivotTable\Contracts\Table\Table as SourceTable;
use Rekalogika\PivotTable\HtmlTable\Table;
use Rekalogika\PivotTable\Implementation\DefaultDataCell;
use Rekalogika\PivotTable\Implementation\DefaultHeaderCell;
use Rekalogika\PivotTable\Implementation\DefaultRow;
use Rekalogika\PivotTable\Implementation\DefaultRows;
use Rekalogika\PivotTable\Implementation\DefaultTable;
use Rekalogika\PivotTable\Implementation\DefaultTableBody;
use Rekalogika\PivotTable\Implementation\DefaultTableHeader;

final class TableToHtmlTableTransformer
{
    private DefaultTableHeader $tableHeader;

    private DefaultTableBody $tableBody;

    public static function transform(SourceTable $sourceTable): Table
    {
        return (new self($sourceTable))->getTable();
    }

    private function __construct(SourceTable $sourceTable)
    {
        $dimensionMeasureCount = $this->getMaxDimensionMeasureCount($sourceTable);

        $rows = [];
        $thead = null;

        foreach ($sourceTable->getRows() as $row) {
            $currentCount = \count(iterator_to_array($row->getDimensions())) + \count(iterator_to_array($row->getMeasures()));
            if ($currentCount !== $dimensionMeasureCount) {
                continue;
            }

            $rows[] = $this->tableRowToHtmlRow($row, $sourceTable);
            $thead ??= $this->getTableHeader($row, $sourceTable);
        }

        if ($thead === null) {
            throw new \RuntimeException('Table must have at least one row with dimensions and measures.');
        }

        $this->tableBody = new DefaultTableBody(new DefaultRows($rows, null), null);
        $this->tableHeader = new DefaultTableHeader(new DefaultRows([$thead], null), null);
    }

    private function getTable(): DefaultTable
    {
        return new DefaultTable(
            [
                $this->tableHeader,
                $this->tableBody,
            ],
            null,
        );
    }

    private function getMaxDimensionMeasureCount(SourceTable $sourceTable): int
    {
        $maxCount = 0;

        foreach ($sourceTable->getRows() as $row) {
            $dimensionCount = \count(iterator_to_array($row->getDimensions()));
            $measureCount = \count(iterator_to_array($row->getMeasures()));
            $totalCount = $dimensionCount + $measureCount;

            if ($totalCount > $maxCount) {
                $maxCount = $totalCount;
            }
        }

        return $maxCount;
    }

    private function getTableHeader(Row $row, SourceTable $sourceTable): DefaultRow
    {
        $htmlRow = new DefaultRow([], null);

        // Add headers for dimensions
        /** @psalm-suppress MixedAssignment */
        foreach ($row->getDimensions() as $key => $value) {
            $cell = new DefaultHeaderCell(
                name: $key,
                content: $sourceTable->getLegend($key) ?? $key,
                columnSpan: 1,
                rowSpan: 1,
                context: null,
            );

            $htmlRow = $htmlRow->appendCell($cell);
        }

        // Add headers for measures
        /** @psalm-suppress MixedAssignment */
        foreach ($row->getMeasures() as $key => $value) {
            $cell = new DefaultHeaderCell(
                name: $key,
                content: $sourceTable->getLegend($key) ?? $key,
                columnSpan: 1,
                rowSpan: 1,
                context: null,
            );

            $htmlRow = $htmlRow->appendCell($cell);
        }

        return $htmlRow;
    }

    private function tableRowToHtmlRow(Row $row, SourceTable $sourceTable): DefaultRow
    {
        $htmlRow = new DefaultRow([], null);

        // Add data cells for dimensions
        /** @psalm-suppress MixedAssignment */
        foreach ($row->getDimensions() as $key => $value) {
            $cell = new DefaultDataCell(
                name: $key,
                content: $value,
                columnSpan: 1,
                rowSpan: 1,
                context: null,
            );

            $htmlRow = $htmlRow->appendCell($cell);
        }

        // Add data cells for measures
        /** @psalm-suppress MixedAssignment */
        foreach ($row->getMeasures() as $key => $value) {
            $cell = new DefaultDataCell(
                name: $key,
                content: $value,
                columnSpan: 1,
                rowSpan: 1,
                context: null,
            );

            $htmlRow = $htmlRow->appendCell($cell);
        }

        return $htmlRow;
    }
}
