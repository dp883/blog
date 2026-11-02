<?php

namespace App\Livewire\Post;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

#[Layout('layouts.admin')]
class PostForm extends Component
{
    use WithFileUploads;

    public string $pageTitle = 'Create Post';

    public ?int $postId = null;

    public string $title = '';
    public string $slug = '';
    public string $content = '';
    public string $status = 'draft';

    public ?int $category_id = null;

    public array $selectedTags = [];

    public $image = null;

    public ?string $existingImage = null;

    public bool $removeCurrentImage = false;

    // Load post when editing
    public function mount(?int $postId = null): void
    {
        if ($postId) {
            $this->postId = $postId;

            $post = Post::with('tags')->findOrFail($postId);

            $this->authorizePost($post);

            $this->pageTitle = 'Edit Post';

            $this->title = $post->title;
            $this->slug = $post->slug;
            $this->content = $post->content;
            $this->status = $post->status;
            $this->category_id = $post->category_id;

            $this->selectedTags = $post->tags
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();

            $this->existingImage = $post->image;
        } else {
            abort_unless(
                auth()->user()->can('posts.create'),
                403
            );
        }
    }

    // Auto generate slug
    public function updatedTitle(): void
    {
        if (!$this->postId) {
            $this->slug = Str::slug($this->title);
        }
    }

    // Save post
    public function save()
    {
        if ($this->postId) {
            abort_unless(
                auth()->user()->can('posts.edit'),
                403
            );
        } else {
            abort_unless(
                auth()->user()->can('posts.create'),
                403
            );
        }

        $validated = $this->validate([
            'title' => ['required','string','max:255',
                Rule::unique('posts', 'title')->ignore($this->postId),
            ],
            'slug' => ['required','string','max:255',
                Rule::unique('posts', 'slug')->ignore($this->postId),
            ],
            'content' => ['required', 'string'],
            'status' => ['required',
                Rule::in(['draft', 'published']),
            ],
            'category_id' => ['required','integer','exists:categories,id'],
            'selectedTags' => ['array'],
            'selectedTags.*' => ['integer', 'exists:tags,id'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $post = $this->postId
            ? Post::findOrFail($this->postId)
            : new Post();

        if ($this->postId) {
            $this->authorizePost($post);
        }

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
        session()->flash('success', 'Post saved successfully.');
        return $this->redirectRoute('posts.index');
    }

    // Mark existing image for removal
    public function removecurrentImage(): void
    {
        $this->removeCurrentImage = true;
        $this->existingImage = null;
    }

    // Authorization helper
    private function authorizePost(Post $post): void
    {
        abort_unless(
            auth()->user()->hasRole('admin') ||
            $post->user_id === auth()->id(),
            403
        );
    }

    public function render()
    {
        return view('livewire.post.form', [
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }
}