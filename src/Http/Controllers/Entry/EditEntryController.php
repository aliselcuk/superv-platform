<?php

namespace SuperV\Platform\Http\Controllers\Entry;

use SuperV\Platform\Domains\UI\Form\FormBuilder;
use SuperV\Platform\Http\Controllers\BasePlatformController;

class EditEntryController extends BasePlatformController
{
    public function index($ticket, FormBuilder $builder)
    {
        if ($config = superv('cache')->get('superv::entry.tickets.edit:'.$ticket)) {
            $model = array_get($config, 'model');
            $id = array_get($config, 'id');

            return $builder->render($model::findOrFail($id));
        }

        throw new \Exception('Ticket not found: '.$ticket);
    }
}
