<?php

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component {
    use Toast, WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public bool $myModal = false;

    public int $perPage = 5;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

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

    public function resetInput(): void
    {
        $this->reset(['id', 'name', 'email', 'password', 'role', 'role_selected_id', 'permissionsMultiSearchable', 'permission_multi_searchable_ids', 'nameRole', 'namePermission']);
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
            ->map(fn (int $id) => Permission::where('id', $id)->first())
            ->filter()
            ->values();

        $this->permissionsMultiSearchable = Permission::query()
            ->where('name', 'like', "%$value%")
            ->take(5)
            ->orderBy('name')
            ->get()
            ->merge($selectedOptions);
    }

    public function create(): void
    {
        $this->resetInput();
        $this->myModal = true;

        $this->roles = Role::all();
        $this->reset(['id', 'name', 'email', 'password', 'role']);
    }

    public function edit($id): void
    {
        $this->myModal = true;

        $user = User::find($id);

        $this->id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles->first()->id;
        $this->roles = Role::all();
        // dd($this->roles-);
    }

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    // Delete action
    public function delete($id): void
    {
        try {
            DB::beginTransaction();
            User::find($id)->delete();
            DB::commit();

            $this->success('User deleted.', position: 'toast-bottom');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->warning("Will delete #$id", $th->getMessage(), position: 'toast-bottom');
        }
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
            $this->warning("Will update role", $th->getMessage(), position: 'toast-bottom');
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
            $this->warning("Will create role", $th->getMessage(), position: 'toast-bottom');
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
            $this->warning("Will create permission", $th->getMessage(), position: 'toast-bottom');
        }
    }

    // Save action
    public function save(): void
    {
        if ($this->id != null) {
            $this->validate([
                'name' => 'required|string',
                'email' => 'required|unique:users,email,' . $this->id,
                'password' => 'nullable',
                'role' => 'required',
            ]);

            $user = User::find($this->id);
            try {
                DB::beginTransaction();
                $user->name = $this->name;
                $user->email = $this->email;
                $user->roles()->sync($this->role);
                if ($this->password) {
                    $user->password = Hash::make($this->password);
                }
                $user->save();
                DB::commit();

                $this->success('User updated.', position: 'toast-bottom');
                $this->myModal = false;
            } catch (\Throwable $th) {
                $this->warning("Will update #$id", $th->getMessage(), position: 'toast-bottom');
                DB::rollBack();
                $this->myModal = false;
            }
        } else {
            $this->validate([
                'name' => 'required|string',
                'email' => 'required|unique:users,email',
                'password' => 'required',
                'role' => 'required',
            ]);

            try {
                DB::beginTransaction();
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                ]);
                $user->roles()->sync($this->role);
                DB::commit();

                $this->success('User created.', position: 'toast-bottom');
                $this->myModal = false;
            } catch (\Throwable $th) {
                $this->warning('Will create user', $th->getMessage(), position: 'toast-bottom');
                DB::rollBack();
                $this->myModal = false;
            }
        }
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Nama', 'class' => 'w-64'],
            ['key' => 'roles.0.name', 'label' => 'Role', 'class' => 'w-40', 'sortable' => false],
            ['key' => 'email', 'label' => 'E-mail']];
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
            ->where('id', '!=', auth()->id())
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
        <x-table :headers="$headers" :rows="$users" :sort-by="$sortBy" per-page="perPage" :per-page-values="[5, 10, 50]"
            with-pagination>
            @scope('cell_id', $user)
                {{ ($this->users()->currentPage() - 1) * $this->perPage + $loop->iteration }}
            @endscope
            @scope('cell_roles.0.name', $user)
                <x-badge :value="$user['roles'][0]['name']"
                    class="{{ $user['roles'][0]['name'] == 'super-admin' ? 'badge-warning' : 'badge-primary' }}" />
            @endscope
            @scope('actions', $user)
                <div class="flex">
                    @can('user-edit')
                    <x-button icon="o-pencil" wire:click="edit({{ $user['id'] }})" class="btn-ghost btn-sm" />
                    @endcan
                    @can('user-delete')
                    <x-button icon="o-trash" wire:click="delete({{ $user['id'] }})" wire:confirm="Are you sure?" spinner
                    class="btn-ghost btn-sm text-red-500" />
                    @endcan
                </div>
            @endscope
        </x-table>
    </x-card>

    <!-- DRAWER -->
    @include('livewire.cms.users.drawer')



    {{-- modal --}}
    @include('livewire.cms.users.modal')
</div>
