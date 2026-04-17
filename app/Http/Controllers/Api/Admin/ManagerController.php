<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManagerController extends Controller
{
    /**
     * Display a listing of branch managers.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'branch_admin');
        
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
            });
        }
        
        return response()->json($query->paginate(10));
    }

    /**
     * Store a newly created branch manager in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'branch_admin',
            'is_active' => true,
        ]);

        return response()->json($user, 201);
    }

    /**
     * Update the specified manager.
     */
    public function update(Request $request, $id)
    {
        $user = User::where('role', 'branch_admin')->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'sometimes|required|string|min:6',
        ]);

        $data = $request->only(['name', 'email', 'is_active']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json($user);
    }

    /**
     * Remove the specified manager from storage.
     */
    public function destroy($id)
    {
        $user = User::where('role', 'branch_admin')->findOrFail($id);
        
        if ($user->branches()->count() > 0) {
            return response()->json(['error' => 'Không thể xóa quản lý đang được phân công cho chi nhánh.'], 400);
        }
        
        $user->delete();
        return response()->json(['message' => 'Xóa tài khoản quản lý thành công']);
    }
}
