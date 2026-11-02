<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Comment Management</h1>
            <p class="text-secondary mb-0">
                Review, approve, reject, or delete blog comments.
            </p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-8">
                    <label for="search" class="form-label">
                        Search comments
                    </label>

                    <input id="search" type="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search comment, author, email, or post...">
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label">
                        Filter by status
                    </label>

                    <select id="status" wire:model.live="status" class="form-select">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="all">All comments</option>
                    </select>
                </div>

            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Comment</th>
                            <th>Author</th>
                            <th>Post</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($comments as $comment)
                            <tr wire:key="comment-{{ $comment->id }}">

                                <td class="ps-3" style="min-width: 220px; max-width: 320px;">
                                    <div class="text-break">
                                        {{ $comment->body }}
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $comment->user?->name ?? 'Deleted user' }}
                                    </div>

                                    <small class="text-secondary">
                                        {{ $comment->user?->email ?? '' }}
                                    </small>
                                </td>

                                <td>
                                    {{ $comment->post?->title ?? 'Deleted post' }}
                                </td>

                                <td>
                                    @if ($comment->status === 'pending')
                                        <span class="badge text-bg-warning">
                                            Pending
                                        </span>
                                    @elseif ($comment->status === 'approved')
                                        <span class="badge text-bg-success">
                                            Approved
                                        </span>
                                    @elseif ($comment->status === 'rejected')
                                        <span class="badge text-bg-danger">
                                            Rejected
                                        </span>
                                    @else
                                        <span class="badge text-bg-secondary">
                                            {{ ucfirst($comment->status) }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <small>
                                        {{ $comment->created_at->format('d M Y') }}
                                    </small>
                                </td>

                                <td class="text-end pe-3">
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">

                                        @can('comments.approve')
                                            @if ($comment->status !== 'approved')
                                                <button type="button" wire:click="approve({{ $comment->id }})" wire:loading.attr="disabled" wire:target="approve({{ $comment->id }})" class="btn btn-sm btn-success">
                                                    Approve
                                                </button>
                                            @endif

                                            @if ($comment->status !== 'rejected')
                                                <button type="button" wire:click="reject({{ $comment->id }})" wire:confirm="Are you sure you want to reject this comment?" wire:loading.attr="disabled" wire:target="reject({{ $comment->id }})" class="btn btn-sm btn-outline-warning">
                                                    Reject
                                                </button>
                                            @endif
                                        @endcan

                                        @can('comments.delete')
                                            <button type="button" wire:click="delete({{ $comment->id }})" wire:confirm="Permanently delete this comment?" wire:loading.attr="disabled" wire:target="delete({{ $comment->id }})" class="btn btn-sm btn-outline-danger">
                                                Delete
                                            </button>
                                        @endcan

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <p class="fw-semibold mb-1">
                                        No comments found
                                    </p>

                                    <p class="text-secondary mb-0">
                                        Try another status filter or search term.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

        @if ($comments->hasPages())
            <div class="card-footer bg-white">
                {{ $comments->links() }}
            </div>
        @endif
    </div>
</div>