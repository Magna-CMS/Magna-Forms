<?php

declare(strict_types=1);

namespace Magna\Forms\Blocks;

use Magna\Blocks\Contracts\ProvidesOptions;
use Magna\Forms\Models\Form;

/** The form block's picker: every form on the site, by name. */
final class FormOptions implements ProvidesOptions
{
    /** @return array<string, string> */
    public function options(): array
    {
        /** @var array<string, string> */
        return Form::query()->orderBy('name')->pluck('name', 'handle')->all();
    }
}
