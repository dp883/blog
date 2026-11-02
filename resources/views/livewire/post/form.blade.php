
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h3>{{ $pageTitle }}</h3>
        <a wire:navigate href="{{ route('posts.index') }}" class="btn btn-secondary">
            Back
        </a>
    </div>

    <form wire:submit="save">
        <div class="mb-3">
            <label>Title</label>
            <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror"  >
            @error('title')
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
            <label>Content</label>
            <textarea wire:model="content" class="form-control @error('content') is-invalid @enderror" rows="6"></textarea>
            @error('content')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label>Category</label>
            <select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror">
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label>Tags</label>
            @foreach($tags as $tag)
                <div class="form-check">
                    <input type="checkbox" wire:model="selectedTags" value="{{ $tag->id }}" class="form-check-input">
                    <label class="form-check-label">
                        {{ $tag->name }}
                    </label>
                </div>
            @endforeach
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select wire:model="status" class="form-select">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Image</label>
            <input type="file" wire:model="image" class="form-control">
            @error('image')
                <span class="text-danger">{{ $message }}</span>
            @enderror

            <div wire:loading wire:target="image">
                Uploading image...
            </div>
            @if($image)
                <img src="{{ $image->temporaryUrl() }}" width="120" class="mt-2">
            @elseif($existingImage)
                <div class="mt-2">
                    <img src="{{ Storage::disk('public')->url($existingImage) }}" width="120">

                    <button type="button" wire:click="removecurrentImage" class="btn btn-danger btn-sm">
                        Remove Image
                    </button>
                </div>
            @endif
        </div>
        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
            {{ $postId ? 'Update Post' : 'Create Post' }}
        </button>
    </form>
</div>