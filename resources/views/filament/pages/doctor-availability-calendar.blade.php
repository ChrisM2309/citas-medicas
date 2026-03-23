<x-filament-panels::page>
    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Médico
                </label>

                @if (empty($this->doctorOptions))
                    <div class="rounded-lg border border-dashed border-gray-300 p-3 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">
                        No hay médicos disponibles para mostrar en el calendario.
                    </div>
                @else
                    <select
                        wire:model.live="doctorId"
                        @disabled($this->doctorLocked)
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:disabled:bg-gray-800"
                    >
                        @foreach ($this->doctorOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Mes
                </label>

                <input
                    type="month"
                    wire:model.live="month"
                    class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                />
            </div>

            <div class="flex items-end gap-2">
                <x-filament::button color="gray" wire:click="previousMonth" class="w-full">
                    Mes anterior
                </x-filament::button>
                <x-filament::button wire:click="nextMonth" class="w-full">
                    Mes siguiente
                </x-filament::button>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $this->monthLabel }}
                    </h2>
                    @if ($this->selectedDoctorName)
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Disponibilidad de {{ $this->selectedDoctorName }}
                        </p>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2 text-xs font-medium">
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                        Disponible
                    </span>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                        Ocupado
                    </span>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        Sin horario
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-7 gap-2 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                @foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $dayName)
                    <div>{{ $dayName }}</div>
                @endforeach
            </div>

            <div class="mt-2 grid grid-cols-7 gap-2">
                @forelse ($this->calendarCells as $cell)
                    @if (! $cell)
                        <div class="aspect-square rounded-lg bg-transparent"></div>
                        @continue
                    @endif

                    @php
                        $stateClasses = match ($cell['state']) {
                            'available' => 'bg-emerald-50 border-emerald-200 text-emerald-900 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-200',
                            'occupied' => 'bg-amber-50 border-amber-200 text-amber-900 dark:bg-amber-500/10 dark:border-amber-500/30 dark:text-amber-200',
                            default => 'bg-gray-50 border-gray-200 text-gray-600 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300',
                        };
                    @endphp

                    <div class="aspect-square rounded-lg border p-2 {{ $stateClasses }}">
                        <div class="text-sm font-semibold">{{ $cell['day'] }}</div>
                        <div class="mt-1 space-y-1 text-[11px] leading-tight">
                            <div>Horarios: {{ $cell['schedules_count'] }}</div>
                            <div>Citas: {{ $cell['appointments_count'] }}</div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-7 rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">
                        Selecciona un médico para visualizar su disponibilidad mensual.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-filament-panels::page>
