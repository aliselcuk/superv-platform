<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use Closure;
use Illuminate\Http\UploadedFile;
use Storage;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FileFieldTest extends ResourceTestCase
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    function test_type_file_is_not_required_by_default()
    {
        $avatar = $this->resource->getField('avatar');
        $this->assertFalse($avatar->isRequired());
        $this->assertTrue($avatar->hasFlag('nullable'));
    }

    function test__type_file()
    {
        $this->assertColumnNotExists('avatar', 'tmp_table');
        $this->assertFalse(in_array('avatar', \Schema::getColumnListing('tmp_table')));

        $entry = $this->resource->fake();
        /** @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface $field */
        $field = $entry->getResource()->getField('avatar');

        $this->assertEquals('file', $field->getType());
        $this->assertEquals(['disk' => 'fakedisk'], $field->getConfig());

        $uploadedFile = UploadedFile::fake()->image('square.png');
        $request = $this->makePostRequest('', ['avatar' => $uploadedFile]);
        [$callback,] = $field->getFieldType()->resolveValueFromRequest($request, $entry);

        $this->assertInstanceOf(Closure::class, $callback);

        /** @var \SuperV\Platform\Domains\Media\Media $media */
        $media = $callback();
        $this->assertNotNull($media);
        $this->assertEquals('testing.tbl', $media->owner_type);
        $this->assertNotNull($field->getComposer()->toView($entry)->get('image_url'));
//        $this->assertNotNull((new FieldComposer($field))->forView($entry)->get('image_url'));

        $this->assertFileExists($media->filePath());
        $this->assertEquals('square.png', $media->getOriginalFilename());
    }

    function test__allowed_file_types()
    {
        $response = $this->uploadFile($this->resource, UploadedFile::fake()->image('square.jpg'));
        $response->assertStatus(422);
    }

    function test__max_size_fail()
    {
        $response = $this->uploadFile($this->resource, UploadedFile::fake()->image('square.png')->size(301));
        $response->assertStatus(422);
    }

    function test__max_size_success()
    {
        $response = $this->uploadFile($this->resource, UploadedFile::fake()->image('square.png')->size(300));
        $response->assertOk();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->resource = $this->create('tmp_table',
            function (Blueprint $table, ResourceConfig $config) {
                $config->setIdentifier('testing.tbl');
                $table->increments('id');
                $table->file('avatar')
                      ->config(['disk' => 'fakedisk'])
                      ->rules(['image', 'max:300', 'mimes:png']);
            });

        Storage::fake('fakedisk');
    }

    protected function uploadFile(Resource $resource, $file)
    {
        $response = $this->postJsonUser($resource->router()->createForm(), ['avatar' => $file]);

        return $response;
    }
}
