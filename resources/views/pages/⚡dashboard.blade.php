<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;

new #[Layout('layouts.admin')] class extends Component
{
    public string $pageTitle = 'Dashboard';

    public function render()
    {
        $user = auth()->user();
        return $this->view([
            'totalPosts' => Post::when(
                                ! $user->hasRole('admin'),
                                fn ($query) => $query->where('user_id', $user->id)
                            )
                            ->count(),

            'publishedPosts' => Post::when(
                            ! $user->hasRole('admin'),
                            fn ($query) => $query->where('user_id', $user->id)
                        )
                        ->where('status', 'published')->count(),

            'totalCategories' => Category::count(),

            'totalUsers' => User::count(),

            'latestPosts' => Post::latest()
                ->when(
                    ! $user->hasRole('admin'),
                    fn ($query) => $query->where('user_id', $user->id)
                )
                ->take(5)
                ->get(),
        ]);
    }
};
?>

<div>
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Dashboard</h2>
        <p class="text-secondary mb-0">
            Welcome to your blog admin panel.
        </p>
    </div>

    <div class="row g-4 mb-4">

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="text-secondary mb-2">Total Posts</p>
                    <h2 class="fw-bold mb-0">{{ $totalPosts ?? 0 }}</h2>
                    <small class="text-secondary">Blog articles</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="text-secondary mb-2">Published Posts</p>
                    <h2 class="fw-bold mb-0">{{  $publishedPosts ?? 0 }}</h2>
                    <small class="text-secondary">Live articles</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="text-secondary mb-2">Categories</p>
                    <h2 class="fw-bold mb-0">{{ $totalCategories ?? 0 }}</h2>
                    <small class="text-secondary">Content categories</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="text-secondary mb-2">Users</p>
                    <h2 class="fw-bold mb-0">{{ $user ?? 0 }}</h2>
                    <small class="text-secondary">Registered accounts</small>
                </div>
            </div>
        </div>

    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h5 class="fw-bold">Welcome to your admin panel!</h5>
        </div>
    </div>
</div>