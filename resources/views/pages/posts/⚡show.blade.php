
<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component
{
    public string $slug;
    public string $comment = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function addComment(): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('login', navigate: true);
            return;
        }

        $validated = $this->validate([
            'comment' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        $post = Post::where('slug', $this->slug)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        Comment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'body' => $validated['comment'],
            'status' => 'pending',
        ]);

        $this->reset('comment');

        session()->flash(
            'commentMessage',
            'Your comment has been submitted and is awaiting approval.'
        );
    }

    public function render()
    {
        $post = Post::with(['category', 'tags', 'user','comments'])
            ->where('slug', $this->slug)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        return $this->view([
            'post' => $post,
            'comments' => $post->comments()
                ->with('user')
                ->where('status', 'approved')
                ->latest()
                ->get(),
        ]);
    }
};
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <article class="col-lg-9">
            <a href="{{ route('home') }}" wire:navigate
               class="btn btn-outline-secondary btn-sm mb-4">
                &larr; Back to Blog
            </a>

            @if ($post->image)
                <img
                    src="{{ asset('storage/' . $post->image) }}"
                    alt="{{ $post->title }}"
                    class="img-fluid rounded-4 w-100 mb-4"
                    style="max-height: 480px; object-fit: cover;"
                >
            @endif

            @if ($post->category)
                <div class="mb-3">
                    <span class="badge text-bg-primary">
                        {{ $post->category->name }}
                    </span>
                </div>
            @endif

            <h1 class="display-5 fw-bold mb-3">
                {{ $post->title }}
            </h1>

            <p class="text-secondary mb-4">
                Published {{ $post->published_at?->format('d M Y') }}
                @if ($post->user)
                    &middot; By {{ $post->user->name }}
                @endif
            </p>

            @if ($post->tags->isNotEmpty())
                <div class="mb-4">
                    @foreach ($post->tags as $tag)
                        <span class="badge text-bg-light border me-1">
                            #{{ $tag->name }}
                        </span>
                    @endforeach
                </div>
            @endif

            <div class="post-content fs-5" style="line-height: 1.9;">
                {!! nl2br(e($post->content)) !!}
            </div>
        </article>
    </div>

    <hr class="my-5">

    <section id="comments">
        <h2 class="h3 fw-bold mb-4">
            Comments ({{ $comments->count() }})
        </h2>

        @auth
            <form wire:submit="addComment" class="mb-5">
                <div class="mb-3">
                    <label for="comment" class="form-label">
                        Write a comment
                    </label>

                    <textarea id="comment" wire:model="comment" class="form-control @error('comment') is-invalid @enderror" rows="4" placeholder="Share your thoughts..."></textarea>

                    @error('comment')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="addComment">
                    <span wire:loading.remove wire:target="addComment">
                        Submit Comment
                    </span>

                    <span wire:loading wire:target="addComment">
                        Submitting...
                    </span>
                </button>
            </form>
        @else
            <div class="alert alert-light border mb-5">
                <a href="{{ route('login') }}" wire:navigate class="btn btn-primary btn-sm">
                    Login to Comment
                </a>
            </div>
        @endauth

        @if (session()->has('commentMessage'))
            <div class="alert alert-success">
                {{ session('commentMessage') }}
            </div>
        @endif
        @forelse ($comments as $item)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <strong>{{ $item->user->name ?? 'User' }}</strong>

                        <small class="text-secondary">
                            {{ $item->created_at->format('d M Y, h:i A') }}
                        </small>
                    </div>

                    <p class="mb-0 mt-3" style="white-space: pre-line;">{{ $item->body }}</p>
                </div>
            </div>
        @empty
            <p class="text-secondary">
                No comments yet. Be the first to comment!
            </p>
        @endforelse
    </section>
</div>
