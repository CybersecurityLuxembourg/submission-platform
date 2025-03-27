<?php

namespace App\Filament\Resources\ApiLogResource\Pages;

use App\Filament\Resources\ApiLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateApiLog extends CreateRecord
{
    protected static string $resource = ApiLogResource::class;
}
