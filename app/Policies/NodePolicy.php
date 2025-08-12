<?php

namespace App\Policies;

use App\Models\Node;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NodePolicy
{
    public function before(User $user): ?Response
    {
        return $user->is_admin ? Response::allow() : null;
    }

    public function view(User $user, Node $node): Response
    {
        return Response::allow(); // everyone authenticated can view
    }

    public function create(User $user): Response
    {
        return Response::deny('Only admins can create nodes.');
    }

    public function update(User $user, Node $node): Response
    {
        return Response::deny('Only admins can update nodes.');
    }

    public function delete(User $user, Node $node): Response
    {
        return Response::deny('Only admins can delete nodes.');
    }
}
