<x-drawer wire:model="drawer" title="Pengaturan" right separator with-close-button class="lg:w-1/3">
    <x-card title="Pengaturan Akses">
        <x-form wire:submit="saveOption" class="relative" no-separator>
            <x-select label="Role" icon="o-user" :options="$roles" wire:change="selectedRole($event.target.value)"
                wire:model="role_selected_id" placeholder="Select Role" placeholder-value="0" inline />
            {{-- <div class="my-2"></div> --}}
            <x-choices label="Permission" wire:model="permission_multi_searchable_ids" :options="$permissionsMultiSearchable"
                placeholder="Pilih ..." search-function="searchMulti" no-result-text="Ops! Nothing here ..." searchable
                inline />

            <x-slot:actions>
                <x-button label="Save" icon="o-check" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-card>

    <x-card title="Tambah Role">
        <x-form wire:submit="saveRole" class="relative" no-separator>
            <x-input label="Nama Role" icon="o-user" type="text" wire:model="nameRole" inline />

            <x-slot:actions>
                <x-button label="Save" icon="o-check" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-card>

    <x-card title="Tambah Permission">
        <x-form wire:submit="savePermission" class="relative" no-separator>
            <x-input label="Nama Permission" icon="o-user" type="text" wire:model="namePermission" inline />

            <x-slot:actions>
                <x-button label="Save" icon="o-check" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-card>


</x-drawer>
