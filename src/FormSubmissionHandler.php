<?php

declare(strict_types=1);

namespace Magna\Forms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Magna\Forms\Models\Form;
use Magna\Forms\Models\Submission;
use Magna\Forms\Notifications\SubmissionMail;
use Magna\Frontend\Forms\SignedFormTimestamp;

/**
 * What happens when the public posts a form: spam checks, validation
 * derived from the form's own definition, storage, notification.
 *
 * Spam handling is deliberately quiet — a bot that learns which trap it
 * tripped defeats the trap. A honeypot hit or an instant submission
 * returns the same success the visitor would see, and stores nothing.
 */
class FormSubmissionHandler
{
    /** Field name of the honeypot the real form renders hidden. */
    public const HONEYPOT = 'website_url';

    /** Nothing human fills a form this fast. */
    private const MIN_SECONDS = 2;

    /**
     * The signing scope of the render stamp. It is the prefix the plugin
     * signed with before the stamp moved into core, so stamps already
     * sitting in cached pages keep passing.
     */
    private const STAMP_PURPOSE = 'magna-forms';

    /**
     * @return array{ok: bool, errors: array<string, list<string>>, message: string}
     */
    public function handle(Form $form, Request $request): array
    {
        $success = [
            'ok' => true,
            'errors' => [],
            'message' => $form->success_message ?? 'Thanks — we got your message.',
        ];

        if ($this->looksAutomated($request)) {
            return $success;
        }

        $fields = $form->normalizedFields();
        $validator = Validator::make(
            ['fields' => $request->input('fields', [])],
            FormFields::rules($fields),
            [],
            $this->attributeNames($fields),
        );

        if ($validator->fails()) {
            /** @var array<string, list<string>> $errors */
            $errors = $validator->errors()->messages();

            return ['ok' => false, 'errors' => $errors, 'message' => 'Please check the highlighted fields.'];
        }

        $submitted = $request->input('fields', []);
        $values = FormFields::values($fields, is_array($submitted) ? $submitted : []);

        if ($form->store_submissions) {
            Submission::query()->create([
                'form_id' => $form->id,
                'data' => $values,
                'ip_hash' => Submission::hashIp($request->ip()),
            ]);
        }

        $this->notify($form, $values);

        return $success;
    }

    private function looksAutomated(Request $request): bool
    {
        // Honeypot: a hidden field only a bot fills.
        $honeypot = $request->input(self::HONEYPOT);
        if (is_string($honeypot) && trim($honeypot) !== '') {
            return true;
        }

        // Timing: the render timestamp is signed, so a bot cannot simply
        // post an older one to pass the check.
        return ! SignedFormTimestamp::passes(
            $request->input(SignedFormTimestamp::FIELD),
            self::STAMP_PURPOSE,
            self::MIN_SECONDS,
        );
    }

    /** The signed render timestamp the form embeds. */
    public static function timestampToken(): string
    {
        return SignedFormTimestamp::issue(self::STAMP_PURPOSE);
    }

    /**
     * @param  list<array{handle: string, label: string, type: string, required: bool, options: list<string>}>  $fields
     * @return array<string, string>
     */
    private function attributeNames(array $fields): array
    {
        $names = [];
        foreach ($fields as $field) {
            $names['fields.'.$field['handle']] = strtolower($field['label']);
        }

        return $names;
    }

    /** @param array<string, string> $values */
    private function notify(Form $form, array $values): void
    {
        foreach ($form->notify_emails ?? [] as $address) {
            if (filter_var($address, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }

            try {
                Mail::to($address)->send(new SubmissionMail($form->name, $values));
            } catch (\Throwable $e) {
                // A mail outage must not lose the submission or show the
                // visitor an error — it is already stored.
                report($e);
            }
        }
    }
}
