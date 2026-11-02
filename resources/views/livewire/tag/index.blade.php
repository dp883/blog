<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Tags</h2>
            <p class="text-secondary mb-0">Create and manage your tags.</p>
        </div>
        @can('tags.create')
            
        <a href="{{ route('tags.create') }}"
            class="btn btn-primary">
                Create Tag
        </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <input type="search" class="form-control mb-3" placeholder="Search tags..." wire:model.live.debounce.300ms="search">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Posts</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($tags as $tag)
                            <tr wire:key="tag-{{ $tag->id }}">
                                <td class="fw-semibold">{{ $tag->name }}</td>
                                <td>{{ $tag->posts_count }}</td>
                                <td class="text-end text-nowrap">
                                    @can('tags.edit')
                                    <a href="{{ route('tags.edit', $tag->id) }}" class="btn btn-warning btn-sm">
                                        Edit
                                    </a>
                                    @endcan
                                    @can('tags.delete')
                                    <button class="btn btn-sm btn-outline-danger" wire:click="delete({{ $tag->id }})" wire:confirm="Are you sure you want to delete this tag?">
                                        Delete
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    No tags found. Create your first tag.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $tags->links() }}
                </div>
            </div>
        </div>
    </div>
</div>