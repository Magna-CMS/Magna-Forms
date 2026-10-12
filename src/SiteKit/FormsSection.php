<?php

declare(strict_types=1);

namespace Magna\Forms\SiteKit;

use Magna\Contracts\SiteKitSection;
use Magna\Forms\FormFields;
use Magna\Forms\Models\Form;

/**
 * Forms travel in the site kit under 'forms:forms'.
 *
 * The form block selects a form by HANDLE, so a kit whose contact page uses
 * it applied cleanly and rendered a hole until someone hand-created the form
 * on the target — the exact manual step a kit exists to remove. Handles are
 * the identity here for the same reason pages key by slug: ids are
 * environment facts.
 *
 * Submissions deliberately do not travel: they are environment data, not the
 * site. Fields pass through FormFields::normalize() on the way in AND out —
 * a kit is a file somebody can edit, so it earns no more trust than the
 * admin form builder does, and a malformed row is skipped rather than
 * aborting the sync.
 */
class FormsSection implements SiteKitSection
{
    private const ROW_KEYS = ['handle', 'name', 'fields', 'submit_label', 'success_message', 'notify_emails', 'store_submissions'];

    public function key(): string
    {
        return 'forms:forms';
    }

    /** @return list<array<string, mixed>> */
    public function export(): array
    {
        return array_values(Form::query()
            ->orderBy('handle')
            ->get()
            ->map(fn (Form $form): array => [
                'handle' => $form->handle,
                'name' => $form->name,
                'fields' => $form->normalizedFields(),
                'submit_label' => $form->submit_label,
                'success_message' => $form->success_message,
                'notify_emails' => $form->notify_emails,
                'store_submissions' => $form->store_submissions,
            ])
            ->all());
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{added: list<string>, changed: list<string>, localOnly: list<string>}
     */
    public function diff(array $rows): array
    {
        $local = [];
        foreach ($this->export() as $row) {
            $handle = $this->rowHandle($row);
            if ($handle !== null) {
                $local[$handle] = $this->comparable($row);
            }
        }

        $added = $changed = $seen = [];
        foreach ($rows as $row) {
            $handle = $this->rowHandle($row);
            if ($handle === null) {
                continue;
            }
            $seen[] = $handle;

            if (! array_key_exists($handle, $local)) {
                $added[] = $handle;
            } elseif ($local[$handle] !== $this->comparable($row)) {
                $changed[] = $handle;
            }
        }

        return [
            'added' => $added,
            'changed' => $changed,
            'localOnly' => array_values(array_diff(array_keys($local), $seen)),
        ];
    }

    /**
     * Upsert by handle. A row without a usable handle is skipped — same
     * stance the kit's patterns take: one bad row must not cost the site.
     *
     * @param  list<array<string, mixed>>  $rows
     */
    public function apply(array $rows, ?string $actorId = null): int
    {
        $written = 0;

        foreach ($rows as $row) {
            $handle = $this->rowHandle($row);
            if ($handle === null || ! is_string($row['name'] ?? null) || $row['name'] === '') {
                continue;
            }

            $attributes = ['name' => $row['name'], 'fields' => FormFields::normalize($row['fields'] ?? null)];
            if (is_string($row['submit_label'] ?? null) && $row['submit_label'] !== '') {
                $attributes['submit_label'] = $row['submit_label'];
            }
            if (array_key_exists('success_message', $row)) {
                $attributes['success_message'] = is_string($row['success_message']) ? $row['success_message'] : null;
            }
            if (is_array($row['notify_emails'] ?? null)) {
                $attributes['notify_emails'] = array_values(array_filter($row['notify_emails'], 'is_string'));
            }
            if (is_bool($row['store_submissions'] ?? null)) {
                $attributes['store_submissions'] = $row['store_submissions'];
            }

            Form::query()->updateOrCreate(['handle' => $handle], $attributes);
            $written++;
        }

        return $written;
    }

    /** @param array<string, mixed> $row */
    private function rowHandle(array $row): ?string
    {
        $handle = $row['handle'] ?? null;

        return is_string($handle) && $handle !== '' ? $handle : null;
    }

    /**
     * Normalised comparison form: known keys only, fields normalised, key
     * order fixed — so an export compares equal to itself re-imported.
     *
     * @param  array<string, mixed>  $row
     */
    private function comparable(array $row): string
    {
        $row = array_intersect_key($row, array_flip(self::ROW_KEYS));
        $row['fields'] = FormFields::normalize($row['fields'] ?? null);
        ksort($row);

        return (string) json_encode($row);
    }
}
