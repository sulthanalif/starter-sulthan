{{--  modal --}}
<x-modal wire:model="myModal" class="backdrop-blur">
    <x-form wire:submit="save" class="relative" no-separator>
        <div class="">
            <h1>{{ $this->id == null ? 'Create User' : 'Update User' }}</h1>
        </div>
    <div class="mb-5 rounded-lg p-6">
            <x-input hidden wire:model="id" />
            <x-input label="Name" icon="o-user" type="text" wire:model="name" inline autofocus />
            <div class="my-3"></div>
            <x-input label="Email" icon="o-envelope" type="email" wire:model="email" inline />
            <div class="my-3"></div>
            <x-password label="Password"  type="password" wire:model="password" inline hint="{{ $this->id == '' ? '' : 'Isi password jika ingin diubah' }}" />
            <div class="my-3"></div>
            <x-select label="Role" icon="o-user" :options="$roles" wire:model="role" placeholder="Select Role" placeholder-value="0" inline />
        </div>
        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.myModal = false" />
            <x-button label="Save" class="btn-primary" type="submit" spinner="save" />
        </x-slot:actions>
    </x-form>
</x-modal>
