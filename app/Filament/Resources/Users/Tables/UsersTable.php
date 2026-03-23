<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email_verified_at')
                    ->label('Fecha de verificación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
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
                    ->falseLabel('Solo eliminados'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Editar'),

                    DeleteAction::make()
                        ->label('Eliminar')
                        ->modalHeading('Confirmar eliminación')
                        ->modalDescription(fn($record) => "¿Estás seguro de que deseas eliminar a {$record->name}? Esta acción se puede revertir desde la papelera de reciclaje."),

                    RestoreAction::make()
                        ->label('Restaurar')
                        ->modalHeading('Confirmar restauración')
                        ->modalDescription(fn($record) => "¿Estás seguro de que deseas restaurar a {$record->name}?"),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->button()
                    ->label(''),
            ]);
    }
}
