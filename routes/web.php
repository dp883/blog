<?php

use Illuminate\Routing\RouteGroup;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {

    Route::livewire('/login', 'pages::login')->name('login');
    Route::livewire('/register', 'pages::register')->name('register');
});
Route::livewire('/', 'pages::home')->name('home');

Route::livewire('/posts/{slug}', 'pages::posts.show')->name('posts.show');

Route::middleware('auth')->group(function () {
    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');
    
    Route::livewire('/admin/categories', 'pages::categories.index')->middleware(['auth', 'can:categories.view'])->name('categories.index');

    Route::livewire('/admin/tags', 'pages::tags.index')->middleware(['auth', 'can:tags.view'])->name('tags.index');

    Route::livewire('/admin/posts', 'pages::posts.index')->middleware(['auth', 'can:posts.view'])->name('posts.index');

    Route::livewire('/admin/roles', 'pages::roles.index')->middleware(['auth', 'can:roles.view'])->name('roles.index');

    Route::livewire('/admin/users', 'pages::users.index')->middleware(['auth', 'can:users.view'])->name('users.index');

    Route::livewire('/admin/comments', 'pages::comments.index')->middleware(['auth', 'can:comments.view'])->name('comments.index');

    Route::post('/logout', function () {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});