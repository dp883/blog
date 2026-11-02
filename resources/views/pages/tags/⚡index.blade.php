<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tag;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

new #[Layout('layouts.admin')] class extends Component
{
    public string $pageTitle = 'Tags';

    public ?int $tagId = null;
    public string $name = '';
    public string $slug = '';
    public string $search = '';
    public bool $showForm = false;

    public function updatedName(): void
    {
        if (!$this->tagId) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $tag = Tag::findOrFail($id);

        $this->tagId = $tag->id;
        $this->name = $tag->name;
        $this->slug = $tag->slug;
        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'slug')->ignore($this->tagId),
            ],
        ]);
        Tag::updateOrCreate(
            ['id' => $this->tagId],
            $validated
        );

        $this->resetForm();

        session()->flash('success', 'Tag saved successfully.');
    }

    public function delete(int $id): void
    {
        $tag = Tag::findOrFail($id);

        $tag->delete();

        session()->flash('success', 'Tag deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->reset('tagId', 'name', 'slug', 'showForm');
        $this->resetValidation();
    }

    public function render()
    {
        return $this->view([
            'tags' => Tag::query()
                ->withCount('posts')
                ->where('name', 'like', '%' . $this->search . '%')
                ->latest()
                ->get(),
        ]);
    }
};
?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Tags</h2>
            <p class="text-secondary mb-0">Manage your blog tags.</p>
        </div>
        @can('tags.create')
        <button class="btn btn-primary" wire:click="create">
            + Add Tag
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
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">
                    {{ $tagId ? 'Edit Tag' : 'Create Tag' }}
                </h5>

                <form wire:submit="save">
                    <div class="mb-3">
                        <label class="form-label">Tag Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror" wire:model="slug">

                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button class="btn btn-primary" type="submit">
                        Save Tag
                    </button>

                    <button class="btn btn-outline-secondary" type="button" wire:click="resetForm">
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <input type="search" class="form-control mb-3" placeholder="Search tags..." wire:model.live.debounce.300ms="search">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Posts</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($tags as $tag)
                            <tr wire:key="tag-{{ $tag->id }}">
                                <td class="fw-semibold">{{ $tag->name }}</td>
                                <td>{{ $tag->slug }}</td>
                                <td>{{ $tag->posts_count }}</td>
                                <td class="text-end text-nowrap">
                                    @can('tags.edit')
                                    <button class="btn btn-sm btn-outline-primary"
                                            wire:click="edit({{ $tag->id }})">
                                        Edit
                                    </button>
                                    @endcan
                                    @can('tags.delete')
                                    <button class="btn btn-sm btn-outline-danger"
                                            wire:click="delete({{ $tag->id }})"
                                            wire:confirm="Are you sure you want to delete this tag?">
                                        Delete
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-4">
                                    No tags found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>