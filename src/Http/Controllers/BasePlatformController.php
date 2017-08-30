<?php

namespace SuperV\Platform\Http\Controllers;

use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\MessageBag;
use Illuminate\View\Factory;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Http\Middleware\PlatformReady;

class BasePlatformController extends Controller
{
    use ServesFeaturesTrait;

    /** @var Request */
    protected $request;

    /** @var Route */
    protected $route;

    /** @var Redirector */
    protected $redirect;

    /** @var Factory */
    protected $view;

    /** @var Dispatcher */
    protected $events;

    /** @var MessageBag */
    protected $messages;

    public function __construct()
    {
        $this->middleware(EncryptCookies::class);
        $this->middleware(AddQueuedCookiesToResponse::class);
        $this->middleware(StartSession::class);
        $this->middleware(ShareErrorsFromSession::class);
        $this->middleware(VerifyCsrfToken::class);
        $this->middleware(SubstituteBindings::class);

        $this->middleware('auth');

        $this->request = app('Illuminate\Http\Request');
        $this->redirect = app('Illuminate\Routing\Redirector');
        $this->view = app('Illuminate\Contracts\View\Factory');
        $this->events = app('Illuminate\Contracts\Events\Dispatcher');
        $this->messages = app('Illuminate\Support\MessageBag');

        $this->route = $this->request->route();

        $this->middleware(PlatformReady::class);
    }
}
