<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Admin Dashboard' }}</title>

    @livewireStyles
</head>

<body class="bg-light">

    <div class="d-flex min-vh-100">

        <aside class="d-flex flex-column flex-shrink-0 p-3 text-bg-dark"
               style="width: 260px;">

            <a href="{{ route('dashboard') }}"
               
               class="d-flex align-items-center mb-4 text-white text-decoration-none">
                <span class="fs-4 fw-bold">Blog Admin</span>
            </a>

            <hr class="text-secondary">

            <nav class="nav nav-pills flex-column mb-auto gap-2">
                @can('dashboard.view')
                <a href="{{ route('dashboard') }}" wire:navigate
                   class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>
                @endcan

                @can('posts.view')
                <a href="{{ route('posts.index') }}" wire:navigate
                   class="nav-link text-white {{ request()->routeIs('posts.*') ? 'active' : '' }}" aria-disabled="true">
                    Posts
                </a>
                @endcan
                
                @can('tags.view')
                <a href="{{ route('tags.index') }}" wire:navigate
                   class="nav-link text-white {{ request()->routeIs('tags.*') ? 'active' : '' }}"aria-disabled="true">
                    Tag
                </a>
                @endcan

                @can('categories.view')
                <a href="{{ route('categories.index') }}" wire:navigate
                   class="nav-link text-white {{ request()->routeIs('categories.*') ? 'active' : '' }}" aria-disabled="true">
                    Category
                </a>
                @endcan

                @can('users.view')
                <a href="{{ route('users.index') }}" wire:navigate
                    class="nav-link text-white {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    Users
                </a>
                @endcan

                @can('roles.view')
                <a href="{{ route('roles.index') }}" wire:navigate
                   class="nav-link text-white {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                   aria-disabled="true">
                    Role 
                </a>
                @endcan
                @can('comments.view')
                    <a href="{{ route('comments.index') }}" wire:navigate
                        class="nav-link text-white {{ request()->routeIs('comments.*') ? 'active' : '' }}">
                        Comments
                    </a>
                @endcan
                <div class="nav-link">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="dropdown-item text-danger">
                            Logout
                        </button>
                    </form>
                </div>

            </nav>

            <hr class="text-secondary">

            <div class="small text-secondary">
                <div class="mt-1">Version 1.01</div>
            </div>
        </aside>

        <div class="d-flex flex-column flex-grow-1 min-vh-100">

            <header class="navbar bg-white border-bottom px-4 py-3 shadow-sm">
                <div class="container-fluid p-0">

                    <h5 class="mb-0 fw-semibold">
                        {{ $pageTitle ?? 'Dashboard' }}
                    </h5>

                    <div class="d-flex align-items-center gap-3">

                        <div class="text-end d-none d-sm-block">
                            <a class="btn btn-outline-primary" href="{{ url('/') }}">Go To Website</a>
                        </div>
                        <div class="text-end d-none d-sm-block">
                            <div class="fw-semibold">
                                {{ auth()->user()->name }}
                            </div>
                            <small class="text-secondary">Administrator</small>
                        </div>
                    </div>
                </div>
            </header>

            <main class="container-fluid p-4 flex-grow-1">

                {{ $slot }}

            </main>

            <footer class="bg-white border-top px-4 py-3 text-secondary small">
                &copy; {{ date('Y') }} Blog Admin. All rights reserved.
            </footer>

        </div>
    </div>

    @livewireScripts
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</body>
</html>