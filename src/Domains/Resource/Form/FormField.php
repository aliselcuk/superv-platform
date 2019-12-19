<?php

namespace SuperV\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\InlinesForm;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormFieldInterface;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Exceptions\PlatformException;

class FormField extends Field implements FormFieldInterface
{
    protected $temporal = false;

    /** @var \SuperV\Platform\Domains\Resource\Form\Form */
    protected $form;

    /** @var \SuperV\Platform\Domains\Resource\Form\FieldLocation */
    protected $location;

    public function observe(FormFieldInterface $parent, ?EntryContract $entry = null)
    {
        $parent->setConfigValue('meta.on_change_event', $parent->getHandle().':'.$parent->getColumnName().'={value}');

        $this->mergeConfig([
            'meta' => [
                'listen_event' => $parent->getHandle(),
                'autofetch'    => false,
            ],
        ]);

        if ($entry) {
            $this->mergeConfig([
                'meta' => [
                    'query'     => [$parent->getColumnName() => $entry->{$parent->getColumnName()}],
                    'autofetch' => false,
                ],
            ]);
        }
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function setForm(FormInterface $form): void
    {
        $this->form = $form;
    }

    public function getLocation(): ?FieldLocation
    {
        return $this->location;
    }

    public function setLocation(FieldLocation $location): FormFieldInterface
    {
        $this->location = $location;

        return $this;
    }

    public function setTemporal($temporal)
    {
        $this->temporal = $temporal;
    }

    public function isTemporal(): bool
    {
        return $this->temporal;
    }

    /**
     * Inline get underlying relation form
     *
     * @param array $config
     */
    public function inlineForm(array $config = [])
    {
        if (! $this->fieldType instanceof InlinesForm) {
            PlatformException::runtime('Field type '.$this->type.' does not provide any form to inline');
        }

        $this->fieldType->inlineForm($this->form, $config);
    }

    public function compose()
    {
        $composer = $this->getFieldType()->resolveComposer();

        return $composer->toForm($this->form);
    }

    public function getRow()
    {
        if ($location = $this->getLocation()) {
            return $location->row;
        }

        return 10;
    }

    public static function make(array $params): FormFieldInterface
    {
        return FieldFactory::createFromArray($params, self::class);
    }
}
