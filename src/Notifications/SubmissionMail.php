<?php

declare(strict_types=1);

namespace Magna\Forms\Notifications;

use Illuminate\Mail\Mailable;

/**
 * The notification a form's watchers receive. Values render escaped in a
 * plain table — a submission is public input and must never become markup
 * in someone's inbox.
 */
class SubmissionMail extends Mailable
{
    /** @param array<string, string> $values */
    public function __construct(
        public readonly string $formName,
        public readonly array $values,
    ) {}

    public function build(): self
    {
        return $this->subject('New submission: '.$this->formName)
            ->view('magna-forms::mail.submission');
    }
}
