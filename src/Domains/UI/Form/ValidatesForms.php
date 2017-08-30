<?php

namespace SuperV\Platform\Domains\UI\Form;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Symfony\Component\Form\Form;

trait ValidatesForms
{
    use ValidatesRequests;

    /**
     * Validate the given request with the given rules.
     *
     * @param \Symfony\Component\Form\Form $form
     * @param \Illuminate\Http\Request     $request
     * @param array                        $rules
     * @param array                        $messages
     *
     * @return void
     */
    public function validateForm(Form $form, Request $request, array $rules, array $messages = [])
    {
        $data = $form->getName() ? $request->offsetGet($form->getName()) : $request->all();
        $validator = $this->getValidationFactory()->make($data, $rules, $messages);

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }
    }
}
