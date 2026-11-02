<div>
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-3">
            <h3>{{ $pageTitle }}</h3>

            <a wire:navigate href="{{ route('roles.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>

        <form wire:submit="save">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3">

                <label class="form-label">Permissions</label>

                <div class="border rounded p-3">

                    <div class="mb-3 border-bottom pb-3">
                        <button type="button" wire:click="selectAllPermissions" class="btn btn-sm btn-outline-primary me-2">
                            Select All
                        </button>

                        <button type="button" wire:click="deselectAllPermissions" class="btn btn-sm btn-outline-danger">
                            Deselect All
                        </button>

                        <span class="text-muted ms-2">
                            {{ count($selectedPermissions) }} /
                            {{ $allpermissions->count() }} selected
                        </span>
                    </div>

                    <div class="row">
                        @forelse($allpermissions as $permission)
                            <div class="col-md-4 col-sm-6 mb-2" wire:key="permission-{{ $permission->id }}">
                                <div class="form-check">

                                    <input type="checkbox" class="form-check-input" id="permission-{{ $permission->id }}" value="{{ $permission->id }}" wire:model="selectedPermissions">

                                    <label class="form-check-label" for="permission-{{ $permission->id }}">
                                        {{ $permission->name }}
                                    </label>

                                </div>
                            </div>

                        @empty

                            <p class="text-muted">
                                No permissions available.
                            </p>

                        @endforelse

                    </div>

                </div>

                @error('selectedPermissions')
                    <span class="text-danger">{{ $message }}</span>
                @enderror

            </div>

            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">
                    {{ $roleId ? 'Update Role' : 'Create Role' }}
                </span>

                <span wire:loading wire:target="save">
                    Saving...
                </span>
            </button>
        </form>
    </div>
</div>