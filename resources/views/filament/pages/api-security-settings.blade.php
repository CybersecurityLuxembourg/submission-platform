<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}
        
        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </form>
    
    <x-filament-actions::modals />
</x-filament-panels::page>
