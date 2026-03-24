<?php

namespace App\Filament\Resources\Schedules\Tables;

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

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('doctor.user.name')
                    ->label('Doctor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('day_of_week')
                    ->label('Día de la semana')
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'Monday' => 'Lunes',
                        'Tuesday' => 'Martes',
                        'Wednesday' => 'Miércoles',
                        'Thursday' => 'Jueves',
                        'Friday' => 'Viernes',
                        'Saturday' => 'Sábado',
                        'Sunday' => 'Domingo',
                        default => $state,
                    }),

                TextColumn::make('start_time')
                    ->label('Hora de inicio')
                    ->sortable()
                    ->formatStateUsing(fn($state): ?string => $state
                        ? Carbon::createFromFormat('H:i:s', $state)->format('h:i A')
                        : null),

                TextColumn::make('end_time')
                    ->label('Hora de finalización')
                    ->sortable()
                    ->formatStateUsing(fn($state): ?string => $state
                        ? Carbon::createFromFormat('H:i:s', $state)->format('h:i A')
                        : null),

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
                        ->modalDescription(fn($record) => "¿Estás seguro de que deseas eliminar el horario de {$record->doctor->user->name}? Esta acción se puede revertir desde la papelera de reciclaje."),

                    RestoreAction::make()
                        ->label('Restaurar')
                        ->modalHeading('Confirmar restauración')
                        ->modalDescription(fn($record) => "¿Estás seguro de que deseas restaurar el horario de {$record->doctor->user->name}?"),
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
