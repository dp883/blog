<?php

namespace App\Livewire\Login;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use App\Models\User;

#[Layout('layouts.app')]
class LoginIndex extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials,$this->remember)) {
            throw ValidationException::withMessages([
                'email' => 'The email or password is incorrect.',
            ]);
        }

        request()->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function render()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('livewire.login.index');
    }
}