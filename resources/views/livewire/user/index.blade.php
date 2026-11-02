<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Users</h2>
            <p class="text-secondary mb-0">Create and manage your users.</p>
        </div>
        @can('users.create')
            
        <a href="{{ route('users.create') }}"
            class="btn btn-primary">
                Create User
        </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <input type="search" class="form-control mb-3" placeholder="Search users..." wire:model.live.debounce.300ms="search">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($users as $user)
                            <tr wire:key="user-{{ $user->id }}">
                                <td class="fw-semibold">
                                    {{ $user->name }}
                                    @if ($user->id === Auth::id())
                                        <span class="badge text-bg-info">
                                            You
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->roles->isNotEmpty())
                                        <span class="badge text-bg-dark">
                                            {{ $user->roles->pluck('name')->join(', ') }}
                                        </span>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @can('users.edit')
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">
                                        Edit
                                    </a>
                                    @endcan
                                    @can('users.delete')
                                    <button class="btn btn-sm btn-outline-danger" wire:click="delete({{ $user->id }})" wire:confirm="Are you sure you want to delete this user?">
                                        Delete
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    No users found. Create your first user.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>