<x-filament-panels::page>
    {{-- See forms-index.blade.php: structural CSS is plugin-owned because the
         compiled panel theme only contains utilities the core itself uses. --}}
    <style>
        .mgf-sub-grid {
            display: grid;
            gap: 0.25rem;
        }

        @media (min-width: 640px) {
            .mgf-sub-grid {
                grid-template-columns: 10rem minmax(0, 1fr);
            }
        }
    </style>

    <div class="space-y-4">
        <div class="flex flex-wrap items-end gap-2">
            <label class="block">
                <span class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Form</span>
                <x-filament::input.wrapper class="w-56">
                    <x-filament::input.select wire:model.live="formHandle">
                        @foreach($forms as $form)
                            <option value="{{ $form->handle }}">{{ $form->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </label>
        </div>

        <div class="space-y-3">
            @forelse($submissions as $submission)
                <x-filament::section>
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $submission->created_at?->diffForHumans() }}
                        </span>
                        <button type="button" wire:click="deleteSubmission('{{ $submission->id }}')"
                                wire:confirm="Delete this submission?"
                                class="text-xs font-semibold text-gray-500 hover:text-danger-600">Delete</button>
                    </div>
                    <dl class="mgf-sub-grid text-sm">
                        @foreach($submission->data as $handle => $value)
                            <dt class="text-gray-500 dark:text-gray-400">{{ $handle }}</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $value }}</dd>
                        @endforeach
                    </dl>
                </x-filament::section>
            @empty
                <p class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nothing submitted yet.</p>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
