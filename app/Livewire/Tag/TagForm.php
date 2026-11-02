<?php

namespace App\Livewire\Tag;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

use App\Models\Category;
use App\Models\Tag;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

#[Layout('layouts.admin')]
class TagForm extends Component
{
    use WithFileUploads;

    public string $pageTitle = 'Create Tag';

    public ?int $tagId = null;

    public string $name = '';
    public string $slug = '';

    public function mount(?int $tagId = null): void
    {
        if ($tagId) {
            $this->tagId = $tagId;

            $tag = Tag::findOrFail($tagId);

            $this->pageTitle = 'Edit Tag';

            $this->name = $tag->name;
            $this->slug = $tag->slug;
        } else {
            abort_unless(
                auth()->user()->can('tags.create'),
                403
            );
        }
    }

    public function updatedName(): void
    {
        if (!$this->tagId) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function save()
    {
        if ($this->tagId) {
            abort_unless(
                auth()->user()->can('tags.edit'),
                403
            );
        } else {
            abort_unless(
                auth()->user()->can('tags.create'),
                403
            );
        }

        $validated = $this->validate([
            'name' => ['required','string','max:255',
                Rule::unique('tags', 'name')->ignore($this->tagId),
            ],
            'slug' => ['required','string','max:255',
                Rule::unique('tags', 'slug')->ignore($this->tagId),
            ],
        ]);

        Tag::updateOrCreate(
            ['id' => $this->tagId],
            $validated
        );
    
        session()->flash('success', 'Tag saved successfully.');
        return $this->redirectRoute('tags.index');
    }

    public function render()
    {
        return view('livewire.tag.form');
    }
}