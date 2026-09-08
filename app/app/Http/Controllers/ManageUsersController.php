<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ManageUsersController extends Controller
{
    /**
     * Admin user management list.
     */
    public function index(Request $request)
    {
        $query = User::query()->withCount(['loans' => fn ($q) => $q->active()]);

        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', trim((string) $request->input('role')));
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Update a user's role (admin only).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            session()->flash('error', 'You cannot change your own role while logged in.');

            return back();
        }

        $data = $request->validate([
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        $user->update(['role' => $data['role']]);

        session()->flash('success', "{$user->name} is now a {$data['role']}.");

        return back();
    }
}
