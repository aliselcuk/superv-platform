<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Action\ModalAction;
use SuperV\Platform\Domains\Resource\Action\ViewEntryAction;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormConfig;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\Table\TableV2;

class HasMany extends Relation implements ProvidesTable, ProvidesForm
{
    protected function newRelationQuery(EntryContract $relatedEntryInstance): EloquentRelation
    {
        if (! $localKey = $this->config->getLocalKey()) {
            if ($this->parentEntry) {
                $entry = $this->parentEntry;
                $localKey = $entry->getKeyName();
            }
        }

        return new EloquentHasMany(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->config->getForeignKey(),
            $localKey ?? 'id'
        );
    }

    public function makeTable()
    {
        return app(TableV2::class)
            ->setResource($this->getRelatedResource())
            ->setQuery($this)
            ->addAction(ViewEntryAction::class)
            ->setDataUrl(url()->current().'/data')
            ->addContextAction(ModalAction::make()->setModalUrl($this->route('create', $this->parentEntry)));
    }

    public function makeForm(): Form
    {
        return FormConfig::make($this->newQuery()->make())
                         ->hideField(ResourceFactory::make($this->parentEntry)->getResourceKey().'_id')
                         ->makeForm();
    }

    public function getFormTitle(): string
    {
    }
}