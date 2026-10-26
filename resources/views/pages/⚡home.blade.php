
<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Post;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return $this->view([
            'posts' => Post::with('category')
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('title', 'like', '%' . $this->search . '%');
                    });
                })
                ->latest('published_at')
                ->paginate(9),
        ]);
    }
};
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Our Blog</h1>
        <p class="text-secondary">
            Discover our latest articles.
        </p>
    </div>

    <div class="row justify-content-center mb-4">
        <div class="col-md-6">
            <input type="search" class="form-control form-control-lg" placeholder="Search articles..." wire:model.live.debounce.300ms="search">
        </div>
    </div>

    <div class="row g-4">
        @forelse ($posts as $post)
            <div class="col-md-6 col-lg-4" wire:key="post-{{ $post->id }}">
                <article class="card h-100 border-0 shadow-sm">
                    @if ($post->image)
                        <img src="{{ asset('storage/' . $post->image) }}" class="card-img-top" alt="{{ $post->title }}" style="height: 220px; object-fit: cover;">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 220px;">
                            <span class="text-secondary">No image available</span>
                        </div>
                    @endif

                    <div class="card-body d-flex flex-column">
                        @if ($post->category)
                            <span class="badge text-bg-primary align-self-start mb-2">
                                {{ $post->category->name }}
                            </span>
                        @endif

                        <h5 class="card-title fw-bold">
                            {{ $post->title }}
                        </h5>

                        <p class="text-secondary small">
                            {{ $post->published_at?->format('d M Y') }}
                        </p>

                        <p class="card-text text-secondary">
                            {{ \Illuminate\Support\Str::limit(strip_tags($post->content), 120) }}
                        </p>

                        <a href="{{ route('posts.show', $post->slug) }}" wire:navigate class="btn btn-outline-primary mt-auto align-self-start">
                            Read More
                        </a>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <h4>No published posts found</h4>
                <p class="text-secondary">
                    Try another search or check back later.
                </p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $posts->links() }}
    </div>
</div>