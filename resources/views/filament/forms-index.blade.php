<x-filament-panels::page>
    {{-- Plugin-owned layout CSS. The panel theme is compiled from core views
         only, so any utility class the core never uses is absent from the
         built stylesheet — on a customer install this page cannot trigger a
         rebuild. Everything structural therefore lives here, scoped under a
         plugin prefix, and the markup below sticks to Filament components
         and utilities the compiled theme is known to ship. --}}
    <style>
        .mgf-field-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        @media (min-width: 768px) {
            .mgf-field-row {
                grid-template-columns: 9rem minmax(0, 1fr) 8rem minmax(0, 1fr) auto;
                align-items: center;
            }
        }

        .mgf-row-active {
            background: rgba(124, 58, 237, 0.06);
        }

        .dark .mgf-row-active {
            background: rgba(124, 58, 237, 0.12);
        }
    </style>

    <div class="space-y-6">
        <div class="flex flex-wrap items-end gap-2">
            <label class="block">
                <span class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">New form</span>
                <x-filament::input.wrapper class="w-56">
                    <x-filament::input type="text" wire:model="newFormName" wire:keydown.enter="createForm"
                                       placeholder="Contact" />
                </x-filament::input.wrapper>
            </label>
            <x-filament::button wire:click="createForm">Create form</x-filament::button>
        </div>

        <div class="fi-ta-ctn overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($forms as $form)
                        <tr class="{{ $selectedFormId === $form->id ? 'mgf-row-active' : '' }}">
                            <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ $form->name }}</td>
                            <td class="px-4 py-2.5 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $form->handle }}</td>
                            <td class="px-4 py-2.5 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $form->submissions_count }} submissions</td>
                            <td class="px-4 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button type="button" wire:click="selectForm('{{ $form->id }}')"
                                            class="text-xs font-semibold text-primary-600 dark:text-primary-400">Edit</button>
                                    <button type="button" wire:click="deleteForm('{{ $form->id }}')"
                                            wire:confirm="Delete this form and every submission it collected?"
                                            class="text-xs font-semibold text-gray-500 hover:text-danger-600">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No forms yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($selectedFormId !== null)
            <x-filament::section>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Name</span>
                            <x-filament::input.wrapper>
                                <x-filament::input type="text" wire:model="draft.name" />
                            </x-filament::input.wrapper>
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Submit button</span>
                            <x-filament::input.wrapper>
                                <x-filament::input type="text" wire:model="draft.submit_label" />
                            </x-filament::input.wrapper>
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Success message</span>
                            <x-filament::input.wrapper>
                                <x-filament::input type="text" wire:model="draft.success_message" />
                            </x-filament::input.wrapper>
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Notify (comma-separated emails)</span>
                            <x-filament::input.wrapper>
                                <x-filament::input type="text" wire:model="draft.notify_emails" />
                            </x-filament::input.wrapper>
                        </label>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <x-filament::input.checkbox wire:model="draft.store_submissions" />
                        Store submissions in the admin
                    </label>

                    <div class="space-y-2">
                        @foreach($draft['fields'] ?? [] as $index => $field)
                            <div class="mgf-field-row">
                                <x-filament::input.wrapper>
                                    <x-filament::input type="text" wire:model="draft.fields.{{ $index }}.handle"
                                                       placeholder="handle" class="font-mono text-xs" />
                                </x-filament::input.wrapper>
                                <x-filament::input.wrapper>
                                    <x-filament::input type="text" wire:model="draft.fields.{{ $index }}.label"
                                                       placeholder="Label" />
                                </x-filament::input.wrapper>
                                <x-filament::input.wrapper>
                                    <x-filament::input.select wire:model="draft.fields.{{ $index }}.type">
                                        @foreach($fieldTypes as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>
                                <x-filament::input.wrapper>
                                    <x-filament::input type="text" wire:model="draft.fields.{{ $index }}.options"
                                                       placeholder="select options, comma-separated" />
                                </x-filament::input.wrapper>
                                <div class="flex items-center gap-3">
                                    <label class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                        <x-filament::input.checkbox wire:model="draft.fields.{{ $index }}.required" />
                                        required
                                    </label>
                                    <button type="button" wire:click="removeField({{ $index }})"
                                            class="text-xs font-semibold text-gray-500 hover:text-danger-600">Remove</button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-2">
                        <x-filament::button color="gray" wire:click="addField">Add field</x-filament::button>
                        <x-filament::button wire:click="save">Save form</x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
