<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * List users (admin only).
     */
    public function index(Request $request)
    {
        $query = User::query()->withCount(['loans' => fn ($q) => $q->active()]);

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.trim((string) $request->input('q')).'%')
                    ->orWhere('email', 'like', '%'.trim((string) $request->input('q')).'%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', trim((string) $request->input('role')));
        }

        $perPage = min(max((int) $request->input('per_page', 20), 1), 50);

        return UserResource::collection($query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString());
    }

    /**
     * Change a user's role (admin only).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        $user->update(['role' => $data['role']]);

        return (new UserResource($user->loadCount(['loans' => fn ($q) => $q->active()])))
            ->additional(['message' => 'User updated successfully.']);
    }
}
