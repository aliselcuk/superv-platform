<?php

namespace Tests\Platform\Domains\Resource;

use Illuminate\Http\UploadedFile;
use Storage;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Exceptions\ValidationException;

class ValidationTest extends ResourceTestCase
{
    /** @test */
    function runs_validation_when_creating_resource_entry()
    {
        $resource = $this->create('tx_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->unsignedInteger('age')->min(10)->max(50);
        });

        $this->expectException(ValidationException::class);

        $resource->create(['name' => 'Nicola']);
    }

    /** @test */
    function validates_file_fields()
    {
        $res = $this->create('tx_users', function (Blueprint $table) {
            $table->increments('id');
            $table->file('avatar')->config(['disk' => 'fakedisk']);
        });

        //upload
        Storage::fake('fakedisk');

        $field = $res->freshWithFake()->build()->getField('avatar');

        $field->setValue(new UploadedFile($this->basePath('__fixtures__/square.png'), 'square.png'));
    }
}