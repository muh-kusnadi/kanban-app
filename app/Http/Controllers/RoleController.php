<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    public function index()
    {
        $pageTitle = 'Role Lists';
        $roles     = Role::all();

        return view('roles.index', [
            'pageTitle' => $pageTitle,
            'roles'     => $roles,
        ]);
    }

    public function create()
    {
        $pageTitle   = 'Add Role';
        $permissions = Permission::all();
        return view('roles.create', [
            'pageTitle' => $pageTitle,
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'permissionIds' => ['required'],
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $request->name,
            ]);

            $role->permissions()->sync($request->permissionIds);

            DB::commit();

            return redirect()->route('roles.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function edit($id)
    {
        Gate::authorize('manageRoles', Role::class);

        $pageTitle = 'Edit Role';
        $role      = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all();

        return view('roles.edit', [
            'role'        => $role,
            'pageTitle'   => $pageTitle,
            'permissions' => $permissions
        ]);
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('manageRoles', Role::class);

        $request->validate([
            'name'          => ['required'],
            'permissionIds' => ['required'],
        ]);

        $role = Role::with('permissions')->findOrFail($id);

        DB::beginTransaction();
        try {
            $role->update([
                'name' => $request->name,
            ]);

            $role->permissions()->sync($request->permissionIds);

            DB::commit();

            return redirect()->route('roles.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete($id)
    {
        Gate::authorize('manageRoles', Role::class);

        $pageTitle = 'Delete Role';
        $role      = Role::findOrFail($id);

        return view('roles.delete', [
            'role'        => $role,
            'pageTitle'   => $pageTitle,
        ]);
    }

    public function destroy($id)
    {
        Gate::authorize('manageRoles', Role::class);

        $role = Role::with('permissions')->findOrFail($id);

        DB::beginTransaction();
        try {

            $role->permissions()->detach();
            $role->delete();

            DB::commit();
            return redirect()->route('roles.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
