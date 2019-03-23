<?php

namespace SuperV\Platform\Domains\Port;

class ApiPort extends Port
{
    protected $slug = 'api';

    protected $prefix = 'sv-api';

    protected $guard = 'sv-api';

    protected $navigationSlug = 'acp';

    protected $roles = ['user'];

    protected $middlewares = [
        'Barryvdh\Cors\HandleCors',
    ];
}