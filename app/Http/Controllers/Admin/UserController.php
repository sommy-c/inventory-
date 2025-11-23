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

    return redirect()->route('admin.dashboard')
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
    $authUser = Auth::user();

    $users = User::query()
        ->when($authUser->hasRole('manager'), function ($q) {
            $q->whereDoesntHave('roles', function ($qr) {
                $qr->where('name', 'admin');
            });
        })
        ->with('roles')
        ->paginate(20);

    $roles = Role::all();

    return view('admin.users.index', compact('users', 'roles'));
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
    $authUser = Auth::user();
    $isEditingSelf = $authUser->id === $user->id;

    // 🔒 Managers cannot edit admin users at all
    if ($authUser->hasRole('manager') && $user->hasRole('admin')) {
        abort(403, 'Managers cannot edit admin users.');
    }

    // ----- VALIDATION RULES -----
    $rules = [
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email,' . $user->id,
        'gender'   => 'required|in:male,female,other',
        'password' => 'nullable|string|min:6|confirmed',
    ];

    // Admin/Manager can see a role field, but:
    // - admin editing self: ignore role change
    // - manager: cannot assign admin later in logic
    if ($authUser->hasRole('admin') || $authUser->hasRole('manager')) {
        // We still validate, but will ignore in some cases below
        $rules['role'] = 'required|exists:roles,name';
    }

    $data = $request->validate($rules);

    // ----- BASIC FIELDS -----
    $user->name   = $data['name'];
    $user->email  = $data['email'];
    $user->gender = $data['gender'];

    if (!empty($data['password'])) {
        $user->password = Hash::make($data['password']);
    }

    $user->save();

    // ----- ROLE LOGIC -----

    // 1) ADMIN
    if ($authUser->hasRole('admin')) {

        // Admin editing self: DO NOT change own role
        if ($isEditingSelf) {
            // do nothing to roles
        } else {
            // Admin editing someone else → can set any role
            if (isset($data['role'])) {
                $user->syncRoles($data['role']);
            }
        }

    // 2) MANAGER
    } elseif ($authUser->hasRole('manager')) {

        // At this point we already ensured $user is NOT admin

        $newRole = $data['role'] ?? null;

        // Manager cannot assign admin role to anyone
        if ($newRole === 'admin') {
            return back()
                ->withInput()
                ->with('error', 'Managers cannot assign the admin role.');
        }

        if ($newRole) {
            $user->syncRoles($newRole);
        }
    }

    // Other roles: no role changing

    return redirect()
        ->route('admin.users.index')
        ->with('success', 'User updated successfully.');
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
