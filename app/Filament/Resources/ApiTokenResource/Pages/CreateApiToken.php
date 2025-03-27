<?php

namespace App\Filament\Resources\ApiTokenResource\Pages;

use App\Filament\Resources\ApiTokenResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateApiToken extends CreateRecord
{
    protected static string $resource = ApiTokenResource::class;
    
    // Store the plain text token temporarily
    public $plainTextToken;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate a secure token
        $this->plainTextToken = Str::random(40);
        
        // Store hashed token in the database
        $data['token'] = hash('sha256', $this->plainTextToken);
        
        // Set user ID
        $data['user_id'] = auth()->id();
        
        // Store default abilities if none provided
        if (!isset($data['abilities'])) {
            $data['abilities'] = ['forms:read'];
        }
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        // Attach the plain text token to the model for display purposes
        $this->record->plain_text_token = $this->plainTextToken;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
