<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public string $pageTitle = 'Dashboard';

    public function render()
    {
        $user = auth()->user();

        $users = User::count();
        $posts = Post::when(! $user->hasRole('admin'),fn ($query) => $query->where('user_id', $user->id))->count();
        $categories = Category::count();
        $publishedPosts = Post::when(! $user->hasRole('admin'),fn ($query) => $query->where('user_id', $user->id))->whereNotNull('published_at')->count();
    
        return view('livewire.dashboard', [
            'users' => $users,
            'posts' => $posts,
            'categories' => $categories,
            'publishedPosts' => $publishedPosts,
        ]);
    }
}