
<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

new #[Layout('layouts.admin')] class extends Component
{
    public string $pageTitle = 'Roles';

    public ?int $roleId = null;
    public string $name = '';
    public array $selectedPermissions = [];
    public string $search = '';
    public bool $showForm = false;
    public bool $selectAll = false;

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedPermissions = Permission::where('guard_name', 'web')
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedPermissions = [];
        }
    }

    public function updatedSelectedPermissions(): void
    {
        $allPermissionIds = Permission::where('guard_name', 'web')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        $this->selectAll = count($allPermissionIds) > 0
            && empty(array_diff(
                $allPermissionIds,
                array_map('strval', $this->selectedPermissions)
            ));
    }

    public function edit(int $id): void
    {
        $role = Role::with('permissions')->findOrFail($id);

        $this->resetForm();

        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $allPermissionIds = Permission::where('guard_name', 'web')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        $this->selectAll = count($allPermissionIds) > 0
            && empty(array_diff(
                $allPermissionIds,
                $this->selectedPermissions
            ));

        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->ignore($this->roleId),
            ],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')
                    ->where('guard_name', 'web'),
            ],
        ]);

        $role = $this->roleId
            ? Role::findOrFail($this->roleId)
            : new Role();

        $role->name = $validated['name'];
        $role->guard_name = 'web';
        $role->save();

        $permissions = Permission::whereIn(
            'id',
            $validated['selectedPermissions'] ?? []
        )->where('guard_name', 'web')->get();

        $role->syncPermissions($permissions);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->resetForm();

        session()->flash('success', 'Role saved successfully.');
    }

    public function delete(int $id): void
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'admin') {
            session()->flash('error', 'The admin role cannot be deleted.');
            return;
        }

        if ($role->users()->exists()) {
            session()->flash(
                'error',
                'This role is assigned to users. Reassign those users before deleting it.'
            );
            return;
        }

        $role->delete();

        session()->flash('success', 'Role deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->reset(
            'roleId',
            'name',
            'selectedPermissions',
            'showForm',
            'selectAll'
        );

        $this->resetValidation();
    }

    public function render()
    {
        return $this->view([
            'roles' => Role::with('permissions')
                ->where('guard_name', 'web')
                ->where('name', 'like', '%' . $this->search . '%')
                ->orderBy('name')
                ->get(),

            'permissions' => Permission::where('guard_name', 'web')
                ->orderBy('name')
                ->get(),
        ]);
    }
};
?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Roles</h2>
            <p class="text-secondary mb-0">
                Manage roles and assign permissions.
            </p>
        </div>
        @can('roles.create')
        <button class="btn btn-primary" wire:click="create">
            + Add Role
        </button>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($showForm)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">
                    {{ $roleId ? 'Edit Role' : 'Create Role' }}
                </h5>

                <form wire:submit="save">
                    <div class="mb-3">
                        <label class="form-label">Role Name</label>

                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="e.g. editor">

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <label class="form-label fw-semibold">Permissions</label>
                    <div class="row mb-3">
                        <label class="ml-3 form-check">
                            <input class="form-check-input"  type="checkbox" wire:model.live="selectAll" >
                            <span class="form-check-label">
                                Select All
                            </span>
                        </label>
                    </div>
                    <div class="row mb-3">
                        @forelse ($permissions as $permission)
                            <div class="col-md-4 mb-2"
                                 wire:key="permission-{{ $permission->id }}">
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" value="{{ $permission->id }}" wire:model.live="selectedPermissions">

                                    <span class="form-check-label">
                                        {{ $permission->name }}
                                    </span>
                                </label>
                            </div>
                        @empty
                            <p class="text-secondary">
                                Create permissions first.
                            </p>
                        @endforelse
                    </div>

                    @error('selectedPermissions.*')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror

                    <button class="btn btn-primary" type="submit">
                        Save Role
                    </button>

                     <button class= "btn btn-outline-secondary"type="button"wire:click="resetForm">
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <input class=" form -control mb-3"placeholder="Search roles..."wire:model.live.debounce.300ms="search">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Role</th>
                            <th>Permissions</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($roles as $role)
                            <tr wire:key="role-{{ $role->id }}">
                                <td>
                                    <span class="badge text-bg-dark">
                                        {{ $role->name }}
                                    </span>
                                </td>

                                <td>
                                    @forelse ($role->permissions as $permission)
                                        <span class="badge text-bg-light border mb-1">
                                            {{ $permission->name }}
                                        </span>
                                    @empty
                                        <span class="text-secondary">None</span>
                                    @endforelse
                                </td>

                                <td class="text-end text-nowrap">
                                    @can('roles.edit')
                                    <button class="btn btn-sm btn-outline-primary" wire:click="edit({{ $role->id }})">
                                        Edit
                                    </button>
                                    @endcan
                                    @can('roles.delete')
                                    <button class="btn btn-sm btn-outline-danger" wire:click="delete({{ $role->id }})" wire:confirm="Delete this role?">
                                        Delete
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    No roles found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>