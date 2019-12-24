<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne;

use Current;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;

class Composer extends FieldComposer
{
    public function table(?EntryContract $entry = null): void
    {
        if ($entry) {
            $relatedEntry = $entry->{$this->getFieldHandle()}()->first();

            $this->payload->set('value', $relatedEntry->getEntryLabel());
        }
    }

    public function view(EntryContract $entry): void
    {
        $relatedEntry = $this->field->type()->getRelatedEntry($entry);

        $this->payload->set('value', $relatedEntry->getEntryLabel());

        if (Current::user()->can($this->field->getConfigValue('related'))) {
            $this->payload->set('meta.link', $relatedEntry->router()->dashboardSPA());
        }
    }

    public function form(?FormInterface $form = null): void
    {
        if (! $options = $this->field->getConfigValue('meta.options')) {
            if ($form) {
                $options = $form->getFieldRpcUrl($this->getFieldHandle(), 'options');
            }
        }

        $this->payload->set('meta.options', $options);
        $this->payload->set('placeholder', __('Select :Object', [
            'object' => $this->field->type()->getRelated()->getSingularLabel(),
        ]));
    }
}