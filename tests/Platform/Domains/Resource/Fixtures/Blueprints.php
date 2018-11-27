<?php

namespace Tests\Platform\Domains\Resource\Fixtures;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use SuperV\Platform\Domains\Resource\Testing\ResourceTestHelpers;

class Blueprints
{
    use ResourceTestHelpers;

    /** @return Resource */
    public function users()
    {
        $this->groups();
        $this->roles();

        $users = $this->create('t_users', function (Blueprint $table, ResourceBlueprint $resource) {
            $resource->resourceKey('user');

            $table->increments('id');
            $table->string('name');
            $table->email('email')->unique();
            $table->string('bio')->rules(['string'])->nullable();
            $table->unsignedInteger('age')->nullable()->showOnIndex();

            $table->file('avatar')->config(['disk' => 'fakedisk']);

            $table->belongsTo('t_groups', 'group')->showOnIndex();
            $table->belongsToMany('t_roles', 'roles', 'assigned_roles', 'user_id', 'role_id',
                function (Blueprint $pivotTable) {
                    $pivotTable->string('notes');
                });

            $table->morphToMany('t_actions', 'actions', 'owner', 'assigned_actions', 'action',
                function (Blueprint $pivotTable) {
                    $pivotTable->string('provision');
                });

            $table->hasMany('t_posts', 'posts');
        });

        return $users;
    }

    /** @return Resource */
    public function posts()
    {
        return $this->create('t_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');

            $table->belongsTo('t_users', 'user');
        });
    }

    /** @return Resource */
    public function roles()
    {
        $roles = $this->create('t_roles', function (Blueprint $table, ResourceBlueprint $resource) {
            $resource->resourceKey('role');
            $table->increments('id');
            $table->string('title')->unique();
        });

        $roles->create(['id' => 1, 'title' => 'client']);
        $roles->create(['id' => 2, 'title' => 'admin']);
        $roles->create(['id' => 3, 'title' => 'root']);
    }

    /** @return Resource */
    public function actions()
    {
        $actions = $this->create('t_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('action')->unique();
        });

        $actions->create(['action' => 'create']);
        $actions->create(['action' => 'view']);
        $actions->create(['action' => 'edit']);
        $actions->create(['action' => 'update']);
        $actions->create(['action' => 'delete']);
    }

    /** @return Resource */
    public function groups()
    {
        $groups = $this->create('t_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });

        $groups->create(['id' => 1, 'title' => 'Users']);
        $groups->create(['id' => 2, 'title' => 'Clients']);
        $groups->create(['id' => 3, 'title' => 'Admins']);

        return $groups;
    }
}