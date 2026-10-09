<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => 'required|in:admin,pembina,siswa',
        ]);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Kamu tidak bisa mengubah role akunmu sendiri.');
        }

        $user->update(['role' => $data['role']]);

        return back()->with('success', "Role {$user->name} diubah menjadi {$data['role']}.");
    }
}