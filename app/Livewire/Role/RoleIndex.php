<?php

namespace App\Livewire\Role;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

use App\Models\Tag;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

#[Layout('layouts.admin')]
class RoleIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public string $pageTitle = 'Roles';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->can('roles.delete'), 403);

        $role = Role::findOrFail($id);

        $role->delete();

        session()->flash('success', 'Role deleted successfully.');
    }

    public function render()
    {
        $user = auth()->user();

        $roles = Role::where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.role.index', [
            'roles' => $roles,
        ]);
    }
}