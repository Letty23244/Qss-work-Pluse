<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use App\Models\ProductionRecord;
use Illuminate\Database\Eloquent\Builder;

class PendingWorkResource extends Resource
{
    protected static ?string $model = ProductionRecord::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationLabel = 'Pending Work';
    protected static ?string $modelLabel = 'pending work';
    protected static ?string $slug = 'pending-work';
    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('quantity_pending', '>', 0)->where('status', '!=', 'rejected');
    }

    public static function canCreate(): bool { return false; }

    public static function form(Form $form): Form { return $form->schema([]); }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('date', 'desc')->columns([
            Tables\Columns\TextColumn::make('date')->date()->sortable(),
            Tables\Columns\TextColumn::make('client.name')->searchable(),
            Tables\Columns\TextColumn::make('team.name'),
            Tables\Columns\TextColumn::make('assignment.title')->label('Assignment'),
            Tables\Columns\TextColumn::make('shift_type')->badge(),
            Tables\Columns\TextColumn::make('quantity_pending')->numeric(2)->summarize(Tables\Columns\Summarizers\Sum::make()),
            Tables\Columns\TextColumn::make('remarks')->limit(60),
        ])->filters([Tables\Filters\SelectFilter::make('client_id')->relationship('client', 'name')->label('Client')]);
    }

    public static function getPages(): array
    {
        return ['index' => PendingWorkResource\Pages\ManagePendingWork::route('/')];
    }
}
