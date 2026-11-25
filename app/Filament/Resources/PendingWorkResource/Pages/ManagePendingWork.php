<?php

namespace App\Filament\Resources\PendingWorkResource\Pages;

use App\Filament\Resources\PendingWorkResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePendingWork extends ManageRecords
{
    protected static string $resource = PendingWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
