<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex flex-wrap items-end gap-2">
            <label class="block">
                <span class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Form</span>
                <select wire:model.live="formHandle"
                        class="w-64 rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    @foreach($forms as $form)
                        <option value="{{ $form->handle }}">{{ $form->name }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="space-y-3">
            @forelse($submissions as $submission)
                <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $submission->created_at?->diffForHumans() }}
                        </span>
                        <button wire:click="deleteSubmission('{{ $submission->id }}')"
                                wire:confirm="Delete this submission?"
                                class="text-xs font-semibold text-gray-500 hover:text-danger-600">Delete</button>
                    </div>
                    <dl class="grid gap-1 text-sm sm:grid-cols-[10rem_1fr]">
                        @foreach($submission->data as $handle => $value)
                            <dt class="text-gray-500 dark:text-gray-400">{{ $handle }}</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $value }}</dd>
                        @endforeach
                    </dl>
                </div>
            @empty
                <p class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nothing submitted yet.</p>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
