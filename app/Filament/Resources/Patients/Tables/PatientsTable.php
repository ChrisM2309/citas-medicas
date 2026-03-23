<?php

namespace App\Filament\Resources\Patients\Tables;

use App\Models\Patient;
// Usamos las Acciones unificadas de Filament (v3.2+)
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
// Componentes esenciales
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('lastname')->label('Apellido')->searchable(),
                TextColumn::make('email')->label('Correo electrónico')->placeholder('ejemplo@correo.com')->searchable(),
                TextColumn::make('phone')->label('Teléfono'),
                TextColumn::make('gender')
                    ->label('Género')
                    ->badge()
                    ->color(fn($state) => $state === 'M' ? 'info' : 'pink')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'M' => 'Masculino',
                        'F' => 'Femenino',
                        default => 'Otro',
                    }),
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
                    Action::make('manage_medical_record')
                        ->label('Gestionar Historial')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->color('primary')
                        ->visible(fn() => Auth::user()->can('manage_medical_records'))
                        ->modalHeading(fn(Patient $record): string => 'Expediente Médico de ' . $record->name . ' ' . $record->lastname)
                        ->modalWidth('2xl')
                        ->mountUsing(fn(Schema $schema, Patient $record) => $schema->fill(
                            $record->medicalRecord?->toArray() ?? []
                        ))
                        ->schema([
                            Section::make()
                                ->schema([
                                    TextInput::make('blood_type')
                                        ->label('Tipo de sangre')
                                        ->placeholder('Ej: O+')
                                        ->maxLength(3),

                                    TextInput::make('allergies')
                                        ->label('Alergias')
                                        ->placeholder('Ninguna')
                                        ->maxLength(255),

                                    Textarea::make('chronic_diseases')
                                        ->label('Enfermedades crónicas')
                                        ->rows(3)
                                        ->maxLength(255)
                                        ->columnSpanFull(),

                                    Textarea::make('medications')
                                        ->label('Medicamentos actuales')
                                        ->rows(3)
                                        ->maxLength(255)
                                        ->columnSpanFull(),

                                    Textarea::make('family_history')
                                        ->label('Antecedentes familiares')
                                        ->rows(3)
                                        ->maxLength(255)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ])
                        ->action(function (Patient $record, array $data) {
                            $record->medicalRecord()->updateOrCreate(
                                ['patient_id' => $record->id],
                                [
                                    'blood_type' => $data['blood_type'],
                                    'allergies' => $data['allergies'],
                                    'chronic_diseases' => $data['chronic_diseases'],
                                    'medications' => $data['medications'],
                                    'family_history' => $data['family_history'],
                                ]
                            );

                            Notification::make()
                                ->title('Historial actualizado')
                                ->success()
                                ->send();
                        }),

                    EditAction::make(),
                    DeleteAction::make()
                        ->modalHeading('Confirmar eliminación')
                        ->modalDescription(fn($record) => "¿Estás seguro de que deseas eliminar a {$record->name} {$record->lastname}? Esta acción se puede revertir desde la papelera de reciclaje."),
                    RestoreAction::make()
                        ->modalHeading("Confirmar restauración")
                        ->modalDescription(fn($record) => "¿Estás seguro de que deseas restaurar a {$record->name} {$record->lastname}?"),
                    ])
                        ->label('')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->button(),
            ]);
    }
}
