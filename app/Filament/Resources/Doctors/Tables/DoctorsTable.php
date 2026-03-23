<?php

namespace App\Filament\Resources\Doctors\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DoctorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nombre del Doctor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Correo Electrónico')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('specialty')
                    ->label('Especialidad')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),

                IconColumn::make('user.is_active')
                    ->label('Estado')
                    ->boolean()
                    ->trueColor('primary')
                    ->falseColor('gray'),

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
                    ->falseLabel('Solo eliminados')
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->label('Editar'),
                    DeleteAction::make()->label('Eliminar')
                        ->modalHeading('Confirmar eliminación')
                        ->modalDescription(fn($record) => "¿Estás seguro de que deseas eliminar a {$record->user->name}? Esta acción se puede revertir desde la papelera de reciclaje."),
                    RestoreAction::make()->label('Restaurar')
                        ->modalHeading('Confirmar restauración')
                        ->modalDescription(fn($record) => "¿Estás seguro de que deseas restaurar a {$record->user->name}?"),
                ])->icon('heroicon-m-ellipsis-vertical')->button()->label(''),
            ]);
    }
}
