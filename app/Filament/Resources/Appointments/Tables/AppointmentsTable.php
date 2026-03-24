<?php

namespace App\Filament\Resources\Appointments\Tables;

use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient.name')
                    ->label('Paciente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('doctor.user.name')
                    ->label('Doctor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('appointment_date')
                    ->label('Fecha de la cita')
                    ->date()
                    ->sortable(),

                TextColumn::make('appointment_start_time')
                    ->label('Hora de inicio')
                    ->sortable()
                    ->formatStateUsing(fn($state): ?string => $state
                        ? Carbon::createFromFormat('H:i:s', $state)->format('h:i A')
                        : null),

                TextColumn::make('appointment_end_time')
                    ->label('Hora de finalización')
                    ->sortable()
                    ->formatStateUsing(fn($state): ?string => $state
                        ? Carbon::createFromFormat('H:i:s', $state)->format('h:i A')
                        : null),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'scheduled' => 'Programada',
                        'completed' => 'Completada',
                        'canceled' => 'Cancelada',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'scheduled' => 'warning',
                        'completed' => 'success',
                        'canceled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('reason')
                    ->label('Motivo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Fecha de registro')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Visibilidad del registro')
                    ->placeholder('Solo activos')
                    ->trueLabel('Todos')
                    ->falseLabel('Solo eliminados'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Editar'),

                    DeleteAction::make()
                        ->label('Eliminar')
                        ->modalHeading('Confirmar eliminación')
                        ->modalDescription(fn($record) => "¿Estás seguro de que deseas eliminar la cita de {$record->patient->name} con {$record->doctor->user->name}? Esta acción se puede revertir desde la papelera de reciclaje."),

                    RestoreAction::make()
                        ->label('Restaurar')
                        ->modalHeading('Confirmar restauración')
                        ->modalDescription(fn($record) => "¿Estás seguro de que deseas restaurar la cita de {$record->patient->name} con {$record->doctor->user->name}?"),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->button()
                    ->label(''),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
