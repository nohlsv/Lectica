<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

    // List all non-admin users
    public function index()
    {
//        if (!Gate::allows('manage-roles')) {
//            abort(403);
//        }
        $users = User::where('user_role', '!=', 'admin')->get(['id', 'first_name', 'last_name', 'email', 'user_role']);
        return response()->json($users);
    }

    // Update a user's role (student <-> faculty)
    public function updateRole(Request $request, User $user)
    {
//        if (!Gate::allows('manage-roles')) {
//            abort(403);
//        }
        if ($user->user_role === 'admin') {
            return response()->json(['error' => 'Cannot change admin role'], 403);
        }
        $role = $request->input('role');
        if (!in_array($role, ['student', 'faculty'])) {
            return response()->json(['error' => 'Invalid role'], 422);
        }
        $user->user_role = $role;
        $user->save();
        return response()->json(['success' => true, 'user' => $user]);
    }

    public function show(Request $request)
    {
        // Only allow admins
//        if (!Gate::allows('manage-roles')) {
//            abort(403);
//        }
        return Inertia::render('AdminUserRoles');
    }
}
