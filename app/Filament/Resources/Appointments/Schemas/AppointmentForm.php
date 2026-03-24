<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Doctor;
use App\Models\Patient;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('patient_id')
                    ->label('Paciente')
                    ->options(
                        Patient::query()
                            ->whereNull('deleted_at')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn(Patient $patient) => [
                                $patient->id => trim($patient->name . ' ' . ($patient->lastname ?? '')),
                            ])
                            ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

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
                    ->preload()
                    ->required(),

                DatePicker::make('appointment_date')
                    ->label('Fecha de cita')
                    ->required()
                    ->minDate(fn(string $operation) => $operation === 'create'
                        ? now('America/El_Salvador')->toDateString()
                        : null),

                TimePicker::make('appointment_start_time')
                    ->label('Hora de inicio')
                    ->required()
                    ->seconds(false)
                    ->format('H:i'),

                TimePicker::make('appointment_end_time')
                    ->label('Hora de fin')
                    ->required()
                    ->seconds(false)
                    ->format('H:i')
                    ->rule(function (callable $get) {
                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                            $start = $get('appointment_start_time');

                            if (! $start || ! $value) {
                                return;
                            }

                            if ($value <= $start) {
                                $fail('La hora de fin debe ser posterior a la hora de inicio.');
                            }
                        };
                    }),

                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'scheduled' => 'Programada',
                        'completed' => 'Completada',
                        'canceled' => 'Cancelada',
                    ])
                    ->default('scheduled')
                    ->required(),

                Textarea::make('reason')
                    ->label('Motivo')
                    ->rows(3)
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}
