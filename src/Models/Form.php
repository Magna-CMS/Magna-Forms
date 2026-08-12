<?php

declare(strict_types=1);

namespace Magna\Forms\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Magna\Forms\FormFields;

/**
 * @property string $id
 * @property string $handle
 * @property string $name
 * @property array<mixed, mixed> $fields
 * @property string $submit_label
 * @property string|null $success_message
 * @property list<string>|null $notify_emails
 * @property bool $store_submissions
 */
class Form extends Model
{
    use HasUlids;

    protected $table = 'forms_forms';

    protected $fillable = [
        'handle', 'name', 'fields', 'submit_label', 'success_message',
        'notify_emails', 'store_submissions',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'notify_emails' => 'array',
            'store_submissions' => 'bool',
        ];
    }

    /** @return HasMany<Submission, $this> */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * The validated field list — always read through this, never the raw
     * attribute, so a malformed stored field cannot reach validation.
     *
     * @return list<array{handle: string, label: string, type: string, required: bool, options: list<string>}>
     */
    public function normalizedFields(): array
    {
        return FormFields::normalize($this->fields);
    }
}
