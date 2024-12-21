<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
     <!-- HEADER -->
     <x-header title="Dashboard" separator progress-indicator>
        {{-- <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
        </x-slot:actions> --}}
    </x-header>

    <x-card>
        Ini Dashboard
    </x-card>
</div>
