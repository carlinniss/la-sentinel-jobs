<?php

declare(strict_types=1);

namespace Modules\User\Filament\Admin\Resources;

use A909M\FilamentStateFusion\Tables\Columns\StateFusionSelectColumn;
use A909M\FilamentStateFusion\Tables\Filters\StateFusionSelectFilter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Admin\Support\Filament\ResourceTableActions;
use Modules\Admin\Support\Filament\ResourceTableColumns;
use Modules\User\App\Models\User;
use Modules\User\App\Support\Filament\UserFormFields;
use Modules\User\Filament\Admin\Resources\UserResource\Pages;
use STS\FilamentImpersonate\Actions\Impersonate;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'User Management';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            UserFormFields::name(),
            UserFormFields::email(),
            UserFormFields::password(fn ($livewire) => $livewire instanceof Pages\CreateUser),
            UserFormFields::status(),
            UserFormFields::roles(),
            Group::make()
                ->relationship('profile')
                ->schema([
                    Toggle::make('is_verified')
                        ->label('Verified employer'),
                    Toggle::make('resume_access_enabled')
                        ->label('Resume bank access')
                        ->helperText('Grant only to approved employer accounts. This can represent a paid employer entitlement.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ResourceTableColumns::id(),
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('email')->searchable()->sortable(),
            TextColumn::make('roles.name')->badge()->label('Roles'),
            IconColumn::make('profile.resume_access_enabled')->boolean()->label('Resume access'),
            StateFusionSelectColumn::make('status'),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->defaultSort('id', 'desc')->filters([
            StateFusionSelectFilter::make('status'),
        ])->actions(ResourceTableActions::editActivityDelete(static::class, [
            Impersonate::make(),
        ]));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'activities' => Pages\ListUserActivities::route('/{record}/activities'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
