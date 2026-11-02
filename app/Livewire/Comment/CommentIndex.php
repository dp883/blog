<?php

namespace App\Livewire\Comment;

use App\Models\Comment;
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
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Layout('layouts.admin')]
class CommentIndex extends Component
{
    use WithPagination;
    public string $pageTitle = 'Comments';

    public string $search = '';

    public string $status = 'all';
    public function mount(): void
    {
        abort_unless(
            auth()->user()->can('comments.view'),
            403
        );
    }

    public function approve(int $id): void
    {
        abort_unless(
            auth()->user()?->can('comments.approve'),
            403
        );

        $comment = Comment::findOrFail($id);
        $comment->update(['status' => 'approved',]);
        session()->flash('success','Comment approved successfully.');
    }

    public function reject(int $id): void
    {
        abort_unless(
            auth()->user()?->can('comments.approve'),
            403
        );
        $comment = Comment::findOrFail($id);
        $comment->update(['status' => 'rejected',]);

        session()->flash('success','Comment rejected successfully.');
    }

    /**
     * Permanently delete a comment.
     */
    public function delete(int $id): void
    {
        abort_unless(
            auth()->user()?->can('comments.delete'),
            403
        );
        $comment = Comment::findOrFail($id);
        $comment->delete();
        session()->flash('success','Comment deleted successfully.');
    }

    public function render()
    {
        $user = auth()->user();
        $comments = Comment::query()
            ->with(['user', 'post'])
            ->when(! $user->hasRole('admin'), function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                    ->orWhereHas('post', function ($postQuery) use ($user) {
                        $postQuery->where('user_id', $user->id);
                    });
                });
            })
            ->when($this->search !== '', function ($query) {
                $search = $this->search;

                $query->where(function ($q) use ($search) {
                    $q->where('body', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('post', function ($postQuery) use ($search) {
                            $postQuery->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->when($this->status !== 'all',
                fn ($query) => $query->where('status', $this->status)
            )
            ->latest()
            ->paginate(10);
        return view('livewire.comment.index',['comments' => $comments]);
    }

}
