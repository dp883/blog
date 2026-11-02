<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.admin')]
class UserIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public string $pageTitle = 'Users';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->can('users.delete'), 403);

        $user = User::findOrFail($id);

        $user->delete();

        session()->flash('success', 'User deleted successfully.');
    }

    public function render()
    {
        $user = auth()->user();

        $users = User::where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.user.index', [
            'users' => $users,
        ]);
    }
}