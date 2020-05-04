<?php

namespace Tests\Platform\Domains\Routing;

use Hub;
use Route;
use SuperV\Platform\Domains\Port\Port;
use SuperV\Platform\Domains\Routing\RouteRegistrar;
use Tests\Platform\TestCase;

/**
 * Class RouteRegistrarTest
 *
 * @package Tests\Platform\Domains\Routing
 * @group   resource
 */
class RouteRegistrarTest extends TestCase
{
    function test__registers_routes_from_array()
    {
        app(RouteRegistrar::class)
            ->register([
                'web/foo'       => 'WebController@foo',
                'web/bar'       => [
                    'uses' => 'WebController@bar',
                    'as'   => 'web.bar',
                ],
                'post@web/foo'  => 'WebController@postFoo',
                'patch@web/bar' => function () { },
            ]);

        $getRoutes = $this->router()->getRoutes()->get('GET');

        $this->assertRouteController('WebController@foo', $getRoutes['web/foo']);
        $this->assertRouteController('WebController@bar', $getRoutes['web/bar']);
        $this->assertEquals('web.bar', $getRoutes['web/bar']->getName());

        $postRoutes = $this->router()->getRoutes()->get('POST');
        $this->assertRouteController('WebController@postFoo', $postRoutes['web/foo']);

        $patchRoutes = $this->router()->getRoutes()->get('PATCH');
        $this->assertInstanceOf(\Closure::class, $patchRoutes['web/bar']->getAction('uses'));
    }

    function test__registers_hostname_without_port_number()
    {
        Hub::register(new class extends Port
        {
            protected $slug = 'local';

            protected $hostname = 'localhost:8000';
        });

        $registrar = $this->app->make(RouteRegistrar::class);
        $registrar->setPort($this->getPort('local'))->register(['data' => 'WebController@foo']);

        $getRoutes = $this->router()->getRoutes()->get('GET');

        $this->assertNotNull($route = $getRoutes['localhostdata'] ?? null, 'Route not found');
        $this->assertEquals('local', $route->getAction('port'));
    }

    function test__registers_routes_for_a_port()
    {
        $this->setUpPorts();

        $registrar = $this->app->make(RouteRegistrar::class);
        $registrar->setPort($this->getPort('web'))->register(['web/foo' => 'WebController@foo']);
        $registrar->setPort($this->getPort('acp'))->register(['acp/foo' => 'AcpController@foo']);
        $registrar->setPort($this->getPort('api'))->register(['api/foo' => 'ApiController@foo']);

        $getRoutes = $this->router()->getRoutes()->get('GET');

        $webRoute = $getRoutes['superv.ioweb/foo'];
        $this->assertEquals('web', $webRoute->getAction('port'));
        $this->assertEquals('superv.io', $webRoute->getDomain());
//        $this->assertEquals('/', $webRoute->getPrefix());
        $this->assertNull($webRoute->getPrefix());

        $acpRoute = $getRoutes['superv.ioacp/acp/foo'];
        $this->assertEquals('acp', $acpRoute->getAction('port'));
        $this->assertEquals('superv.io', $acpRoute->getDomain());
        $this->assertEquals('acp', $acpRoute->getPrefix());

        $apiRoute = $getRoutes['api.superv.ioapi/foo'];
        $this->assertEquals('api', $apiRoute->getAction('port'));
        $this->assertEquals('api.superv.io', $apiRoute->getDomain());
//        $this->assertEquals('/', $apiRoute->getPrefix());
        $this->assertNull($apiRoute->getPrefix());
    }

    function test__registers_global_routes()
    {
        // set up 3 sample ports
        $this->setUpPorts();

        Route::get('key/kol', function () { return 'ok'; });

        $registrar = $this->app->make(RouteRegistrar::class);
        $routeList = $registrar->globally()->registerRoute('bar/foo', 'BarController@foo');
        $this->assertEquals(4, count($routeList));

        $routeList = $registrar->globally()->registerRoute('foo/bar', 'FooController@bar');
        $this->assertEquals(8, count($routeList));

        $routes = $this->router()->getRoutes()->get('GET');

        $this->assertNotNull($routes['key/kol']);
        $this->assertNotNull($routes[sv_config('hostname').'bar/foo']);
        $this->assertNotNull($routes[sv_config('hostname').'foo/bar']);

        $this->assertNotNull($routes['superv.iobar/foo']);
        $this->assertNotNull($routes['superv.iofoo/bar']);

        $this->assertNotNull($routes['superv.ioacp/bar/foo']);
        $this->assertNotNull($routes['superv.ioacp/foo/bar']);

        $this->assertNotNull($routes['api.superv.iobar/foo']);
        $this->assertNotNull($routes['api.superv.iofoo/bar']);

        $this->assertEquals(3, Hub::ports()->count());
    }

    function test__registers_ports_middlewares()
    {
        Hub::register((new Port)->hydrate([
            'slug'        => 'web',
            'hostname'    => 'localhost',
            'middlewares' => ['a', 'b', 'c'],
        ]));

        $registrar = $this->app->make(RouteRegistrar::class)->setPort($this->getPort('web'));
        $routes = $registrar->registerRoute('foo', 'WebController@foo');

        $this->assertEquals(['a', 'b', 'c'], $routes->first()->getAction('middleware'));
    }

    protected function assertRouteController($controller, $route)
    {
        $this->assertEquals($controller, $route->getAction('controller'));
    }

    /**
     * @return \Illuminate\Routing\Router
     */
    protected function router()
    {
        return $this->app['router'];
    }

    protected function getPort($slug)
    {
        return \Hub::get($slug);
    }
}