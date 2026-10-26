
<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;

new #[Layout('layouts.admin')] class extends Component
{
    public string $pageTitle = 'Users';

    public ?int $userId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $selectedRole = '';
    public string $search = '';
    public bool $showForm = false;

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $user = User::with('roles')->findOrFail($id);

        $this->resetForm();

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;

        $this->selectedRole = (string) (
            $user->roles->first()?->id ?? ''
        );

        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],

            'password' => [
                $this->userId ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'selectedRole' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')
                    ->where('guard_name', 'web'),
            ],
        ]);

        $user = $this->userId
            ? User::findOrFail($this->userId)
            : new User();

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $role = Role::where('guard_name', 'web')
            ->findOrFail($validated['selectedRole']);

        $user->syncRoles([$role]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->resetForm();

        session()->flash(
            'success',
            'User saved and role assigned successfully.'
        );
    }

    public function delete(int $id): void
    {
        abort_unless(
            auth()->user()?->can('users.delete'),
            403
        );
        if ($id === Auth::id()) {
            session()->flash(
                'error',
                'You cannot delete your own account.'
            );

            return;
        }

        $user = User::findOrFail($id);

        $user->delete();

        session()->flash('success', 'User deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->reset(
            'userId',
            'name',
            'email',
            'password',
            'password_confirmation',
            'selectedRole',
            'showForm'
        );

        $this->resetValidation();
    }

    public function render()
    {
        return $this->view([
            'users' => User::with('roles')
                ->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->orderBy('name')
                ->get(),

            'roles' => Role::where('guard_name', 'web')
                ->orderBy('name')
                ->get(),
        ]);
    }
};
?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Users</h2>
            <p class="text-secondary mb-0">
                Manage users and assign roles.
            </p>
        </div>
        @can('users.create')
        <button type="button" class="btn btn-primary" wire:click="create">
            + Add User
        </button>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if ($showForm)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <h5 class="fw-bold mb-3">
                    {{ $userId ? 'Edit User' : 'Create User' }}
                </h5>

                <form wire:submit="save">

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>

                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="Enter full name">

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>

                        <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email" placeholder="user@example.com">

                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Password
                            @if ($userId)
                                <span class="text-secondary small">
                                    (Leave blank to keep current password)
                                </span>
                            @endif
                        </label>

                        <input type="password" class="form-control @error('password') is-invalid @enderror" wire:model="password" autocomplete="new-password" placeholder="{{ $userId ? 'Leave blank to keep current password' : 'Minimum 8 characters' }}">

                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>

                        <input type="password" class="form-control" wire:model="password_confirmation" autocomplete="new-password" placeholder="Confirm password">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Assign Role</label>

                        <select
                            class="form-select @error('selectedRole') is-invalid @enderror"
                            wire:model="selectedRole">
                            <option value="">-- Select Role --</option>

                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>

                        @error('selectedRole')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        @if ($roles->isEmpty())
                            <small class="text-danger">
                                No roles found. Create a role first.
                            </small>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">
                            {{ $userId ? 'Update User' : 'Create User' }}
                        </span>

                        <span wire:loading wire:target="save">
                            Saving...
                        </span>
                    </button>

                    <button type="button" class="btn btn-outline-secondary" wire:click="resetForm">
                        Cancel
                    </button>

                </form>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <input type="search" class="form-control mb-3" placeholder="Search users by name or email..." wire:model.live.debounce.300ms="search">

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($users as $user)
                            <tr wire:key="user-{{ $user->id }}">
                                <td>{{ $user->id }}</td>

                                <td>
                                    <span class="fw-semibold">
                                        {{ $user->name }}
                                    </span>

                                    @if ($user->id === Auth::id())
                                        <span class="badge text-bg-info">
                                            You
                                        </span>
                                    @endif
                                </td>

                                <td>{{ $user->email }}</td>

                                <td>
                                    @forelse ($user->roles as $role)
                                        <span class="badge text-bg-dark">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="badge text-bg-secondary">
                                            No role
                                        </span>
                                    @endforelse
                                </td>

                                <td class="text-end text-nowrap">
                                    @can('users.edit')
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="edit({{ $user->id }})">
                                        Edit
                                    </button>
                                    @endcan
                                    @can('users.delete')
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="delete({{ $user->id }})" wire:confirm="Are you sure you want to delete this user?">
                                        Delete
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
