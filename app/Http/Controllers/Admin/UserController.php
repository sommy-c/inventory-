<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Show the login page
     */
    public function showLoginForm()
    {
        return view('auth.login'); // resources/views/auth/login.blade.php
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            return $this->redirectUserByRole($user);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully.');
    }

    /**
     * Redirect user based on role
     */
  protected function redirectUserByRole(User $user)
{
    // Get the first role assigned to the user
    $role = $user->roles->pluck('name')->first();

    return redirect()->route('dashboard')
                     ->with('success', 'Welcome ' . ucfirst($role) . '!');
}


    /**
     * Display all users (Admin only)
     * 
     * 
     * 
     */



     public function dashboard()
{
    return view('dashboard');
}

    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show form to create a new user (Admin only)
     */
    public function create()
    {
        // Only allow creating manager or cashier roles
        $roles = Role::whereIn('name', ['manager', 'cashier'])->get();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a new user (Admin only)
     */public function store(Request $request)
{
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
        'role'     => 'required|exists:roles,name',
        'gender'   => 'required|in:male,female,other', // ✅ new validation
    ]);

    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => Hash::make($request->password),
        'gender'   => $request->gender, // ✅ store gender
    ]);

    $user->assignRole($request->role);

    return redirect()->route('admin.users.index')
                     ->with('success', 'User created successfully.');
}


/**
 * Show edit user form
 */
public function edit(User $user)
{
    $roles = Role::whereIn('name', ['manager', 'cashier'])->get();
    return view('admin.users.edit', compact('user', 'roles'));
}

/**
 * Update user
 */
public function update(Request $request, User $user)
{
    $request->validate([
        'name'   => 'required|string|max:255',
        'email'  => 'required|email|unique:users,email,' . $user->id,
        'role'   => 'required|exists:roles,name',
        'gender' => 'required|in:male,female,other',
        'password' => 'nullable|string|min:6|confirmed',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;
    $user->gender = $request->gender;

    if($request->filled('password')){
        $user->password = Hash::make($request->password);
    }

    $user->save();

    // Sync role
    $user->syncRoles($request->role);

    return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
}

/**
 * Delete user
 */
public function destroy(User $user)
{
    $user->delete();
    return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
}


}
