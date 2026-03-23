<?php

namespace App\Filament\Resources\Patients\Schemas;

use App\Models\Patient;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;


class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->maxLength(100)
                    ->required(),
                TextInput::make('lastname')
                    ->label('Apellido')
                    ->maxLength(100)
                    ->required(),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->maxLength(100)
                    ->required()
                    ->unique(table: Patient::class, column: 'email', ignoreRecord: true),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->maxLength(9)
                    ->placeholder('7000-7000')
                    ->tel(),
                DatePicker::make('birth_date')
                    ->label('Fecha de nacimiento')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->maxDate(now()),
                Select::make('gender')
                    ->label('Género')
                    ->options([
                        'M' => 'Masculino',
                        'F' => 'Femenino',
                    ])
                    ->required()
                    ->native(false),
            ]);
    }
}
