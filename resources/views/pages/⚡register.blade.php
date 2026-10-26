
<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        request()->session()->regenerate();

        return redirect()->route('dashboard');
    }
};
?>

<div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="card border-0 shadow rounded-4 w-100" style="max-width: 480px;">
        <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">
                <h2 class="fw-bold">Create Account</h2>
                <p class="text-secondary">Register to get started</p>
            </div>

            <form wire:submit="register">

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">
                        Full Name
                    </label>

                    <input type="text" id="name" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="Enter your full name" autocomplete="name">

                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">
                        Email Address
                    </label>

                    <input type="email" id="email" class="form-control @error('email') is-invalid @enderror" wire:model="email" placeholder="Enter your email" autocomplete="email">

                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        class="form-control @error('password') is-invalid @enderror"
                        wire:model="password"
                        placeholder="Minimum 8 characters"
                        autocomplete="new-password"
                    >
                    
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-semibold">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="password_confirmation"
                        class="form-control"
                        wire:model="password_confirmation"
                        placeholder="Re-enter your password"
                        autocomplete="new-password"
                    >
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" wire:loading.attr="disabled" wire:target="register">
                    <span wire:loading.remove wire:target="register">
                        Create Account
                    </span>

                    <span wire:loading wire:target="register">
                        Creating Account...
                    </span>
                </button>

            </form>

            <div class="text-center mt-4">
                <span class="text-secondary">Already have an account?</span>
                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">
                    Login
                </a>
            </div>

        </div>
    </div>
</div>
