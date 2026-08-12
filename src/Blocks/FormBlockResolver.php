<?php

declare(strict_types=1);

namespace Magna\Forms\Blocks;

use Magna\Blocks\Resolution\ResolvesBlockData;
use Magna\Forms\FormSubmissionHandler;
use Magna\Forms\Models\Form;

/**
 * Resolves the form block: the chosen form's definition, ready for the
 * view. A form that was deleted resolves to nothing — the page renders a
 * gap, never an error, same as every other vanished reference.
 */
final class FormBlockResolver implements ResolvesBlockData
{
    public function handle(): string
    {
        return 'form';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function resolve(array $data): array
    {
        $handle = $data['form'] ?? null;
        $form = is_string($handle) ? Form::query()->where('handle', $handle)->first() : null;

        if ($form === null) {
            return [];
        }

        return [
            'handle' => $form->handle,
            'name' => $form->name,
            'fields' => $form->normalizedFields(),
            'submitLabel' => $form->submit_label,
            // Signed at render: the anti-bot timing check needs a stamp the
            // page issued, not one a poster invented.
            'timestamp' => FormSubmissionHandler::timestampToken(),
            'honeypot' => FormSubmissionHandler::HONEYPOT,
        ];
    }
}
