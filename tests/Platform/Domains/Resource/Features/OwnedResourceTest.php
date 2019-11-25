<?php

namespace Tests\Platform\Domains\Resource\Features;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class OwnedResourceTest
 *
 * @package Tests\Platform\Domains\Resource\Features
 * @group   resource
 */
class OwnedResourceTest extends ResourceTestCase
{
    function test__init()
    {
        $entries = $this->create('t_entries',
            function (Blueprint $table, ResourceConfig $config) {
                $config->ownerKey('users');

                $table->increments('id');
                $table->belongsTo('users', 'user');
            });

        $this->assertTrue($entries->isOwned());
    }
}
