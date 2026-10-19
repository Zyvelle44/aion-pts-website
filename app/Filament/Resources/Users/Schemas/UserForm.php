<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->minLength(8)
                            ->maxLength(255)
                            ->helperText('Leave blank when editing to keep the current password.'),

                        CheckboxList::make('roles')
                            ->label('Assigned Roles')
                            ->relationship('roles', 'name')
                            ->options(function (): array {
                                if (auth()->user()?->hasRole('super_admin')) {
                                    return \Spatie\Permission\Models\Role::query()
                                        ->where('guard_name', 'web')
                                        ->pluck('name', 'id')
                                        ->all();
                                }

                                return [];
                            })
                            ->columns(3)
                            ->bulkToggleable()
                            ->helperText('Assign only the roles this user actually needs.')
                            ->visible(
                                fn(): bool =>
                                auth()->user()?->hasRole('super_admin') ?? false
                            ),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
