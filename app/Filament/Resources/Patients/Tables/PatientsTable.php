<?php

namespace App\Filament\Resources\Patients\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\Console\Color;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('lastname')
                    ->label('Apellido')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                TextColumn::make('birth_date')
                    ->label('Fecha de nacimiento')
                    ->date()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('Género')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'M' => "info",
                        'F' => "pink",
                        default => "gray"
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'M' => 'Masculino',
                        'F' => 'Femenino',
                        default => 'Otro',
                    })
                    ->searchable(),
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
                    ->label('Visibilidad de registro')
                    ->placeholder('Solo activos')
                    ->trueLabel('Todos')
                    ->falseLabel('Solo eliminados')
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label("Editar Paciente"),
                    DeleteAction::make()
                        ->label("Eliminar Paciente")
                        ->modalHeading("Eliminar Paciente")
                        ->modalDescription("¿Estás seguro de que deseas eliminar este paciente?"),
                    RestoreAction::make()
                        ->label('Restaurar')
                        ->modalHeading("Restaurar Paciente")
                        ->modalDescription("¿Estás seguro de que deseas restaurar este paciente?"),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
