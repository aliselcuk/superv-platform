<?php

namespace SuperV\Platform\Domains\UI\Page;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\UI\Components\ComponentContract;

class EntryPage extends ResourcePage
{
    protected $editable = true;

    protected $viewable = true;

    public function build($tokens = [])
    {
        $this->buildSections();

//        $this->actions[] = RestoreEntryAction::make();

        $this->buildActions();

        return parent::build($tokens);
    }

    public function makeComponent(): ComponentContract
    {
        if ($imageField = $this->resource->fields()->getHeaderImage()) {
            $imageUrl = (new FieldComposer($imageField))
                ->forView($this->entry)
                ->get('image_url');
        }

        return parent::makeComponent()
                     ->setName('sv-page')
                     ->mergeProps([
                         'sections' => $this->buildSections(),
                         'links'    => array_filter_null(
                             [
                                 'image'  => $imageUrl ?? '',
                                 'create' => $this->creatable ? $this->resource->route('create') : null,
                                 'edit'   => $this->editable ? $this->resource->route('edit', $this->entry) : null,
                                 'view'   => $this->viewable ? $this->resource->route('view', $this->entry) : null,
                                 'index'  => $this->resource->route('index'),
                             ]
                         ),
                     ]);
    }

    public function notEditable(): EntryPage
    {
        $this->editable = false;

        return $this;
    }

    public function notViewable(): EntryPage
    {
        $this->viewable = false;

        return $this;
    }

    protected function buildSections()
    {
        return collect($this->getRelationsSections())
            ->map(function ($section) {
                return sv_parse($section, ['entry' => $this->entry]);
            });
    }

    protected function getRelationsSections(): Collection
    {
        return $this->resource->getRelations()
                              ->filter(function (Relation $relation) {
                                  return ! $relation->hasFlag('view.hide');
                              })
                              ->map(function (Relation $relation) {
                                  if ($url = $relation->getConfigValue('view.url')) {
                                      $portal = true;
                                  } elseif ($relation instanceof ProvidesTable) {
                                      $url = $relation->indexRoute($this->entry);
                                  } elseif ($relation instanceof ProvidesForm) {
                                      $url = $relation->route('edit', $this->entry);
                                  } else {
                                      return null;
                                  }

                                  return [
                                      'url'    => $url,
                                      'portal' => $portal ?? false,
                                      'title'  => str_unslug($relation->getName()),
                                  ];
                              })
                              ->filter()->values();
    }
}