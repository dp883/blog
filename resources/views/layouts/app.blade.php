<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body>
        <div class="d-flex flex-column flex-grow-1">

            <header class="navbar bg-white border-bottom px-4 py-3 shadow-sm">
                <div class="container-fluid p-0">
                    <div class="d-flex align-items-center gap-3">
                        <ul class="nav">
                            <li class="nav-item">
                                <a href="{{ url('/') }}" class="nav-link">Home</a>
                            </li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2">

                        @guest
                            <a href="{{ route('login') }}" wire:navigate class="btn btn-outline-primary">
                                Login
                            </a>
                        @endguest

                        @auth
                            <span class="text-secondary me-2">
                                Hi, {{ auth()->user()->name }}
                            </span>

                            <a href="{{ route('dashboard') }}" wire:navigate class="btn btn-outline-primary">
                                Dashboard
                            </a>

                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf

                                <button type="submit" class="btn btn-danger">
                                    Logout
                                </button>
                            </form>
                        @endauth
                    </div>
                </div>
            </header>
        </div>

        {{ $slot }}

        @livewireScripts
    </body>
</html>
