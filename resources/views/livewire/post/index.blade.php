<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Posts</h2>
            <p class="text-secondary mb-0">Create and manage your blog articles.</p>
        </div>
        @can('posts.create')
            
        <a href="{{ route('posts.create') }}"
            class="btn btn-primary">
                Create Post
        </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <input type="search" class="form-control mb-3" placeholder="Search posts..." wire:model.live.debounce.300ms="search">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Tags</th>
                            <th>Status</th>
                            <th>Author</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($posts as $post)
                            <tr wire:key="post-{{ $post->id }}">
                                <td class="fw-semibold">{{ $post->title }}</td>
                                <td>{{ $post->category?->name ?? '—' }}</td>

                                <td>
                                    @forelse ($post->tags as $tag)
                                        <span class="badge text-bg-light border">
                                            {{ $tag->name }}
                                        </span>
                                    @empty
                                        <span class="text-secondary">—</span>
                                    @endforelse
                                </td>

                                <td>
                                    <span wire:click="updatestatus({{$post->id}})" wire:target="updatestatus({{$post->id}})" wire:loading.class="opacity-50" class="badge cursor-pointer {{ $post->status === 'published' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($post->status) }}
                                    </span>
                                </td>

                                <td>{{ $post->user?->name ?? '—' }}</td>

                                <td class="text-end text-nowrap">
                                    <div x-data="{
                                            canEdit: @js(auth()->user()->can('posts.edit')),
                                            canDelete: @js(auth()->user()->can('posts.delete'))
                                        }">
                                        <template x-if="canEdit">
                                            <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-warning btn-sm">
                                                Edit
                                            </a>
                                        </template>
                                        <template x-if="canDelete">
                                            <button type="button" class="btn btn-sm btn-outline-danger" wire:click="delete({{ $post->id }})" wire:confirm="Are you sure you want to delete this post?">
                                                Delete
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    No posts found. Create your first post.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>