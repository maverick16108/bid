<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('status')) {
            if ($request->status === 'new') {
                $query->where('is_active', false);
            } elseif ($request->status === 'active') {
                $query->where('is_active', true);
            }
        }

        return response()->json($query->orderBy('created_at', 'desc')->paginate(20));
    }

    public function activate($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = true;
        // Optionally assign 'client' role if not set, though migration defaults it.
        $user->save();

        return response()->json(['message' => 'User activated successfully', 'user' => $user]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        // Prevent deleting self or other admins if needed in future
        if ($user->role === 'admin') {
             return response()->json(['message' => 'Cannot delete admin'], 403);
        }
        
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
