<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex flex-wrap items-end gap-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">New form</label>
                <input type="text" wire:model="newFormName" wire:keydown.enter="createForm"
                       placeholder="Contact"
                       class="w-64 rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <x-filament::button wire:click="createForm">Create form</x-filament::button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10">
            <table class="w-full text-sm">
                <tbody>
                    @forelse($forms as $form)
                        <tr class="border-b border-gray-100 last:border-0 dark:border-white/5 {{ $selectedFormId === $form->id ? 'bg-primary-50/60 dark:bg-primary-500/10' : '' }}">
                            <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ $form->name }}</td>
                            <td class="px-4 py-2.5 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $form->handle }}</td>
                            <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">{{ $form->submissions_count }} submissions</td>
                            <td class="px-4 py-2.5 text-right">
                                <button wire:click="selectForm('{{ $form->id }}')"
                                        class="text-xs font-semibold text-primary-600 dark:text-primary-400">Edit</button>
                                <button wire:click="deleteForm('{{ $form->id }}')"
                                        wire:confirm="Delete this form and every submission it collected?"
                                        class="ml-3 text-xs font-semibold text-gray-500 hover:text-danger-600">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No forms yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($selectedFormId !== null)
            <div class="space-y-4 rounded-xl border border-gray-200 p-4 dark:border-white/10">
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Name</span>
                        <input type="text" wire:model="draft.name"
                               class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Submit button</span>
                        <input type="text" wire:model="draft.submit_label"
                               class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Success message</span>
                        <input type="text" wire:model="draft.success_message"
                               class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Notify (comma-separated emails)</span>
                        <input type="text" wire:model="draft.notify_emails"
                               class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </label>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model="draft.store_submissions">
                    Store submissions in the admin
                </label>

                <div class="space-y-2">
                    @foreach($draft['fields'] ?? [] as $index => $field)
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-5">
                            <input type="text" wire:model="draft.fields.{{ $index }}.handle" placeholder="handle"
                                   class="rounded-lg border-gray-300 font-mono text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <input type="text" wire:model="draft.fields.{{ $index }}.label" placeholder="Label"
                                   class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <select wire:model="draft.fields.{{ $index }}.type"
                                    class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                @foreach($fieldTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                            <input type="text" wire:model="draft.fields.{{ $index }}.options" placeholder="select options, comma-separated"
                                   class="rounded-lg border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                    <input type="checkbox" wire:model="draft.fields.{{ $index }}.required"> required
                                </label>
                                <button wire:click="removeField({{ $index }})"
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
        @endif
    </div>
</x-filament-panels::page>
