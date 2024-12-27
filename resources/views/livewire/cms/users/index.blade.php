<?php

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Traits\Traits\HandlesPage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use App\Traits\Traits\HandlesSaveOrUpdate;
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component {
    use Toast, WithPagination, HandlesPage, HandlesSaveOrUpdate;

    //var user
    public $id;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';
    public $roles = [];

    //option
    public string $nameRole;
    public string $namePermission;
    public $role_selected_id;
    public Collection|array $permissionsMultiSearchable = [];
    public array $permission_multi_searchable_ids = [];

    public function mount(): void
    {
        $this->setModel(new User());
        $this->setInput(['id', 'name', 'email', 'password', 'role', 'role_selected_id', 'permissionsMultiSearchable', 'permission_multi_searchable_ids', 'nameRole', 'namePermission']);
        $this->resetInput();
        $this->roles = Role::all();
    }

    public function selectedRole($id): void
    {
        if ($id != 0) {
            $role = Role::find($id);
            $this->permission_multi_searchable_ids = $role->permissions->pluck('id')->toArray();
            $this->permissionsMultiSearchable = Permission::orderBy('name')->get();
        } else {
            $this->permission_multi_searchable_ids = [];
            $this->permissionsMultiSearchable = [];
        }
    }

    public function option(): void
    {
        $this->resetInput();
        $this->drawer = true;

        $this->roles = Role::all();
    }

    public function searchMulti(string $value): void
    {
        $selectedOptions = collect($this->permission_multi_searchable_ids)
            ->map(fn(int $id) => Permission::where('id', $id)->first())
            ->filter()
            ->values();

        $this->permissionsMultiSearchable = Permission::query()
            ->where('name', 'like', "%$value%")
            ->take(5)
            ->orderBy('name')
            ->get()
            ->merge($selectedOptions);
    }

    //save option
    public function saveOption(): void
    {
        $this->validate([
            'role_selected_id' => 'required',
            'permission_multi_searchable_ids' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $role = Role::find($this->role_selected_id);
            $role->syncPermissions($this->permission_multi_searchable_ids);
            DB::commit();
            $this->success('Role updated.', position: 'toast-bottom');
            $this->drawer = false;
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->warning('Will update role', $th->getMessage(), position: 'toast-bottom');
            $this->drawer = false;
        }
    }

    //save role
    public function saveRole(): void
    {
        $this->validate([
            'nameRole' => 'required|string',
        ]);

        try {
            DB::beginTransaction();
            Role::create(['name' => $this->nameRole]);
            DB::commit();
            $this->success('Role created.', position: 'toast-bottom');
            // $this->drawer = false;
            $this->resetInput();
            $this->roles = Role::all();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->resetInput();
            $this->warning('Will create role', $th->getMessage(), position: 'toast-bottom');
        }
    }

    public function savePermission(): void
    {
        $this->validate([
            'namePermission' => 'required|string',
        ]);

        try {
            DB::beginTransaction();
            Permission::create(['name' => $this->namePermission]);
            DB::commit();
            $this->success('Permission created.', position: 'toast-bottom');
            $this->resetInput();
            $this->permissions = Permission::all();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->resetInput();
            $this->warning('Will create permission', $th->getMessage(), position: 'toast-bottom');
        }
    }

    // Save action
    public function save(): void
    {
        $this->saveOrUpdate(
            validationRules: [
                'name' => 'required|string',
                'email' => 'required|unique:users,email,' . $this->recordId,
                'password' => $this->recordId ? 'nullable' : 'required',
                'role' => 'required',
            ],
            beforeSave: function ($user, $component) {
                if ($component->password) {
                    $user->password = Hash::make($component->password);
                }
            },
            afterSave: function ($user, $component) {
                $user->roles()->sync($component->role);
            },
        );
    }

    // Table headers
    public function headers(): array
    {
        return [
            // ['key' => 'id', 'label' => `<x-checkbox wire:model.live="checkbox" />`, 'class' => 'w-1', 'sortable' => false],
            ['key' => 'name', 'label' => 'Nama', 'class' => 'w-64'],
            ['key' => 'roles.0.name', 'label' => 'Role', 'class' => 'w-40', 'sortable' => false],
            ['key' => 'email', 'label' => 'E-mail']
        ];
    }

    /**
     * For demo purpose, this is a static collection.
     *
     * On real projects you do it with Eloquent collections.
     * Please, refer to maryUI docs to see the eloquent examples.
     */
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->with('roles')
            ->where(function ($query) {
                $query
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhereHas('roles', function ($query) {
                        $query->where('name', 'like', "%{$this->search}%");
                    });
            })
            ->where('id', '!=', Auth::id())
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function with(): array
    {
        return [
            'users' => $this->users(),
            'headers' => $this->headers(),
        ];
    }

}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Data User" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            @can('user-create')
                <x-button label="Tambah" @click="$wire.create" responsive icon="o-plus" />
            @endcan
            @can('option-role')
                <x-button label="Pengaturan" @click="$wire.option" responsive icon="o-cog-6-tooth" />
            @endcan
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table
            :headers="$headers"
            :rows="$users"
            :sort-by="$sortBy"
            per-page="perPage"
            :per-page-values="[5, 10, 50]"
            wire:model.live="selected"
            selectable
            with-pagination>

                @scope('cell_roles.0.name', $user)
                    <x-badge :value="$user['roles'][0]['name']"
                        class="{{ $user['roles'][0]['name'] == 'super-admin' ? 'badge-warning' : 'badge-primary' }}" />
                @endscope
                @scope('actions', $user)
                    <div class="flex">
                        @can('user-edit')
                            <x-button icon="o-pencil" wire:click="edit({{ $user['id'] }})" class="btn-ghost btn-sm" />
                        @endcan
                    </div>
                @endscope
            </x-table>
            @can('user-delete')
                @if ($this->selected)
                <div class="mt-2">
                    <x-button label="Hapus" icon="o-trash" wire:click="delete" spinner class="btn-ghost  text-red-500" wire:confirm="Are you sure?" wire:loading.attr="disabled" />
                </div>
                @endif
            @endcan
    </x-card>

    <!-- DRAWER -->
    @include('livewire.cms.users.drawer')

    {{-- modal --}}
    @include('livewire.cms.users.modal')
</div>
