<?php

namespace App\Filament\Resources\Schedules\Schemas;

use App\Models\Doctor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('doctor_id')
                    ->label('Doctor')
                    ->options(
                        Doctor::with('user')
                            ->whereNull('deleted_at')
                            ->get()
                            ->mapWithKeys(fn(Doctor $doctor) => [
                                $doctor->id => $doctor->user?->name ?? 'Doctor sin nombre',
                            ])
                            ->toArray()
                    )
                    ->searchable()
                    ->required(),

                Select::make('day_of_week')
                    ->label('Día de la semana')
                    ->options([
                        'Monday' => 'Lunes',
                        'Tuesday' => 'Martes',
                        'Wednesday' => 'Miércoles',
                        'Thursday' => 'Jueves',
                        'Friday' => 'Viernes',
                        'Saturday' => 'Sábado',
                        'Sunday' => 'Domingo',
                    ])
                    ->required()
                    ->in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']),

                TimePicker::make('start_time')
                    ->label('Hora de inicio')
                    ->required()
                    ->seconds(false)
                    ->format('H:i'),

                TimePicker::make('end_time')
                    ->label('Hora de fin')
                    ->required()
                    ->seconds(false)
                    ->format('H:i')
                    ->rule(function (callable $get) {
                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                            $start = $get('start_time');

                            if (! $start || ! $value) {
                                return;
                            }

                            if ($value <= $start) {
                                $fail('La hora de fin debe ser posterior a la hora de inicio.');
                            }
                        };
                    }),
            ]);
    }
}
