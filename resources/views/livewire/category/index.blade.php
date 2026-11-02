<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Categories</h2>
            <p class="text-secondary mb-0">Create and manage your categories.</p>
        </div>
        @can('categories.create')
            
        <a href="{{ route('categories.create') }}"
            class="btn btn-primary">
                Create Category
        </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <input type="search" class="form-control mb-3" placeholder="Search categories..." wire:model.live.debounce.300ms="search">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($categories as $category)
                            <tr wire:key="category-{{ $category->id }}">
                                <td class="fw-semibold">{{ $category->name }}</td>
                                <td>{{ $category->description }}</td>
                                <td class="text-end text-nowrap">
                                    @can('categories.edit')
                                    <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-warning btn-sm">
                                        Edit
                                    </a>
                                    @endcan
                                    @can('categories.delete')
                                    <button class="btn btn-sm btn-outline-danger" wire:click="delete({{ $category->id }})" wire:confirm="Are you sure you want to delete this category?">
                                        Delete
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    No categories found. Create your first category.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>
</div>