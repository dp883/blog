<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new #[Layout('layouts.admin')] class extends Component
{
    public string $pageTitle = 'Posts';

    use WithFileUploads;

    public $image = null;
    public ?int $postId = null;
    public string $title = '';
    public string $slug = '';
    public string $content = '';
    public string $status = 'draft';
    public ?int $category_id = null;
    public array $selectedTags = [];
    public string $search = '';
    public bool $showForm = false;
    public ?string $existingImage = null;
    public bool $removeCurrentImage = false;

    public function updatedTitle(): void
    {
        if (!$this->postId) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $post = Post::with('tags')->findOrFail($id);

        $this->postId = $post->id;
        $this->title = $post->title;
        $this->slug = $post->slug;
        $this->content = $post->content;
        $this->status = $post->status;
        $this->category_id = $post->category_id;
        $this->selectedTags = $post->tags->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
        $this->existingImage = $post->image;

        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 
                'max:255',
                Rule::unique('posts', 'title')->ignore($this->postId),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('posts', 'slug')->ignore($this->postId),
            ],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'selectedTags' => ['array'],
            'selectedTags.*' => ['integer', 'exists:tags,id'],
            'image' => ['nullable','image','max:2048'],
        ]);

        $post = $this->postId
            ? Post::findOrFail($this->postId)
            : new Post();

        $oldImage = $post->image;

        $post->fill([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'content' => $validated['content'],
            'status' => $validated['status'],
            'category_id' => $validated['category_id'],
        ]);

        if (!$this->postId) {
            $post->user_id = Auth::id();
        }

        $post->published_at = $validated['status'] === 'published'
            ? ($post->published_at ?? now())
            : null;
        
        if ($this->image) {
            $post->image = $this->image->store('posts', 'public');
        } elseif ($this->removeCurrentImage) {
            $post->image = null;
        }

        $post->save();

        $post->tags()->sync($validated['selectedTags'] ?? []);

        if ($oldImage && ($this->image || $this->removeCurrentImage)) {
            Storage::disk('public')->delete($oldImage);
        }

        $this->resetForm();

        session()->flash('success', 'Post saved successfully.');
    }

    public function markImageForRemoval(): void
    {
        $this->removeCurrentImage = true;
        $this->existingImage = null;
    }

    public function delete(int $id): void
    {
        $post = Post::findOrFail($id);

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        session()->flash('success', 'Post deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->reset(
            'postId',
            'title',
            'slug',
            'content',
            'category_id',
            'selectedTags',
            'image',
            'existingImage',
            'removeCurrentImage',
            'showForm'
        );

        $this->status = 'draft';

        $this->resetValidation();
    }

    public function render()
    {   
        $user = auth()->user();
        $posts = Post::with(['category', 'user', 'tags'])
                ->when(
                    ! $user->hasRole('admin'),
                    fn ($query) => $query->where('user_id', $user->id)
                )
                ->where('title', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10);
        return $this->view([
            'posts' => $posts,
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function updatestatus(int $postId): void
    {
        abort_unless(auth()->user()?->can('posts.edit'), 403);

        $post = Post::findOrFail($postId);

        $post->status = $post->status === 'published'
            ? 'draft'
            : 'published';

        if ($post->status === 'published' && ! $post->published_at) {
            $post->published_at = now();
        }

        $post->save();

        session()->flash('message', 'Post status updated successfully.');
    }
};
?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Posts</h2>
            <p class="text-secondary mb-0">Create and manage your blog articles.</p>
        </div>
        @can('posts.create')
            
        <button class="btn btn-primary" wire:click="create">
            + Add Post
        </button>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($showForm)
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">
                    {{ $postId ? 'Edit Post' : 'Create Post' }}
                </h5>

                <form wire:submit="save">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text"
                               class="form-control @error('title') is-invalid @enderror"
                               wire:model="title">

                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text"
                               class="form-control @error('slug') is-invalid @enderror"
                               wire:model="slug">

                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select @error('category_id') is-invalid @enderror"
                                wire:model="category_id">
                            <option value="">Select category</option>

                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tags</label>

                        @forelse ($tags as $tag)
                            <div class="form-check" wire:key="post-tag-{{ $tag->id }}">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="tag-{{ $tag->id }}"
                                       value="{{ $tag->id }}"
                                       wire:model="selectedTags">

                                <label class="form-check-label" for="tag-{{ $tag->id }}">
                                    {{ $tag->name }}
                                </label>
                            </div>
                        @empty
                            <p class="text-secondary small">
                                No tags yet. Create tags first.
                            </p>
                        @endforelse

                        @error('selectedTags.*')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  rows="8"
                                  wire:model="content"></textarea>

                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Post Image</label>

                        <input type="file" class="form-control @error('image') is-invalid @enderror" wire:model="image" accept="image/*">

                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div wire:loading wire:target="image"
                            class="text-primary small mt-2">
                            Uploading image...
                        </div>

                        @if ($image)
                            <div class="mt-3">
                                <p class="small text-secondary mb-2">New image preview</p>

                                <img src="{{ $image->temporaryUrl() }}" alt="New image preview" class="img-thumbnail" style="max-width: 220px; max-height: 180px;">
                            </div>
                        @elseif ($existingImage && !$removeCurrentImage)
                            <div class="mt-3">
                                <p class="small text-secondary mb-2">Current image</p>

                                <img src="{{ asset('storage/' . $existingImage) }}" alt="Current post image" class="img-thumbnail d-block mb-2" style="max-width: 220px; max-height: 180px;">

                                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="markImageForRemoval">
                                    Remove Current Image
                                </button>
                            </div>
                        @elseif ($removeCurrentImage)
                            <p class="text-danger small mt-2">
                                The current image will be deleted when you save this post.
                            </p>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" wire:model="status">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary"
                            wire:loading.attr="disabled"
                            wire:target="save">
                        <span wire:loading.remove wire:target="save">Save Post</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>

                    <button type="button"
                            class="btn btn-outline-secondary"
                            wire:click="resetForm">
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <input type="search"
                   class="form-control mb-3"
                   placeholder="Search posts..."
                   wire:model.live.debounce.300ms="search">

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
                                    @can('posts.edit')
                                    <button class="btn btn-sm btn-outline-primary"
                                            wire:click="edit({{ $post->id }})">
                                        Edit
                                    </button>
                                    @endcan
                                    @can('posts.delete')
                                    <button class="btn btn-sm btn-outline-danger"
                                            wire:click="delete({{ $post->id }})"
                                            wire:confirm="Are you sure you want to delete this post?">
                                        Delete
                                    </button>
                                    @endcan
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