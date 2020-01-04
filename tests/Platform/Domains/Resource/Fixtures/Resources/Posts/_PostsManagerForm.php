<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources\Posts;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\FormFields;
use SuperV\Platform\Domains\Resource\Hook\Contracts\FormResolvingHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\HookByRole;

class _PostsManagerForm implements FormResolvingHook, HookByRole
{
    public static $identifier = 'sv.testing.posts.forms:default';

    public function resolving(FormInterface $form, FormFields $fields)
    {
        $_SERVER['__hooks::form.default.resolving'] .= '.PostsManagerForm';
    }

    public static function getRole(): string
    {
        return 'manager';
    }
}
