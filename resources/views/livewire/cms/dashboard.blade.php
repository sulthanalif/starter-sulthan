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

    <div class="py-4 bg-base-100 rounded-b-xl bg-base-200 grid md:grid-cols-4 gap-5">
        {{-- <x-stat title="Messages" value="44" icon="o-envelope" tooltip="Hello" />

        <x-stat title="Sales" description="This month" value="22.124" icon="o-arrow-trending-up" tooltip-bottom="There" />

        <x-stat title="Lost" description="This month" value="34" icon="o-arrow-trending-down"
            tooltip-left="Ops!" />

        <x-stat title="Sales" description="This month" value="22.124" icon="o-arrow-trending-down"
            class="text-orange-500" color="text-pink-500" tooltip-right="Gosh!" /> --}}

    </div>
    <x-card>
        <div class="text-center">
            <div class="flex justify-center">
                <x-app-brand class=" px-5 pt-5" />
            </div>
            <p class="mt-4">Selamat Datang</p>
            <p class="">{{ auth()->user()->name }}</p>
        </div>
    </x-card>
</div>
