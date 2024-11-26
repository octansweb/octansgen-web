<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}

        <br>
        <x-filament::button type="submit">Submit</x-filament::button>
    </form>

    <x-filament-actions::modals />

</x-filament-panels::page>
