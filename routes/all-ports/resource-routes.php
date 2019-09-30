<?php

use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceDashboardController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceEntryDashboardController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceIndexController;
use SuperV\Platform\Domains\Resource\Http\Controllers\ResourceViewController;

return [
    /**
     * RESOURCE DASHBOARD
     */
    'GET@'.'sv/res/{resource}/{section?}'         => [
        'as'    => 'resource.dashboard',
        'uses'  => ResourceDashboardController::class,
        'where' => ['section' => '^(|create|all)$'],
    ],

    /**
     * RESOURCE ENTRY DASHBOARD
     */
    'GET@'.'sv/res/{resource}/{entry}/{section?}' => [
        'as'    => 'resource.entry.dashboard',
        'uses'  => ResourceEntryDashboardController::class,
        'where' => ['entry' => '[0-9]*'], // , 'section' => '^(|view|edit)$'
    ],

    'GET@'.'sv/ent/{resource}/{entry}/view' => [
        'as'    => 'sv::entry.view',
        'uses'  => ResourceViewController::at('view'),
        'where' => ['entry' => '[0-9]*'],
    ],

    'ANY@'.'sv/res/{resource}/{entry}/actions/{action}' => [
        'as'    => 'resource.entry.actions',
        'uses'  => ResourceIndexController::at('action'),
        'where' => ['entry' => '[0-9]*'],
    ],

    'DELETE@'.'sv/res/{resource}/{entry}' => [
        'as'   => 'resource.entry.delete',
        'uses' => ResourceController::at('delete'),
    ],

    'POST@'.'sv/res/{resource}/{entry}/restore' => [
        'as'   => 'resource.entry.restore',
        'uses' => ResourceController::at('restore'),
    ],

    /**
     * Tables
     */

    'GET@'.'sv/res/{resource}/table/{data?}' => [
        'as'   => 'resource.table',
        'uses' => ResourceIndexController::at('table'),
    ],

    'GET@'.'sv/res/{resource}/table/actions/{action}' => [
        'uses' => ResourceIndexController::at('tableAction'),
    ],

    'POST@'.'sv/res/{resource}/table/actions/{action}' => [
        'uses' => ResourceIndexController::at('tableActionPost'),
    ],



];
