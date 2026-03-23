<?php

namespace App\Filament\Resources\Doctors\Schemas;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DoctorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre completo')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->email()
                            ->placeholder('ejemplo@correo.com')
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->dehydrated(fn($state) => filled($state))
                            ->helperText(
                                fn(string $operation): string =>
                                $operation === 'edit'
                                    ? 'Por seguridad, la contraseña actual no se muestra. Puedes dejarla en blanco si no deseas cambiarla.'
                                    : 'Asigna una contraseña inicial segura para el nuevo doctor.'
                            ),

                        Toggle::make('is_active')
                            ->label('Usuario activo')
                            ->default(true)
                            ->inline(false),

                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->placeholder('7000-7000')
                            ->maxLength(9),

                        TextInput::make('specialty')
                            ->label('Especialidad')
                            ->placeholder('Ej: Cardiología')
                            ->required()
                            ->maxLength(50),
                    ]),
            ]);
    }
}
