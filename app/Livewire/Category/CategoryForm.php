<?php

namespace App\Livewire\Category;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

use App\Models\Category;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

#[Layout('layouts.admin')]
class CategoryForm extends Component
{
    use WithFileUploads;

    public string $pageTitle = 'Create Category';

    public ?int $categoryId = null;

    public string $name = '';
    public string $slug = '';
    public string $description = '';

    public function mount(?int $categoryId = null): void
    {
        if ($categoryId) {
            $this->categoryId = $categoryId;

            $category = Category::findOrFail($categoryId);

            $this->pageTitle = 'Edit Category';

            $this->name = $category->name;
            $this->slug = $category->slug;
            $this->description = $category->description;    
        } else {
            abort_unless(
                auth()->user()->can('categories.create'),
                403
            );
        }
    }

    public function updatedName(): void
    {
        if (!$this->categoryId) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function save()
    {
        if ($this->categoryId) {
            abort_unless(
                auth()->user()->can('categories.edit'),
                403
            );
        } else {
            abort_unless(
                auth()->user()->can('categories.create'),
                403
            );
        }

        $validated = $this->validate([
            'name' => ['required','string','max:255',
                Rule::unique('categories', 'name')->ignore($this->categoryId),
            ],
            'slug' => ['required','string','max:255',
                Rule::unique('categories', 'slug')->ignore($this->categoryId),
            ],
            'description' => ['nullable', 'string'],
        ]);

        Category::updateOrCreate(
            ['id' => $this->categoryId],
            $validated
        );
    
        session()->flash('success', 'Category saved successfully.');
        return $this->redirectRoute('categories.index');
    }

    public function render()
    {
        return view('livewire.category.form');
    }
}