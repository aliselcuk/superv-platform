<?php

namespace Tests\Platform\Domains\Resource\Filter;

use SuperV\Platform\Domains\Resource\Filter\ApplyFilters;
use SuperV\Platform\Testing\PlatformTestCase;

class ApplyFiltersTest extends PlatformTestCase
{
    function test__success()
    {
        $request = $this->makeGetRequest([
            'filters' => base64_encode(json_encode(['filter-id' => 'filter-value'])),
        ]);
        ApplyFilters::dispatch(collect([$filterMock = new FilterMock]), 'the-query', $request);

        $this->assertEquals('the-query', $filterMock->appliedQuery);
        $this->assertEquals('filter-value', $filterMock->appliedValue);
    }

    function test__validates()
    {
        $request = $this->makeGetRequest([
            'filters' => base64_encode(json_encode(['filter-id' => 'filter-value'])),
        ]);
        $result = ApplyFilters::dispatch(collect(), 'the-query', $request);
        $this->assertFalse($result);
    }

    function test__validates_a()
    {
        $request = $this->makeGetRequest();
        $result = ApplyFilters::dispatch(collect($filterMock = new FilterMock), 'the-query', $request);
        $this->assertFalse($result);
    }
}

class FilterMock extends \SuperV\Platform\Domains\Resource\Filter\Filter
{
    protected $identifier = 'filter-id';

    public $appliedQuery;

    public $appliedValue;

    public function applyQuery($query, $value)
    {
        $this->appliedQuery = $query;
        $this->appliedValue = $value;

        return $this;
    }
}