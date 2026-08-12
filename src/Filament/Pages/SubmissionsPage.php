<?php

declare(strict_types=1);

namespace Magna\Forms\Filament\Pages;

use Filament\Pages\Page;
use Magna\Forms\Models\Form;
use Magna\Forms\Models\Submission;

/**
 * What came in. Submissions are read-and-delete: nothing here edits a
 * visitor's words, and deletion is the only write, because a submission
 * that can be edited is no longer evidence of what was sent.
 */
class SubmissionsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox';

    protected static string|\UnitEnum|null $navigationGroup = 'Forms';

    protected static ?string $navigationLabel = 'Submissions';

    protected static ?string $title = 'Submissions';

    protected static ?string $slug = 'forms-submissions';

    protected string $view = 'magna-forms::filament.submissions';

    public ?string $formHandle = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('forms.submissions') ?? false;
    }

    public function deleteSubmission(string $submissionId): void
    {
        Submission::query()->whereKey($submissionId)->delete();
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $forms = Form::query()->orderBy('name')->get();
        $form = $this->formHandle !== null
            ? $forms->firstWhere('handle', $this->formHandle)
            : $forms->first();

        return [
            'forms' => $forms,
            'selected' => $form,
            'submissions' => $form === null ? collect() : Submission::query()
                ->where('form_id', $form->id)
                ->orderByDesc('created_at')
                ->limit(200)
                ->get(),
        ];
    }
}
