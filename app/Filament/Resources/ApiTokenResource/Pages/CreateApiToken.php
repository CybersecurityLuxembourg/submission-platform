<?php

namespace App\Filament\Resources\ApiTokenResource\Pages;

use App\Filament\Resources\ApiTokenResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateApiToken extends CreateRecord
{
    protected static string $resource = ApiTokenResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate a secure token
        $plainTextToken = Str::random(40);
        
        // Store hashed token in the database
        $data['token'] = hash('sha256', $plainTextToken);
        
        // Set user ID
        $data['user_id'] = auth()->id();
        
        // Store default abilities if none provided
        if (!isset($data['abilities'])) {
            $data['abilities'] = ['forms:read'];
        }
        
        // Store plain text token temporarily to show to the user
        $this->record = ['plain_text_token' => $plainTextToken];
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        // Store token in session for display on the form
        $record = $this->record;
        $record->plain_text_token = $this->record['plain_text_token'];
        $this->record = $record;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
