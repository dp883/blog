<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Roles</h2>
            <p class="text-secondary mb-0">Create and manage your roles.</p>
        </div>
        @can('roles.create')
            
        <a href="{{ route('roles.create') }}"
            class="btn btn-primary">
                Create Role
        </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <input type="search" class="form-control mb-3" placeholder="Search roles..." wire:model.live.debounce.300ms="search">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
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
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-warning btn-sm">
                                        Edit
                                    </a>
                                    @endcan
                                    @can('roles.delete')
                                    <button class="btn btn-sm btn-outline-danger" wire:click="delete({{ $role->id }})" wire:confirm="Are you sure you want to delete this role?">
                                        Delete
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    No roles found. Create your first role.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $roles->links() }}
                </div>
            </div>
        </div>
    </div>
</div>