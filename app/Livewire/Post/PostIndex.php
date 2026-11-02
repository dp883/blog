<?php

namespace App\Livewire\Post;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.admin')]
class PostIndex extends Component
{
    use WithPagination;

    public string $pageTitle = 'Posts';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->can('posts.delete'), 403);

        $post = Post::findOrFail($id);

        abort_unless(
            auth()->user()->hasRole('admin') ||
            $post->user_id === auth()->id(),
            403
        );

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        session()->flash('success', 'Post deleted successfully.');
    }

    public function updatestatus(int $postId): void
    {
        abort_unless(auth()->user()->can('posts.edit'), 403);

        $post = Post::findOrFail($postId);

        abort_unless(
            auth()->user()->hasRole('admin') ||
            $post->user_id === auth()->id(),
            403
        );

        $post->status = $post->status === 'published'
            ? 'draft'
            : 'published';

        $post->published_at = $post->status === 'published'
            ? ($post->published_at ?? now())
            : null;

        $post->save();

        session()->flash('success', 'Post status updated successfully.');
    }
    public function render()
    {
        $user = auth()->user();

        $posts = Post::with(['category', 'user', 'tags'])
            ->when(
                !$user->hasRole('admin'),
                fn ($query) => $query->where('user_id', $user->id)
            )
            ->where('title', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.post.index', [
            'posts' => $posts,
        ]);
    }
}