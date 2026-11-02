
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h3>{{ $pageTitle }}</h3>
        <a wire:navigate href="{{ route('users.index') }}" class="btn btn-secondary">
            Back
        </a>
    </div>

    <form wire:submit="save">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror"  >
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="text" wire:model="email" class="form-control @error('email') is-invalid @enderror">
            @error('email')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror">
            @error('password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" wire:model="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror">
            @error('password_confirmation')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label>Role</label>
            <select wire:model="selectedRole" class="form-select @error('selectedRole') is-invalid @enderror">
                <option value="">Select Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}">
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
            {{ $userId ? 'Update User' : 'Create User' }}
        </button>
    </form>
</div>