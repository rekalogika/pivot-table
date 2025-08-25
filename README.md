# rekalogika/pivot-table

A PHP library for transforming multidimensional data into a pivot table.

Full documentation is available at
[rekalogika.dev/pivot-table](https://rekalogika.dev/pivot-table).

## Features

* **Handles complex data**. Renders complex multi-dimensional data involving
  multiple dimensions and measures into equally complex pivot tables.
* **Flexible pivoting**: Configure which dimensions should be pivoted (in
  columns) vs unpivoted (in rows).
* **Measures are dimension**: Measures are regarded as a special dimension
  `@values` that can be flexibly positioned in the pivot structure.
* **Subtotals**: Renders subtotals if they exist in the source data.
* **Dimension ordering**: Honors the ordering of dimensions as specified in the
  `ORDER BY` clause of the SQL query.
* **Accepts simple array data**: Compatible with all result set formats. Does
  not require specific database and database access libraries.
* **Custom integration options**: Options for deeper integration with your
  application by providing your custom data structure in table-like data, or in
  data cube if your data is already in that format.
* **Customizable legends**: Define custom labels for dimensions, measures, and
  subtotals.
* **Does not require a specific data type**: Your dimension members and measure
  values can be of any type, including enums, entity objects, money-pattern
  objects, etc. You can supply a custom renderer to format the output as
  required.

## Design Philosophy

The library only transforms the data into a pivot table, but does not perform
any calculations like sum, average, etc, including for calculating subtotals.
This decision is taken because some aggregation functions, like Hyperloglog, are
database specific, and cannot be easily performed using PHP. Instead, it expects
the data to be already cubed.

If your data needs to be manually cubed, then it should be possible to use a
cubing layer, but it is not yet written.

## Requirements

PHP 8.2 or higher.

Technically, the library does not require a specific database software, it does
not even require a database at all. However, it works best if the data is
properly cubed. If you are getting the data from a database, the most convenient
way to accomplish that is to use a database server that supports the `GROUP BY
CUBE()` clause, like PostgreSQL and SQL Server.
## Installation

Install via Composer:

```bash
composer require rekalogika/pivot-table
```

## Basic Usage

First, we need to procure the data. For maximum functionality, the result should
be properly cubed. The following is an example SQL query for PostgreSQL.

```sql
-- name, country, and month are the dimensions, count and sum are the measures.
-- grouping is used to identify if a dimension is subtotaled.
SELECT name,
    country,
    month,
    COUNT(*) AS count,
    SUM(price) AS sum,
    GROUPING(name, country, month) AS grouping
FROM items
GROUP BY DISTINCT CUBE(name, country, month)
-- ORDER BY clause determines the order of the dimensions in the output
ORDER BY name,
    country,
    month
```

Then, use whatever means to execute the query and fetch the result as an array
of array. We can create a pivot table as follows:


```php
use Rekalogika\PivotTable\ArrayTable\ArrayTableFactory;
use Rekalogika\PivotTable\PivotTableTransformer;
use Rekalogika\PivotTable\TableRenderer\BasicTableRenderer;

// Create a data cube from the result set
$cube = ArrayTableFactory::createCube(
    input: $data,
    // must be in the same order as the GROUPING() function in the SQL query:
    dimensionFields: ['name', 'country', 'month'], 
    measureFields: ['count', 'sum'],
    groupingField: 'grouping',
    legends: [
        '@values' => 'Values',
        'name' => 'Item Name',
        'country' => 'Country', 
        'month' => 'Month',
        'count' => 'Count',
        'sum' => 'Sum',
    ],
    subtotalLabels: [
        'name' => 'All Names',
        'country' => 'All Countries',
        'month' => 'All Months',
    ],
);

// Transform the cube to HTML table object
$htmlTable = PivotTableTransformer::transform(
    cube: $cube,
    unpivoted: ['country', 'month'],    // Rows
    pivoted: ['@values', 'name'],       // Columns  
    measures: ['count', 'sum'],         // Which measures to include
    withSubtotal: ['name', 'country'],  // Add subtotals for these dimensions
);

// Render to HTML string
$html = BasicTableRenderer::render($htmlTable);
echo $html;
```


The resulting HTML table will look like this:

<table>
  <thead>
    <tr>
      <th rowspan="3">Country</th>
      <th rowspan="3">Month</th>
      <th colspan="4">Count</th>
      <th colspan="4">Sum</th>
    </tr>
    <tr>
      <th colspan="4">Name</th>
      <th colspan="4">Name</th>
    </tr>
    <tr>
      <td>itemA</td>
      <td>itemB</td>
      <td>itemC</td>
      <td>All names</td>
      <td>itemA</td>
      <td>itemB</td>
      <td>itemC</td>
      <td>All names</td>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td rowspan="6">CN</td>
      <td>202501</td>
      <td>4</td>
      <td>1</td>
      <td>5</td>
      <td>10</td>
      <td>2586.07</td>
      <td>434.22</td>
      <td>3375.71</td>
      <td>6396</td>
    </tr>
    <tr>
      <td>202502</td>
      <td>null</td>
      <td>5</td>
      <td>1</td>
      <td>6</td>
      <td>null</td>
      <td>2575.55</td>
      <td>337.29</td>
      <td>2912.84</td>
    </tr>
    <tr>
      <td>202503</td>
      <td>4</td>
      <td>5</td>
      <td>3</td>
      <td>12</td>
      <td>2823.54</td>
      <td>2849.01</td>
      <td>1488.2</td>
      <td>7160.75</td>
    </tr>
    <tr>
      <td>202504</td>
      <td>3</td>
      <td>null</td>
      <td>null</td>
      <td>3</td>
      <td>1301.73</td>
      <td>null</td>
      <td>null</td>
      <td>1301.73</td>
    </tr>
    <tr>
      <td>202505</td>
      <td>null</td>
      <td>3</td>
      <td>null</td>
      <td>3</td>
      <td>null</td>
      <td>1845.78</td>
      <td>null</td>
      <td>1845.78</td>
    </tr>
    <tr>
      <td>All months</td>
      <td>11</td>
      <td>14</td>
      <td>9</td>
      <td>34</td>
      <td>6711.34</td>
      <td>7704.56</td>
      <td>5201.2</td>
      <td>19617.1</td>
    </tr>
    <tr>
      <td rowspan="6">JP</td>
      <td>202501</td>
      <td>1</td>
      <td>3</td>
      <td>4</td>
      <td>8</td>
      <td>107.51</td>
      <td>1410.79</td>
      <td>1765.04</td>
      <td>3283.34</td>
    </tr>
    <tr>
      <td>202502</td>
      <td>5</td>
      <td>1</td>
      <td>5</td>
      <td>11</td>
      <td>3119.07</td>
      <td>393.39</td>
      <td>2081.02</td>
      <td>5593.48</td>
    </tr>
    <tr>
      <td>202503</td>
      <td>null</td>
      <td>3</td>
      <td>5</td>
      <td>8</td>
      <td>null</td>
      <td>1966</td>
      <td>3656.16</td>
      <td>5622.16</td>
    </tr>
    <tr>
      <td>202504</td>
      <td>3</td>
      <td>5</td>
      <td>2</td>
      <td>10</td>
      <td>2014.32</td>
      <td>2348.93</td>
      <td>1042.42</td>
      <td>5405.67</td>
    </tr>
    <tr>
      <td>202505</td>
      <td>null</td>
      <td>1</td>
      <td>1</td>
      <td>2</td>
      <td>null</td>
      <td>973.91</td>
      <td>725.8</td>
      <td>1699.71</td>
    </tr>
    <tr>
      <td>All months</td>
      <td>9</td>
      <td>13</td>
      <td>17</td>
      <td>39</td>
      <td>5240.9</td>
      <td>7093.02</td>
      <td>9270.44</td>
      <td>21604.36</td>
    </tr>
    <tr>
      <td rowspan="6">KR</td>
      <td>202501</td>
      <td>null</td>
      <td>null</td>
      <td>2</td>
      <td>2</td>
      <td>null</td>
      <td>null</td>
      <td>1134.36</td>
      <td>1134.36</td>
    </tr>
    <tr>
      <td>202502</td>
      <td>5</td>
      <td>3</td>
      <td>1</td>
      <td>9</td>
      <td>1668.59</td>
      <td>2239.52</td>
      <td>240.94</td>
      <td>4149.05</td>
    </tr>
    <tr>
      <td>202503</td>
      <td>3</td>
      <td>5</td>
      <td>4</td>
      <td>12</td>
      <td>1891.44</td>
      <td>1781.15</td>
      <td>2191.78</td>
      <td>5864.37</td>
    </tr>
    <tr>
      <td>202504</td>
      <td>1</td>
      <td>5</td>
      <td>2</td>
      <td>8</td>
      <td>855.94</td>
      <td>2641.75</td>
      <td>1274.36</td>
      <td>4772.05</td>
    </tr>
    <tr>
      <td>202505</td>
      <td>5</td>
      <td>2</td>
      <td>null</td>
      <td>7</td>
      <td>2231.2</td>
      <td>458.68</td>
      <td>null</td>
      <td>2689.88</td>
    </tr>
    <tr>
      <td>All months</td>
      <td>14</td>
      <td>15</td>
      <td>9</td>
      <td>38</td>
      <td>6647.17</td>
      <td>7121.1</td>
      <td>4841.44</td>
      <td>18609.71</td>
    </tr>
    <tr>
      <td rowspan="6">All countries</td>
      <td>202501</td>
      <td>5</td>
      <td>4</td>
      <td>11</td>
      <td>20</td>
      <td>2693.58</td>
      <td>1845.01</td>
      <td>6275.11</td>
      <td>10813.7</td>
    </tr>
    <tr>
      <td>202502</td>
      <td>10</td>
      <td>9</td>
      <td>7</td>
      <td>26</td>
      <td>4787.66</td>
      <td>5208.46</td>
      <td>2659.25</td>
      <td>12655.37</td>
    </tr>
    <tr>
      <td>202503</td>
      <td>7</td>
      <td>13</td>
      <td>12</td>
      <td>32</td>
      <td>4714.98</td>
      <td>6596.16</td>
      <td>7336.14</td>
      <td>18647.28</td>
    </tr>
    <tr>
      <td>202504</td>
      <td>7</td>
      <td>10</td>
      <td>4</td>
      <td>21</td>
      <td>4171.99</td>
      <td>4990.68</td>
      <td>2316.78</td>
      <td>11479.45</td>
    </tr>
    <tr>
      <td>202505</td>
      <td>5</td>
      <td>6</td>
      <td>1</td>
      <td>12</td>
      <td>2231.2</td>
      <td>3278.37</td>
      <td>725.8</td>
      <td>6235.37</td>
    </tr>
    <tr>
      <td>All months</td>
      <td>34</td>
      <td>42</td>
      <td>35</td>
      <td>111</td>
      <td>18599.41</td>
      <td>21918.68</td>
      <td>19313.08</td>
      <td>59831.17</td>
    </tr>
  </tbody>
</table>

## Related Packages

If you are using Doctrine ORM, you might be interested in
[`rekalogika/doctrine-advanced-group-by`](https://github.com/rekalogika/doctrine-advanced-group-by).
The package allows you to use `CUBE()`, `GROUPING()` and other grouping sets
functions in DQL queries and `QueryBuilder`.

The package [`rekalogika/analytics`](https://rekalogika.dev/analytics) is a
full-fledged analytical solution for Doctrine ORM. This package originated as a
component of that package, but was later generalized to be useful in other
use-cases.

## Documentation

For detailed documentation, examples, and advanced usage patterns, visit
[rekalogika.dev/pivot-table](https://rekalogika.dev/pivot-table).

## License

MIT

## Contributing

Issues and pull requests should be filed in the GitHub repository
[`rekalogika/pivot-table`](https://github.com/rekalogika/pivot-table).