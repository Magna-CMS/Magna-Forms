<?php

declare(strict_types=1);

namespace Magna\Forms\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use Magna\Forms\FormFields;
use Magna\Forms\Models\Form;

/**
 * The Forms screen: create a form, add typed fields, set who gets told.
 * State-on-page Livewire, same shape as the Menus builder — the field
 * list is edited as a small tree and saved whole.
 */
class FormsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|\UnitEnum|null $navigationGroup = 'Forms';

    protected static ?string $navigationLabel = 'Forms';

    protected static ?string $title = 'Forms';

    protected static ?string $slug = 'forms-index';

    protected string $view = 'magna-forms::filament.forms-index';

    public string $newFormName = '';

    public ?string $selectedFormId = null;

    /** @var array<string, mixed> */
    public array $draft = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('forms.manage') ?? false;
    }

    public function mount(): void
    {
        $first = Form::query()->orderBy('name')->first();
        if ($first !== null) {
            $this->selectForm($first->id);
        }
    }

    public function selectForm(string $formId): void
    {
        $form = Form::query()->find($formId);
        if ($form === null) {
            return;
        }

        $this->selectedFormId = $form->id;
        $this->draft = [
            'name' => $form->name,
            'submit_label' => $form->submit_label,
            'success_message' => $form->success_message ?? '',
            'notify_emails' => implode(', ', $form->notify_emails ?? []),
            'store_submissions' => $form->store_submissions,
            'fields' => $form->normalizedFields(),
        ];
    }

    public function createForm(): void
    {
        $name = trim($this->newFormName);
        if ($name === '') {
            Notification::make()->title('Give the form a name first.')->warning()->send();

            return;
        }

        $handle = Str::slug($name, '_');
        if ($handle === '' || Form::query()->where('handle', $handle)->exists()) {
            Notification::make()->title('A form with that name already exists.')->danger()->send();

            return;
        }

        $form = Form::query()->create([
            'handle' => $handle,
            'name' => $name,
            'fields' => [
                ['handle' => 'name', 'label' => 'Your name', 'type' => 'text', 'required' => true],
                ['handle' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                ['handle' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true],
            ],
        ]);

        $this->newFormName = '';
        $this->selectForm($form->id);
    }

    public function addField(): void
    {
        $this->draft['fields'][] = [
            'handle' => '', 'label' => '', 'type' => 'text', 'required' => false, 'options' => [],
        ];
    }

    public function removeField(int $index): void
    {
        unset($this->draft['fields'][$index]);
        $this->draft['fields'] = array_values($this->draft['fields']);
    }

    public function save(): void
    {
        $form = $this->selectedFormId !== null ? Form::query()->find($this->selectedFormId) : null;
        if ($form === null) {
            return;
        }

        // Select options arrive as one comma-separated string per field.
        $fields = [];
        foreach ($this->draft['fields'] ?? [] as $field) {
            if (! is_array($field)) {
                continue;
            }
            if (is_string($field['options'] ?? null)) {
                $field['options'] = array_values(array_filter(array_map(
                    'trim',
                    explode(',', $field['options']),
                ), fn (string $option): bool => $option !== ''));
            }
            $fields[] = $field;
        }

        // Normalizing before save is what makes the definition trustworthy
        // at validation time: a malformed row never reaches the table.
        $normalized = FormFields::normalize($fields);

        $form->update([
            'name' => is_string($this->draft['name'] ?? null) && $this->draft['name'] !== ''
                ? $this->draft['name']
                : $form->name,
            'submit_label' => is_string($this->draft['submit_label'] ?? null) && $this->draft['submit_label'] !== ''
                ? $this->draft['submit_label']
                : 'Send',
            'success_message' => is_string($this->draft['success_message'] ?? null) && trim($this->draft['success_message']) !== ''
                ? trim($this->draft['success_message'])
                : null,
            'notify_emails' => $this->cleanEmails($this->draft['notify_emails'] ?? ''),
            'store_submissions' => (bool) ($this->draft['store_submissions'] ?? true),
            'fields' => $normalized,
        ]);

        $this->selectForm($form->id);
        Notification::make()->title('Form saved')->success()->send();
    }

    public function deleteForm(string $formId): void
    {
        Form::query()->whereKey($formId)->delete();

        $this->selectedFormId = null;
        $this->draft = [];
        $this->mount();
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return [
            'forms' => Form::query()->withCount('submissions')->orderBy('name')->get(),
            'fieldTypes' => FormFields::types(),
        ];
    }

    /** @return list<string> */
    private function cleanEmails(mixed $raw): array
    {
        if (! is_string($raw)) {
            return [];
        }

        $emails = [];
        foreach (explode(',', $raw) as $candidate) {
            $candidate = trim($candidate);
            if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL) !== false) {
                $emails[] = $candidate;
            }
        }

        return $emails;
    }
}
