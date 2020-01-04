<?php

namespace Tests\Platform\Domains\Resource\Hook;

use stdClass;
use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Domains\Addon\Events\AddonBootedEvent;
use SuperV\Platform\Domains\Resource\Hook\Actions\RegisterAddonHooks;
use SuperV\Platform\Domains\Resource\Hook\HookManager;
use Tests\Platform\Domains\Resource\Fixtures\Resources\CategoriesDashboardPage;
use Tests\Platform\Domains\Resource\Fixtures\Resources\CategoryList;
use Tests\Platform\Domains\Resource\Fixtures\Resources\CategoryObserver;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersFormCustom;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersFormDefault;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersObserver;
use Tests\Platform\Domains\Resource\Fixtures\Resources\Posts\_PostsManagerForm;
use Tests\Platform\Domains\Resource\Fixtures\Resources\Posts\PostObserver;
use Tests\Platform\Domains\Resource\Fixtures\Resources\Posts\Posts;
use Tests\Platform\Domains\Resource\Fixtures\Resources\Posts\PostsFields;
use Tests\Platform\Domains\Resource\Fixtures\Resources\Posts\PostsForm;
use Tests\Platform\Domains\Resource\Fixtures\Resources\Posts\PostUserScope;

/**
 * Class HookManagerTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class HookManagerTest extends HookTestCase
{
    function __registers_hooks_globally()
    {
        $hook = HookManager::resolve();
        $hook->register('sv.users', stdClass::class, 'UsersConfig');
        $hook->register('sv.users.fields:title', stdClass::class, 'TitleField');

        $hook = HookManager::resolve();
        $this->assertEquals(
            [
                'config' => stdClass::class,
                'fields' => [
                    'title' => stdClass::class,
                ],
            ],
            $hook->get('sv.users')
        );

        $this->assertEquals(
            [
                'title' => stdClass::class,
            ],
            $hook->get('sv.users', 'fields')
        );
        $this->assertEquals(stdClass::class, $hook->get('sv.users', 'config'));
    }

    function __scan_path_for_hooks()
    {
        $hook = HookManager::resolve();

        $this->assertEquals(
            [
                'forms'    => [
                    'default' => OrdersFormDefault::class,
                    'custom'  => OrdersFormCustom::class,
                ],
                'observer' => OrdersObserver::class,
            ],
            $hook->get('sv.testing.orders')
        );

        $this->assertEquals([
            'resource' => Posts::class,
            'observer' => PostObserver::class,
            'fields'   => PostsFields::class,
            'scopes'   => [
                'user' => PostUserScope::class,
            ],
            'forms'    => [
                'default' => PostsForm::class,
                'manager' => _PostsManagerForm::class,
            ],
        ], $hook->get('sv.testing.posts'));

        $this->assertEquals([
            'lists'    => [
                'default' => CategoryList::class,
            ],
            'observer' => CategoryObserver::class,
            'pages'    => [
                'dashboard' => CategoriesDashboardPage::class,
            ],
        ], $hook->get('sv.testing.categories'));
    }

    function test__scans_resources_directory_when_an_addon_is_booted()
    {
        $addon = $this->setUpAddon(null, null);

        $manager = $this->bindPartialMock(HookManager::class, HookManager::resolve());
        $manager->shouldReceive('scan')->with($addon->realPath('src/Resources'))->once();

        AddonBootedEvent::dispatch($addon);
    }

    function test__scans_dddes_directory_when_an_addon_is_booted()
    {
        $addon = $this->bindMock(Addon::class);
        $addon->shouldReceive('realPath')->andReturn('path-does-not-exist');

        $manager = $this->bindPartialMock(HookManager::class, HookManager::resolve());
        $manager->shouldNotReceive('scan');

        (new RegisterAddonHooks)->handle(new AddonBootedEvent($addon));
    }
}
