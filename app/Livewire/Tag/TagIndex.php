<?php

namespace App\Livewire\Tag;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

use App\Models\Tag;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.admin')]
class TagIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public string $pageTitle = 'Tags';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->can('tags.delete'), 403);

        $tag = Tag::findOrFail($id);

        $tag->delete();

        session()->flash('success', 'Tag deleted successfully.');
    }

    public function render()
    {
        $user = auth()->user();

        $tags = Tag::withCount('posts')
            ->where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.tag.index', [
            'tags' => $tags,
        ]);
    }
}