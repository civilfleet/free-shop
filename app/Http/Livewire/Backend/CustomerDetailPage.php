<?php

namespace App\Http\Livewire\Backend;

use App\Http\Livewire\Traits\TrimEmptyStrings;
use App\Models\Comment;
use App\Models\Customer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class CustomerDetailPage extends BackendPage
{
    use WithPagination;
    use AuthorizesRequests;
    use TrimEmptyStrings;

    protected $paginationTheme = 'bootstrap';

    public Customer $customer;

    public bool $addComment = false;
    public string $newComment = '';

    protected function title()
    {
        return 'Customer ' . $this->customer->name;
    }

    public function render()
    {
        $this->authorize('view', $this->customer);

        return parent::view('livewire.backend.customer-detail-page', [
            'orders' => $this->customer->orders()
                ->orderBy('created_at', 'desc')
                ->paginate(10),
            'comments' => $this->customer->comments()
                ->orderBy('created_at', 'asc')
                ->paginate(10),
        ]);
    }

    public function saveComment()
    {
        $this->validate([
            'newComment' => [
                'required',
                'string',
            ]
        ]);

        $comment = new Comment([
            'content' => $this->newComment,
        ]);
        $comment->user()->associate(Auth::user());
        $this->customer->comments()->save($comment);

        $this->reset(['addComment', 'newComment']);
    }

    public function resetComment()
    {
        $this->reset(['addComment', 'newComment']);
    }

    public function deleteComment($id)
    {
        $this->authorize('delete', $this->customer);

        Comment::where('id', $id)->delete();
    }
}
