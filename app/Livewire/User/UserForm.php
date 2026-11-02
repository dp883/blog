<?php

namespace App\Livewire\User;

use Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

use App\Models\Category;
use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Layout('layouts.admin')]
class UserForm extends Component
{
    use WithFileUploads;

    public string $pageTitle = 'Create User';

    public ?int $userId = null;

    public string $name = '';
    public ?string $email = null;
    public ?string $password = null;
    public ?string $password_confirmation = null;

    public string $selectedRole = '';

    public function mount(?int $userId = null): void
    {
        if ($userId) {
            $this->userId = $userId;

            $user = User::findOrFail($userId);

            $this->pageTitle = 'Edit User';

            $this->name = $user->name;
            $this->email = $user->email;
            $this->selectedRole = (string) (
                $user->roles->first()?->id ?? ''
            );
        } else {
            abort_unless(
                auth()->user()->can('users.create'),
                403
            );
        }
    }

    public function save()
    {
        if ($this->userId) {
            abort_unless(
                auth()->user()->can('users.edit'),
                403
            );
        } else {
            abort_unless(
                auth()->user()->can('users.create'),
                403
            );
        }

        $validated = $this->validate([
            'name' => ['required','string','max:255',Rule::unique('users', 'name')->ignore($this->userId)],
            'email' => ['required','email','max:255',Rule::unique('users', 'email')->ignore($this->userId)],
            'password' => ['nullable','string','min:8','confirmed'],
            'selectedRole' => ['required','integer',Rule::exists('roles', 'id')->where('guard_name', 'web')],
        ]);

        $user = $this->userId ? User::findOrFail($this->userId) : new User();

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }
        $user->save();

        $role = Role::where('guard_name', 'web')
            ->findOrFail($validated['selectedRole']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $user->syncRoles([$role]); 
        session()->flash('success', 'User saved successfully.');
        return $this->redirectRoute('users.index');
    }

    public function render()
    {
        return view('livewire.user.form', [
            'roles' => Role::where('guard_name', 'web')->get(),
        ]);
    }
}