<?php namespace SuperV\Platform\Domains\Droplet\Module\Jobs;

use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;
use SuperV\Platform\Domains\Droplet\Model\Droplets;

class DetectActiveModuleJob
{
    /**
     * @var Droplets
     */
    private $droplets;

    public function __construct(Droplets $droplets)
    {
        $this->droplets = $droplets;
    }

    public function handle(RouteMatched $event)
    {
        /** @var Route $route */
        if (!$route = $event->route) {
            return;
        }

        if (!$slug = array_get($route->getAction(), 'superv::droplet')) {
            return;
        }

        /** @var DropletModel $module */
        $module = $this->droplets->withSlug($slug);

        superv('view')->addNamespace(
            'module',
            [base_path($module->getPath('resources/views'))]
        );
    }
}