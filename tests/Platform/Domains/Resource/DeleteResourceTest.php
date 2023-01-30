<?php

namespace Tests\Platform\Domains\Resource;

use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Domains\Addon\Events\AddonUninstallingEvent;
use SuperV\Platform\Domains\Auth\Access\Action;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Jobs\DeleteResource;
use SuperV\Platform\Domains\Resource\Resource;

/**
 * Class DeleteResourceTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class DeleteResourceTest extends ResourceTestCase
{
    function test__deletes_resource_when_the_table_is_dropped()
    {
        $categories = $this->blueprints()->categories();

        Schema::drop($categories->config()->getTable());

        $this->assertFalse(Resource::exists($categories->getIdentifier()));
    }

    function test__deletes_field_when_a_column_is_dropped()
    {
        $resourceEntry = $this->makeResourceModel('test_users', ['name', 'title']);

        $this->assertNotNull($resourceEntry->getField('name'));
        $this->assertNotNull($resourceEntry->getField('title'));

        Schema::table('test_users', function (Blueprint $table) {
            $table->dropColumn(['title', 'name']);
        });
        $resourceEntry->load('fields');
        $this->assertNull($resourceEntry->getField('name'));
        $this->assertNull($resourceEntry->getField('title'));
    }

    function test__deletes_fields_when_a_resource_is_deleted()
    {
        $resourceEntry = $this->makeResourceModel('test_users', ['name', 'title']);

        $this->assertEquals(2, $resourceEntry->fields()->count());

        $resourceEntry->delete();

        $this->assertEquals(0, $resourceEntry->fields()->count());
    }

    function test__deletes_auth_action_entries()
    {
        $this->blueprints()->posts();

        DeleteResource::dispatch('sv.testing.posts');
        $this->assertFalse(Resource::exists('sv.testing.posts'));

        $this->assertEquals(0, Action::query()->where('slug', 'LIKE', 'sv.testing.posts%')->count());
        $this->assertEquals(0, Action::query()->where('namespace', 'LIKE', 'sv.testing.posts%')->count());
    }

    function __deletes_pivot_resources()
    {
        $resource = $this->blueprints()->actions();
        $this->assertTableExists('assigned_actions');
        $this->assertTrue(Resource::exists('sv.testing.assigned_actions'));

        $addon = $this->bindMock(Addon::class);
        $addon->shouldReceive('getIdentifier')->andReturn('testing');
        AddonUninstallingEvent::dispatch($addon);

        $this->assertTableDoesNotExist('assigned_actions');
    }

    protected function tearDown(): void
    {
        @unlink($this->basePath('sv-testing.sqlite'));
        parent::tearDown();
    }
}

