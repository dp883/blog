<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

new #[Layout('layouts.admin')] class extends Component
{
    public string $pageTitle = 'Categories';

    public ?int $categoryId = null;
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public string $search = '';
    public bool $showForm = false;

    public function updatedName(): void
    {
        if (!$this->categoryId) {
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
        $category = Category::findOrFail($id);

        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description ?? '';
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
                Rule::unique('categories', 'slug')->ignore($this->categoryId),
            ],
            'description' => ['nullable', 'string'],
        ]);
        Category::updateOrCreate(
            ['id' => $this->categoryId],
            $validated
        );

        $this->resetForm();

        session()->flash('success', 'Category saved successfully.');
    }

    public function delete(int $id): void
    {
        $category = Category::findOrFail($id);

        if ($category->posts()->exists()) {
            session()->flash(
                'error',
                'This category has posts. Reassign or remove those posts before deleting it.'
            );

            return;
        }

        $category->delete();

        session()->flash('success', 'Category deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->reset('categoryId', 'name', 'slug', 'description', 'showForm');
        $this->resetValidation();
    }

    public function render()
    {
        return $this->view([
            'categories' => Category::query()
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
            <h2 class="fw-bold mb-1">Categories</h2>
            <p class="text-secondary mb-0">Manage your blog categories.</p>
        </div>
        @can('categories.create')
        <button class="btn btn-primary" wire:click="create">
            + Add Category
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
                    {{ $categoryId ? 'Edit Category' : 'Create Category' }}
                </h5>

                <form wire:submit="save">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               wire:model="name">

                        @error('name')
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
                        <label class="form-label">Description</label>
                        <textarea class="form-control"
                                  rows="3"
                                  wire:model="description"></textarea>
                    </div>

                    <button class="btn btn-primary" type="submit">
                        Save Category
                    </button>

                    <button class="btn btn-outline-secondary"
                            type="button"
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
                   placeholder="Search categories..."
                   wire:model.live.debounce.300ms="search">

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
                        @forelse ($categories as $category)
                            <tr wire:key="category-{{ $category->id }}">
                                <td class="fw-semibold">{{ $category->name }}</td>
                                <td>{{ $category->slug }}</td>
                                <td>{{ $category->posts_count }}</td>
                                <td class="text-end text-nowrap">
                                    @can('categories.edit')
                                    <button class="btn btn-sm btn-outline-primary"
                                            wire:click="edit({{ $category->id }})">
                                        Edit
                                    </button>
                                    @endcan
                                    @can('categories.delete')
                                    <button class="btn btn-sm btn-outline-danger"
                                            wire:click="delete({{ $category->id }})"
                                            wire:confirm="Are you sure you want to delete this category?">
                                        Delete
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-4">
                                    No categories found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>