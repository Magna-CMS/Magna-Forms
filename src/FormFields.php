<?php

declare(strict_types=1);

namespace Magna\Forms;

/**
 * The field-type allowlist and the validation rules each type earns.
 *
 * A form definition is editor input; a submission is PUBLIC input. Rules
 * are derived from the definition here and nowhere else, so a submitted
 * key that no declared field claims is dropped before it can be stored —
 * "validate what you declared, ignore the rest" rather than trusting a
 * payload's shape.
 */
final class FormFields
{
    /** type => base validation rules. */
    private const TYPES = [
        'text' => ['string', 'max:255'],
        'email' => ['email:rfc', 'max:255'],
        'tel' => ['string', 'max:40'],
        'url' => ['url', 'max:2048'],
        'number' => ['numeric'],
        'textarea' => ['string', 'max:5000'],
        'select' => ['string', 'max:255'],
        'checkbox' => ['accepted'],
    ];

    /** @return list<string> */
    public static function types(): array
    {
        return array_keys(self::TYPES);
    }

    /**
     * Normalize a stored field list: drop anything malformed, so a
     * definition that predates a rename can never widen validation.
     *
     * @return list<array{handle: string, label: string, type: string, required: bool, options: list<string>}>
     */
    public static function normalize(mixed $fields): array
    {
        if (! is_array($fields)) {
            return [];
        }

        $clean = [];
        foreach ($fields as $field) {
            if (! is_array($field)) {
                continue;
            }

            $handle = $field['handle'] ?? null;
            $type = $field['type'] ?? null;
            if (! is_string($handle) || preg_match('/^[a-z][a-z0-9_]{0,39}$/', $handle) !== 1
                || ! is_string($type) || ! array_key_exists($type, self::TYPES)
            ) {
                continue;
            }

            $options = [];
            if ($type === 'select' && is_array($field['options'] ?? null)) {
                $options = array_values(array_filter($field['options'], 'is_string'));
            }

            $clean[] = [
                'handle' => $handle,
                'label' => is_string($field['label'] ?? null) && $field['label'] !== '' ? $field['label'] : $handle,
                'type' => $type,
                'required' => ($field['required'] ?? false) === true,
                'options' => $options,
            ];
        }

        return $clean;
    }

    /**
     * Laravel rules for a normalized field list.
     *
     * @param  list<array{handle: string, label: string, type: string, required: bool, options: list<string>}>  $fields
     * @return array<string, list<string>>
     */
    public static function rules(array $fields): array
    {
        $rules = [];
        foreach ($fields as $field) {
            $base = self::TYPES[$field['type']];

            // A checkbox is present or absent; `accepted` already means
            // required, and an optional one must tolerate absence.
            if ($field['type'] === 'checkbox') {
                $rules['fields.'.$field['handle']] = $field['required'] ? $base : ['nullable', 'boolean'];

                continue;
            }

            $rule = [$field['required'] ? 'required' : 'nullable', ...$base];

            // A select may only submit what it offers — the options are the
            // allowlist, not a suggestion.
            if ($field['type'] === 'select' && $field['options'] !== []) {
                $rule[] = 'in:'.implode(',', $field['options']);
            }

            $rules['fields.'.$field['handle']] = $rule;
        }

        return $rules;
    }

    /**
     * The submitted values reduced to declared fields, as scalars.
     *
     * @param  list<array{handle: string, label: string, type: string, required: bool, options: list<string>}>  $fields
     * @param  array<mixed, mixed>  $submitted
     * @return array<string, string>
     */
    public static function values(array $fields, array $submitted): array
    {
        $values = [];
        foreach ($fields as $field) {
            $value = $submitted[$field['handle']] ?? null;
            if ($field['type'] === 'checkbox') {
                $values[$field['handle']] = $value ? 'yes' : 'no';

                continue;
            }
            if (is_string($value) || is_numeric($value)) {
                $values[$field['handle']] = (string) $value;
            }
        }

        return $values;
    }
}
