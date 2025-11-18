@extends('admin.layout')
@section('title','Users')

@section('content')
<div class="users-container">
    <h3 class="page-title">Users</h3>

    <!-- Users Table -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td data-label="Name">{{ $user->name }}</td>
                    <td data-label="Email">{{ $user->email }}</td>
                    <td data-label="Role">{{ $user->roles->pluck('name')->join(', ') }}</td>
                    <td data-label="Actions">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="edit-btn">Edit</a>
                        @if(auth()->user()->hasRole('admin')) 
                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="delete-btn"
                            onclick="return confirm('Are you sure you want to delete this user?');">
                        Delete
                    </button>
                </form>
            @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Create User Button -->
    <div class="button-wrapper">
        <a href="{{ route('admin.users.create') }}" class="create-btn">+ Create User</a>
    </div>
</div>

<!-- Vanilla CSS with Transparent Theme -->
<style>
.users-container {
    padding: 20px;
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: rgba(255, 255, 255, 0.05);
    color: #fff;
}

.page-title {
    font-size: 28px;
    font-weight: 600;
    margin-bottom: 20px;
}

.table-wrapper {
    overflow-x: auto;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background-color: rgba(37, 99, 235, 0.8);
    color: #fff;
}

thead th {
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
}

tbody tr {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    transition: background 0.2s ease;
}

tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

tbody td {
    padding: 12px 15px;
}

.edit-btn, .delete-btn {
    display: inline-block;
    padding: 6px 12px;
    margin-right: 5px;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 6px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
}

.edit-btn {
    background-color: rgba(16, 185, 129, 0.8); /* green */
    color: #fff;
}

.edit-btn:hover {
    background-color: rgba(16, 185, 129, 1);
    transform: translateY(-1px);
}

.delete-btn {
    background-color: rgba(239, 68, 68, 0.8); /* red */
    color: #fff;
}

.delete-btn:hover {
    background-color: rgba(239, 68, 68, 1);
    transform: translateY(-1px);
}

.button-wrapper {
    margin-top: 20px;
    text-align: center;
}

.create-btn {
    display: inline-block;
    padding: 12px 24px;
    background-color: rgba(37, 99, 235, 0.8);
    color: #fff;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    transition: background 0.3s ease, transform 0.2s ease;
}

.create-btn:hover {
    background-color: rgba(37, 99, 235, 1);
    transform: translateY(-2px);
}

/* Responsive tweaks */
@media (max-width: 768px) {
    .page-title { font-size: 22px; }
    table thead th, table tbody td { padding: 8px 10px; }
    .edit-btn, .delete-btn, .create-btn { padding: 5px 10px; font-size: 0.85rem; }
}

@media (max-width: 480px) {
    table thead { display: none; }
    table, table tbody, table tr, table td { display: block; width: 100%; }
    table tr { margin-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.2); }
    table td {
        text-align: right;
        padding-left: 45%;
        position: relative;
        color: #fff;
        font-size: 0.85rem;
    }
    table td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        width: 40%;
        padding-left: 5px;
        font-weight: 600;
        text-align: left;
    }
    .edit-btn, .delete-btn, .create-btn { padding: 4px 8px; font-size: 0.8rem; }
    .button-wrapper { text-align: center; margin-top: 15px; }
}

.delete-form { display: inline; }
</style>
@endsection
