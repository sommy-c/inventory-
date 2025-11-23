@php
    $authUser     = auth()->user();
    $isEditingSelf = $authUser->id === $user->id;
    $editingAdmin  = $user->hasRole('admin');
@endphp
@extends('admin.layout')
@section('title','Edit User')

@section('content')
<div class="form-container">
    <h3 class="page-title">Edit User</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-card">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="form-group">
                <label>Gender</label>
                <select name="gender" required>
                    <option value="male" {{ $user->gender == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $user->gender == 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ $user->gender == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label>Password <small>(leave blank to keep current)</small></label>
                <input type="password" name="password">
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation">
            </div>

           {{-- ROLE FIELD --}}
@if($authUser->hasRole('admin'))

    {{-- Admin editing themselves: show role but do NOT allow change --}}
    @if($isEditingSelf)
        <div class="form-group">
            <label>Role</label>
            <input type="text"
                   value="{{ ucfirst($user->roles->first()->name ?? 'admin') }}"
                   disabled>
            <small style="font-size:12px; color:#9ca3af;">
                You cannot change your own role.
            </small>
        </div>
    @else
        {{-- Admin editing someone else: full dropdown of roles --}}
        <div class="form-group">
            <label>Role</label>
            <select name="role" required>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}"
                        {{ $user->roles->contains('name', $role->name) ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

@elseif($authUser->hasRole('manager'))

    {{-- Manager should never see this for admins, controller already aborts,
         but this is an extra safety / UX guard. --}}
    @if($editingAdmin)
        <div class="form-group">
            <label>Role</label>
            <input type="text"
                   value="Admin"
                   disabled>
            <small style="font-size:12px; color:#fca5a5;">
                Managers cannot edit admin users.
            </small>
        </div>
    @else
        {{-- Manager editing non-admin: dropdown WITHOUT "admin" option --}}
        <div class="form-group">
            <label>Role</label>
            <select name="role" required>
                @foreach($roles as $role)
                    @if($role->name === 'admin')
                        @continue   {{-- manager shouldn't even see admin --}}
                    @endif
                    <option value="{{ $role->name }}"
                        {{ $user->roles->contains('name', $role->name) ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

@else
    {{-- Other roles: role is read-only --}}
    <div class="form-group">
        <label>Role</label>
        <input type="text"
               value="{{ ucfirst($user->roles->first()->name ?? 'N/A') }}"
               disabled>
    </div>
@endif


            <button type="submit" class="submit-btn">Update User</button>
        </form>
    </div>
</div>

<style>
.form-container {
    padding: 20px;
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: rgba(255,255,255,0.05);
    color: #fff;
}

.page-title {
    font-size: 28px;
    font-weight: 600;
    margin-bottom: 20px;
}

.form-card {
    background-color: rgba(255,255,255,0.1);
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    max-width: 600px;
    margin: auto;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.3);
    background-color: rgba(255,255,255,0.05);
    color: #fff;
}

.submit-btn {
    padding: 12px 24px;
    background-color: rgba(37, 99, 235, 0.8);
    color: #fff;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
}

.submit-btn:hover {
    background-color: rgba(37, 99, 235, 1);
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 480px) {
    .form-card { padding: 15px; }
}
</style>
@endsection
