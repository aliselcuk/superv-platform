<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Testing\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class RelationLookupTest
 *
 * @package Tests\Platform\Domains\Resource\Http\Controllers
 * @group   resource
 */
class RelationLookupTest extends ResourceTestCase
{
    /**
     * @group http
     */
    function test__lookup_data()
    {
        $this->withoutExceptionHandling();

        $users = $this->blueprints()->users();
        $this->blueprints()->actions();

        $userA = $users->fake();

        $userA->actions()->attach([1 => ['provision' => 'pass']]);
        $userA->actions()->attach([2 => ['provision' => 'fail']]);
        $userA->actions()->attach([3 => ['provision' => 'fail']]);

        $relation = $users->getRelation('actions', $userA);

        $url = $relation->route('lookup', $userA);
        $response = $this->getJsonUser($url)->assertOk();

        $table = HelperComponent::fromArray($response->json('data'));

        //  Since this is a lookup a table, normal fields should
        //  be displayed
        //
        $fields = $table->getProp('config.fields');
        $this->assertEquals(1, count($fields));

        $response = $this->getJsonUser($table->getProp('config.data_url'))->assertOk();
        $rows = $response->json('data.rows');

        // We have 5 actions seeded, and attached 3 above
        // So the remaining two should be listed
        //
        $this->assertEquals(2, count($rows));
    }
}
