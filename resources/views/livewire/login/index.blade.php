<div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="card border-0 shadow-sm rounded-4 w-100" style="max-width: 450px;">
        <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">
                <h2 class="fw-bold">Welcome Back!</h2>
                <p class="text-secondary">
                    Login to your account
                </p>
            </div>

            <form wire:submit="login">

                <div class="mb-3">
                    <label for="email" class="form-label">
                        Email address
                    </label>

                    <input type="email" id="email" class="form-control @error('email') is-invalid @enderror" wire:model="email" placeholder="Enter your email" autocomplete="email">

                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        Password
                    </label>
                    <div x-data="{ showPassword: false }">
                        <div class="input-group">
                            <input :type="showPassword ? 'text' : 'password'" class="form-control @error('password') is-invalid @enderror" wire:model="password" placeholder="Enter your password" autocomplete="current-password">
                            <button type="button" class="btn btn-outline-secondary" @click="showPassword = !showPassword">
                                <span x-text="showPassword ? 'Hide' : 'Show'"></span>
                            </button>
                        </div>
                    </div>  
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" id="remember" class="form-check-input" wire:model="remember">

                    <label for="remember" class="form-check-label">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2" wire:loading.attr="disabled" wire:target="login">
                    <span wire:loading.remove wire:target="login">
                        Login
                    </span>

                    <span wire:loading wire:target="login">
                        Logging in...
                    </span>
                </button>

            </form>
        </div>
    </div>
</div>