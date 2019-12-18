<?php

namespace Tests\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Form\FormRepository;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class FormControllerTest
 *
 * @package Tests\Platform\Domains\Resource\Http\Controllers
 * @group   resource
 */
class FormControllerTest extends ResourceTestCase
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $posts;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\FormModel
     */
    protected $formEntry;

    function test__form_page_with_uuid()
    {
        $form = $this->getUserPage($this->formEntry->getUrl());
//        $form = HelperComponent::from($page->getProp('blocks.0'));

        $this->assertEquals('sv-form', $form->getName());
        $this->assertEquals($this->posts->getFields()->count(), $form->countProp('fields'));

        foreach ($form->getProp('fields') as $fieldArray) {
            $field = $this->posts->getField($fieldArray['handle']);
            $this->assertEquals(
//                (new FieldComposer($field))->forForm()->get(),
                $field->getFormComposer()->compose()->get(),
                $fieldArray
            );
        }
    }

    function test__public_form()
    {
        $this->withoutExceptionHandling();
        $formEntry = $this->makePublicForm();
        $this->assertTrue($formEntry->isPublic());
        $this->getPublicPage($formEntry->getUrl());
    }

    function test__user_form()
    {
        $this->withoutExceptionHandling();
        $formEntry = $this->makeForm(['public' => false]);
        $this->assertFalse($formEntry->isPublic());
        $this->getUserPage($formEntry->getUrl());
    }

    function test__user_forms_are_not_accessible_by_public()
    {
        $formEntry = $this->makeForm(['public' => false]);
        $this->assertFalse($formEntry->isPublic());
        $response = $this->getJson($formEntry->getUrl());
        $response->assertStatus(401);

        $response = $this->postJson($formEntry->getUrl());
        $response->assertStatus(401);
    }

    function test__fields()
    {
        $this->withoutExceptionHandling();

        $formEntry = $this->makePublicForm();

        $response = $this->getJson($formEntry->getUrl().'/fields/name');
        $response->assertOk();

        $fieldArray = $response->decodeResponseJson('data');

        $field = FieldFactory::createFromArray($fieldArray);
        $this->assertEquals(
//            (new FieldComposer($field))->forForm()->get(),
            $field->getFormComposer()->compose()->get(),
            $fieldArray
        );
    }

    protected function makeForm(array $overrides = []): FormModel
    {
        $formEntry = FormRepository::resolve()->create('testing', 'foo', array_merge([
            'title'  => 'Public Form',
            'public' => false,
        ], $overrides));

        $formEntry->createField(['identifier' => 'sv.forms.fields:name', 'type' => 'text', 'handle' => 'name']);
        $formEntry->createField(['identifier' => 'sv.forms.fields:email', 'type' => 'text', 'handle' => 'email']);
        $formEntry->createField(['identifier' => 'sv.forms.fields:user',
                                 'type'       => 'belongs_to',
                                 'handle'     => 'user',
                                 'config'     => [
                                     'related_resource' => 'platform.users',
                                 ]]);

        return $formEntry;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->posts = $this->create('test_posts',
            function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->boolean('is_published');
            });

        /** @var FormModel $postsForm */
        $this->formEntry = FormModel::query()->where('resource_id', $this->posts->id())->first();
    }

    /**
     * @return \SuperV\Platform\Domains\Resource\Form\FormModel
     */
    protected function makePublicForm(): \SuperV\Platform\Domains\Resource\Form\FormModel
    {
        return $this->makeForm(['public' => true]);
    }
}
