<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use App\Models\Shift;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('client_id')->relationship('client', 'name')->required()->searchable()->preload(),
            Forms\Components\Select::make('team_id')->relationship('team', 'name')->required()->searchable()->preload(),
            Forms\Components\Select::make('supervisor_id')->label('Supervisor')
                ->relationship('supervisor', 'name', fn ($query) => $query->where('role', 'supervisor'))->required()->searchable()->preload(),
            Forms\Components\DatePicker::make('date')->required()->default(today()),
            Forms\Components\Select::make('shift_type')->options(Shift::TYPES)->required(),
            Forms\Components\TimePicker::make('start_time')->required()->seconds(false),
            Forms\Components\TimePicker::make('end_time')->required()->seconds(false),
            Forms\Components\Select::make('status')->options(Shift::STATUSES)->default('scheduled')->required(),
            Forms\Components\TextInput::make('workers_deployed')->numeric()->minValue(0)->default(0)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('date', 'desc')->columns([
            Tables\Columns\TextColumn::make('date')->date()->sortable(),
            Tables\Columns\TextColumn::make('shift_type')->badge()->formatStateUsing(fn (string $state): string => Shift::TYPES[$state] ?? $state),
            Tables\Columns\TextColumn::make('client.name')->searchable(),
            Tables\Columns\TextColumn::make('team.name'),
            Tables\Columns\TextColumn::make('supervisor.name'),
            Tables\Columns\TextColumn::make('start_time'),
            Tables\Columns\TextColumn::make('end_time'),
            Tables\Columns\TextColumn::make('workers_deployed')->label('Workers'),
            Tables\Columns\TextColumn::make('status')->badge()
                ->color(fn (string $state): string => ['scheduled' => 'gray', 'active' => 'warning', 'completed' => 'success'][$state] ?? 'gray'),
        ])->filters([
            Tables\Filters\SelectFilter::make('status')->options(Shift::STATUSES),
            Tables\Filters\SelectFilter::make('shift_type')->options(Shift::TYPES),
            Tables\Filters\SelectFilter::make('client_id')->relationship('client', 'name')->label('Client'),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ShiftResource\Pages\ManageShifts::route('/')];
    }
}
