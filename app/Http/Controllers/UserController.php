<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $pageTitle = 'Users List';
        $users     = User::all();
        return view('users.index', [
            'pageTitle' => $pageTitle,
            'users'     => $users,
        ]);
    }

    public function editRole($id)
    {
        $pageTitle = 'Edit User Role';
        $user      = User::findOrFail($id);
        $roles     = Role::all();

        return view('users.edit_role', [
            'pageTitle' => $pageTitle,
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function updateRole($id, Request $request)
    {
        $user = User::findOrFail($id);
        $user->update([
            'role_id' => $request->role_id,
        ]);

        return redirect()->route('users.index');
    }
}
