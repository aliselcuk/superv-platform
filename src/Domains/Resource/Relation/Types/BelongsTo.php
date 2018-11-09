<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Resource\Model\Entry;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class BelongsTo extends Relation
{
    protected function newRelationQuery(Entry $relatedEntryInstance): EloquentRelation
    {
        return new EloquentBelongsTo(
            $relatedEntryInstance->newQuery(),
            $this->getParentEntry(),
            $this->config->getForeignKey(),
            'id',
            $this->getName()
        );
    }
}