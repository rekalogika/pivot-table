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

namespace Rekalogika\PivotTable\ArrayTable;

use Rekalogika\PivotTable\Contracts\Cube\Cube;
use Rekalogika\PivotTable\TableToCubeAdapter\TableToCubeAdapter;

final class ArrayTableFactory
{
    /**
     * @param iterable<array<string,mixed>> $input
     * @param list<string> $dimensionFields
     * @param list<string> $measureFields
     * @param array<string,mixed> $legends Key is any dimension and measure
     * field, value is the legend value. Key can also be `@values` to indicate
     * the legend of the measure dimension.
     * @param array<string,mixed>|string $subtotalLabels
     */
    public static function createTable(
        iterable $input,
        array $dimensionFields,
        array $measureFields,
        string $groupingField,
        array $legends,
        array|string $subtotalLabels = 'Total',
    ): ArrayTable {
        $self = new self(
            input: $input,
            dimensionFields: $dimensionFields,
            measureFields: $measureFields,
            groupingField: $groupingField,
            legends: $legends,
            subtotalLabels: $subtotalLabels,
        );

        return $self->getTable();
    }

    /**
     * @param iterable<array<string,mixed>> $input
     * @param list<string> $dimensionFields
     * @param list<string> $measureFields
     * @param array<string,mixed> $legends Key is any dimension and measure
     * field, value is the legend value. Key can also be `@values` to indicate
     * the legend of the measure dimension.
     * @param array<string,mixed>|string $subtotalLabels
     */
    public static function createCube(
        iterable $input,
        array $dimensionFields,
        array $measureFields,
        string $groupingField,
        array $legends,
        array|string $subtotalLabels = 'Total',
    ): Cube {
        $table = self::createTable(
            input: $input,
            dimensionFields: $dimensionFields,
            measureFields: $measureFields,
            groupingField: $groupingField,
            legends: $legends,
            subtotalLabels: $subtotalLabels,
        );

        return TableToCubeAdapter::adapt($table);
    }

    /**
     * @param iterable<array<string,mixed>> $input
     * @param list<string> $dimensionFields
     * @param list<string> $measureFields
     * @param array<string,mixed> $legends Key is any dimension and measure
     * field, value is the legend value. Key can also be `@values` to indicate
     * the legend of the measure dimension.
     * @param array<string,mixed>|string $subtotalLabels
     */
    private function __construct(
        private readonly iterable $input,
        private readonly array $dimensionFields,
        private readonly array $measureFields,
        private readonly string $groupingField,
        private readonly array $legends,
        private readonly array|string $subtotalLabels,
    ) {}

    private function getTable(): ArrayTable
    {
        // rows with only one dimension are placed before the others, so we will
        // be able to know the ordering of the dimension without a separate
        // query.

        $rows = $this->createRows($this->input);

        $rowsWithSingleDimension = [];
        $rest = [];

        foreach ($rows as $row) {
            if ($row->hasExactlyOneDimension()) {
                $rowsWithSingleDimension[] = $row;
            } else {
                $rest[] = $row;
            }
        }

        $rows = [...$rowsWithSingleDimension, ...$rest];

        return new ArrayTable(
            rows: $rows,
            legends: $this->legends,
            subtotalLabels: $this->subtotalLabels,
        );
    }

    /**
     * @return int<1,max>
     */
    private function getGroupingBit(string $dimensionName): int
    {
        $pos = array_search($dimensionName, $this->dimensionFields, true);

        if ($pos === false) {
            throw new \InvalidArgumentException("Dimension field not found: $dimensionName");
        }

        $count = \count($this->dimensionFields);

        if ($pos >= $count) {
            throw new \InvalidArgumentException("Position out of range for dimension field: $dimensionName");
        }

        /** @var int<1,max> */
        return $count - $pos;
    }

    private function isGrouping(string $dimensionName, int $grouping): bool
    {
        $bit = $this->getGroupingBit($dimensionName);

        return ($grouping & (1 << ($bit - 1))) !== 0;
    }

    // private static function hasSingleNonGrouping(int $grouping): bool
    // {
    //     $bits = PHP_INT_SIZE * 8;
    //     $mask = (1 << $bits) - 1;
    //     $y = (~$grouping) & $mask;
    //     return $y !== 0 && ($y & ($y - 1)) === 0;
    // }

    /**
     * @param iterable<array<string,mixed>> $input
     * @return iterable<ArrayRow>
     */
    private function createRows(iterable $input): iterable
    {
        /** @psalm-suppress MixedAssignment */
        foreach ($input as $row) {
            /** @psalm-suppress MixedArgument */
            yield $this->createRow($row);
        }
    }

    /**
     * @param array<string,mixed> $input
     */
    private function createRow(array $input): ArrayRow
    {
        $grouping = $input[$this->groupingField]
            ?? throw new \InvalidArgumentException("Missing grouping field: {$this->groupingField}");

        if (!\is_int($grouping)) {
            throw new \InvalidArgumentException("Grouping field must be an integer: {$this->groupingField}");
        }

        $dimensions = [];

        foreach ($this->dimensionFields as $field) {
            if ($this->isGrouping($field, $grouping)) {
                continue; // Skip grouping fields
            }

            if (!\array_key_exists($field, $input)) {
                throw new \InvalidArgumentException("Missing dimension field: $field");
            }

            /** @psalm-suppress MixedAssignment */
            $dimensions[$field] = $input[$field];
        }

        $measures = [];

        foreach ($this->measureFields as $field) {
            /** @psalm-suppress MixedAssignment */
            $measures[$field] = $input[$field]
                ?? throw new \InvalidArgumentException("Missing measure field: $field");
        }

        return new ArrayRow(
            dimensions: $dimensions,
            measures: $measures,
        );
    }
}
