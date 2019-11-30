<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\DateTime;

use Carbon\Carbon;
use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasPresenter;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\Contracts\SortsQuery;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Support\Composer\Payload;

class DateTimeField extends FieldType implements RequiresDbColumn, HasPresenter, SortsQuery
{
    protected function boot()
    {
        $this->field->on('table.presenting', $this->getPresenter());
        $this->field->on('view.accessing', $this->formPresenter());
        $this->field->on('form.accessing', $this->formPresenter());
        $this->field->on('form.composing', $this->formComposer());
    }

    public function sortQuery($query, $direction)
    {
        $query->orderBy($this->field->getColumnName(), $direction);
    }

    public function getPresenter(): Closure
    {
        return function (EntryContract $entry) {
            if (! $value = $entry->getAttribute($this->getName())) {
                return null;
            }

            if (! $value instanceof Carbon) {
                $value = Carbon::parse($value);
            }

            return $value->format($this->getFormat());
        };
    }

    protected function formComposer()
    {
        return function (Payload $payload, ?EntryContract $entry) {
            $payload->set('config.time', $this->getConfigValue('time'));
        };
    }

    protected function formPresenter()
    {
        return function (EntryContract $entry) {
            if (! $value = $entry->getAttribute($this->getName())) {
                return null;
            }

            if (! $value instanceof Carbon) {
                $value = Carbon::parse($value);
            }

            return $value->format('Y-m-d H:i:s');
        };
    }

    protected function getFormat()
    {
        $default = $this->hasTime() ? 'M j, Y H:i' : 'M j, Y';

        return $this->getConfigValue('format', $default);
    }

    protected function hasTime()
    {
        return $this->getConfigValue('time') === true;
    }

    public function driverCreating(DriverInterface $driver)
    {
        if ($driver instanceof DatabaseDriver) {
            $driver->getTable()->addColumn($this->getColumnName(), 'datetime');
        }
    }
}
