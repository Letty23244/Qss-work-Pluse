<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use App\Models\AuditLog;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Audit Logs';
    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool { return false; }

    public static function form(Form $form): Form { return $form->schema([]); }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('user.name')->placeholder('System / API'),
            Tables\Columns\TextColumn::make('event')->badge()
                ->color(fn (string $state): string => ['created' => 'success', 'updated' => 'warning', 'deleted' => 'danger'][$state] ?? 'gray'),
            Tables\Columns\TextColumn::make('auditable_type')->label('Record')
                ->formatStateUsing(fn (string $state, AuditLog $record): string => class_basename($state).' #'.$record->auditable_id),
            Tables\Columns\TextColumn::make('old_values')->limit(50)->tooltip(fn (AuditLog $r) => json_encode($r->old_values)),
            Tables\Columns\TextColumn::make('new_values')->limit(50)->tooltip(fn (AuditLog $r) => json_encode($r->new_values)),
            Tables\Columns\TextColumn::make('ip_address')->toggleable(isToggledHiddenByDefault: true),
        ])->filters([Tables\Filters\SelectFilter::make('event')->options(['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted'])]);
    }

    public static function getPages(): array
    {
        return ['index' => AuditLogResource\Pages\ManageAuditLogs::route('/')];
    }
}
