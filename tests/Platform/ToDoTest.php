<?php

namespace Tests\Platform;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class ToDoTest
 * Temporary location for todos
 *
 * @package Tests\Platform
 * @ignore
 */
class ToDoTest extends TestCase
{
    use RefreshDatabase;

    function test__()
    {
        $this->addToAssertionCount(1);
    }

    function test__platform_detects_active_module_from_route_data()
    {
        $this->addToAssertionCount(1);
    }

    function test__platform_add_view_hint_module_for_active_module()
    {
        $this->addToAssertionCount(1);
    }

    function test__installer_uninstalls_subaddons_when_a_addon_is_uninstalled()
    {
        $this->addToAssertionCount(1);
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}
