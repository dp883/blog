<?php

namespace App\Livewire\Category;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.admin')]
class CategoryIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public string $pageTitle = 'Categories';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->can('categories.delete'), 403);

        $category = Category::findOrFail($id);

        $category->delete();

        session()->flash('success', 'Category deleted successfully.');
    }

    public function render()
    {
        $user = auth()->user();

        $categories = Category::withCount('posts')
            ->where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.category.index', [
            'categories' => $categories,
        ]);
    }
}