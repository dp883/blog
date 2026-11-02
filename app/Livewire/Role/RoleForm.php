<?php

namespace App\Livewire\Role;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

use App\Models\Category;
use App\Models\Tag;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('layouts.admin')]
class RoleForm extends Component
{
    use WithFileUploads;

    public string $pageTitle = 'Create Role';

    public ?int $roleId = null;

    public string $name = '';
    public string $slug = '';

    public $permissions = [];

    public $selectedPermissions = [];

    public function selectAllPermissions(): void
    {
        $this->selectedPermissions = Permission::where('guard_name', 'web')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    public function deselectAllPermissions(): void
    {
        $this->selectedPermissions = [];
    }
    public function mount(?int $roleId = null): void
    {
        if ($roleId) {
            $this->roleId = $roleId;

            $role = Role::findOrFail($roleId);

            $this->pageTitle = 'Edit Role';

            $this->name = $role->name;

            $this->selectedPermissions = $role->permissions
                                ->pluck('id')
                                ->map(fn ($id) => (string) $id)
                                ->toArray();
        } else {
            abort_unless(
                auth()->user()->can('roles.create'),
                403
            );
        }
    }

    public function updatedName(): void
    {
        if (!$this->roleId) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function save()
    {
        if ($this->roleId) {
            abort_unless(
                auth()->user()->can('roles.edit'),
                403
            );
        } else {
            abort_unless(
                auth()->user()->can('roles.create'),
                403
            );
        }
    
        $validated = $this->validate([
            'name' => ['required','string','max:255',
                Rule::unique('roles', 'name')->ignore($this->roleId),
            ],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')
                    ->where('guard_name', 'web'),
            ],
        ]);

        $role = $this->roleId ? Role::findOrFail($this->roleId) : new Role();

        $role->name = $validated['name'];
        $role->guard_name = 'web';
        $role->save();
    
        $permission = Permission::whereIn(
            'id',
            $validated['selectedPermissions'] ?? []
        )->where('guard_name', 'web')->get();

        $role->syncPermissions($permission);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $message = $this->roleId ? 'Role updated successfully.' : 'Role created successfully.';
        $this->resetForm();
        session()->flash('success', $message);
        return $this->redirectRoute('roles.index');
    }

    public function resetForm(): void
    {
        $this->reset(
            'roleId',
            'name',
            'selectedPermissions',
        );

        $this->resetValidation();
    }
    public function render()
    {
        return view('livewire.role.form',[
            'allpermissions' => Permission::where('guard_name', 'web')->get(),
        ]);
    }
}