
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h3>{{ $pageTitle }}</h3>
        <a wire:navigate href="{{ route('categories.index') }}" class="btn btn-secondary">
            Back
        </a>
    </div>

    <form wire:submit="save">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label>Slug</label>
            <input type="text" wire:model="slug" class="form-control @error('slug') is-invalid @enderror">
            @error('slug')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror"></textarea>
            @error('description')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
            {{ $categoryId ? 'Update Category' : 'Create Category' }}
        </button>
    </form>
</div>