<?php

use App\Livewire\Category\CategoryForm;
use App\Livewire\Category\CategoryIndex;
use App\Livewire\Comment\CommentIndex;
use App\Livewire\Dashboard;
use App\Livewire\Post\PostForm;
use App\Livewire\Post\PostIndex;
use App\Livewire\Role\RoleForm;
use App\Livewire\Role\RoleIndex;
use App\Livewire\Tag\TagIndex;
use App\Livewire\Tag\TagForm;
use App\Livewire\User\UserForm;
use App\Livewire\User\UserIndex;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {

    Route::livewire('/login', 'pages::login')->name('login');
    Route::livewire('/register', 'pages::register')->name('register');
});
Route::livewire('/', 'pages::home')->name('home');

Route::livewire('/posts/{slug}', 'pages::posts.show')->name('posts.show');

Route::middleware('auth')->group(function () {
    Route::livewire('/dashboard', Dashboard::class)->name('dashboard');
    
    Route::livewire('/admin/categories', 'pages::categories.index')->middleware(['auth', 'can:categories.view'])->name('categories.index');

    Route::livewire('/admin/categories', CategoryIndex::class)->middleware(['auth', 'can:categories.view'])->name('categories.index');
    Route::livewire('/admin/categories/{categoryId}/edit', CategoryForm::class)->middleware(['auth', 'can:categories.edit'])->name('categories.edit');
    Route::livewire('/admin/categories/create', CategoryForm::class)->middleware(['auth', 'can:categories.create'])->name('categories.create');

    Route::livewire('/admin/posts', PostIndex::class)->middleware(['auth', 'can:posts.view'])->name('posts.index');
    Route::livewire('/admin/posts/{postId}/edit', PostForm::class)->middleware(['auth', 'can:posts.edit'])->name('posts.edit');
    Route::livewire('/admin/posts/create', PostForm::class)->middleware(['auth', 'can:posts.create'])->name('posts.create');

    Route::livewire('/admin/tags', TagIndex::class)->middleware(['auth', 'can:tags.view'])->name('tags.index');
    Route::livewire('/admin/tags/{tagId}/edit', TagForm::class)->middleware(['auth', 'can:tags.edit'])->name('tags.edit');
    Route::livewire('/admin/tags/create', TagForm::class)->middleware(['auth', 'can:tags.create'])->name('tags.create');

    Route::livewire('/admin/roles', RoleIndex::class)->middleware(['auth', 'can:roles.view'])->name('roles.index');
    Route::livewire('/admin/roles/{roleId}/edit', RoleForm::class)->middleware(['auth', 'can:roles.edit'])->name('roles.edit');
    Route::livewire('/admin/roles/create', RoleForm::class)->middleware(['auth', 'can:roles.create'])->name('roles.create');


    Route::livewire('/admin/users', UserIndex::class)->middleware(['auth', 'can:users.view'])->name('users.index');
    Route::livewire('/admin/users/{userId}/edit', UserForm::class)->middleware(['auth', 'can:users.edit'])->name('users.edit');
    Route::livewire('/admin/users/create', UserForm::class)->middleware(['auth', 'can:users.create'])->name('users.create');


    Route::livewire('/admin/comments', CommentIndex::class)->middleware(['auth', 'can:comments.view'])->name('comments.index');

    Route::post('/logout', function () {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});