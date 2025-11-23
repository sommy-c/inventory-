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
                            <form action="{{ route('admin.users.destroy', $user->id) }}"
                                  method="POST"
                                  class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="delete-btn"
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

<!-- THEME-AWARE USERS PAGE STYLES -->
<style>
:root {
    --orange-main: #c05621;
    --orange-strong: #9a3412;
    --orange-light: #f97316;
    --orange-light-hover: #ea580c;
    --border-soft: rgba(192,132,45,0.35);
    --muted-text: #7c2d12;
}

/* ---------- CONTAINER ---------- */
.users-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 16px 32px;
    min-height: 100vh;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

/* Dark theme text */
body.theme-dark .users-container {
    color: #f9fafb;
}

/* Light theme text */
body.theme-light .users-container {
    color: var(--orange-main);
}

.page-title {
    font-size: 26px;
    font-weight: 600;
    margin-bottom: 20px;
}

/* ---------- TABLE WRAPPER ---------- */
.users-container .table-wrapper {
    overflow-x: auto;
    border-radius: 12px;
}

/* dark */
body.theme-dark .users-container .table-wrapper {
    background: rgba(15, 23, 42, 0.9);
    border: 1px solid rgba(31, 41, 55, 1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.55);
}

/* light */
body.theme-light .users-container .table-wrapper {
    background: rgba(255,255,255,0.9);
    border: 1px solid var(--border-soft);
    box-shadow: 0 10px 24px rgba(0,0,0,0.06);
}

/* ---------- TABLE ---------- */
.users-container table {
    width: 100%;
    border-collapse: collapse;
    min-width: 680px;
}

/* header */
.users-container thead {
    background: linear-gradient(90deg, #1d4ed8, #2563eb);
}

/* light header tweak */
body.theme-light .users-container thead {
    background: linear-gradient(90deg, #fed7aa, #fdba74);
}

.users-container thead th {
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-bottom: 1px solid rgba(148, 163, 184, 0.4);
}

/* dark header text */
body.theme-dark .users-container thead th {
    color: #e5e7eb;
}

/* light header text */
body.theme-light .users-container thead th {
    color: var(--orange-strong);
}

/* rows */
.users-container tbody tr {
    transition: background 0.2s ease;
}

/* dark stripes */
body.theme-dark .users-container tbody tr {
    border-bottom: 1px solid rgba(55, 65, 81, 0.85);
}
body.theme-dark .users-container tbody tr:nth-child(even) {
    background: rgba(15, 23, 42, 0.92);
}
body.theme-dark .users-container tbody tr:nth-child(odd) {
    background: rgba(15, 23, 42, 0.98);
}
body.theme-dark .users-container tbody tr:hover {
    background: rgba(30, 64, 175, 0.35);
}

/* light stripes */
body.theme-light .users-container tbody tr {
    border-bottom: 1px solid rgba(229,231,235,0.9);
}
body.theme-light .users-container tbody tr:nth-child(even) {
    background: rgba(255,255,255,0.98);
}
body.theme-light .users-container tbody tr:nth-child(odd) {
    background: rgba(255,255,255,0.93);
}
body.theme-light .users-container tbody tr:hover {
    background: rgba(254,243,199,0.85);
}

/* cells */
.users-container tbody td {
    padding: 12px 15px;
    font-size: 0.9rem;
}

/* dark text */
body.theme-dark .users-container tbody td {
    color: #e5e7eb;
}

/* light text */
body.theme-light .users-container tbody td {
    color: var(--orange-main);
}

/* ---------- BUTTONS ---------- */
.edit-btn,
.delete-btn,
.create-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 12px;
    margin-right: 5px;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 999px;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.1s ease,
                border-color 0.15s ease, box-shadow 0.15s ease;
}

/* Edit */
body.theme-dark .edit-btn {
    background: rgba(16, 185, 129, 0.18);
    color: #6ee7b7;
    border-color: rgba(16, 185, 129, 0.8);
}
body.theme-dark .edit-btn:hover {
    background: rgba(16, 185, 129, 0.35);
    transform: translateY(-1px);
}

body.theme-light .edit-btn {
    background: rgba(22,163,74,0.08);
    color: #166534;
    border-color: rgba(22,163,74,0.7);
}
body.theme-light .edit-btn:hover {
    background: rgba(22,163,74,0.22);
    transform: translateY(-1px);
}

/* Delete */
body.theme-dark .delete-btn {
    background: rgba(239,68,68,0.15);
    color: #fecaca;
    border-color: rgba(239,68,68,0.85);
}
body.theme-dark .delete-btn:hover {
    background: rgba(239,68,68,0.32);
    transform: translateY(-1px);
}

body.theme-light .delete-btn {
    background: rgba(248,113,113,0.08);
    color: #b91c1c;
    border-color: rgba(239,68,68,0.85);
}
body.theme-light .delete-btn:hover {
    background: rgba(248,113,113,0.22);
    transform: translateY(-1px);
}

/* Create button */
.create-btn {
    margin-top: 20px;
    padding: 10px 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

/* dark: blue */
body.theme-dark .create-btn {
    background: rgba(37, 99, 235, 0.9);
    color: #fff;
    border-color: rgba(37, 99, 235, 1);
}
body.theme-dark .create-btn:hover {
    background: rgba(37, 99, 235, 1);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37,99,235,0.45);
}

/* light: orange */
body.theme-light .create-btn {
    background: var(--orange-light);
    color: #fff;
    border-color: var(--orange-light-hover);
    box-shadow: 0 6px 16px rgba(248,148,6,0.28);
}
body.theme-light .create-btn:hover {
    background: var(--orange-light-hover);
    transform: translateY(-2px);
}

/* wrapper for create button */
.button-wrapper {
    margin-top: 20px;
    text-align: center;
}

/* delete form inline */
.delete-form {
    display: inline;
}

/* ---------- RESPONSIVE ---------- */
@media (max-width: 768px) {
    .page-title {
        font-size: 22px;
    }
    .users-container table {
        min-width: 600px;
    }
    .edit-btn,
    .delete-btn,
    .create-btn {
        padding: 5px 10px;
        font-size: 0.8rem;
    }
}

@media (max-width: 480px) {
    .users-container table thead {
        display: none;
    }
    .users-container table,
    .users-container table tbody,
    .users-container table tr,
    .users-container table td {
        display: block;
        width: 100%;
    }
    .users-container table tr {
        margin-bottom: 10px;
        border-bottom: 1px solid rgba(148,163,184,0.4);
    }
    .users-container table td {
        text-align: right;
        padding-left: 45%;
        position: relative;
        font-size: 0.85rem;
    }

    /* dark mobile text */
    body.theme-dark .users-container table td {
        color: #e5e7eb;
    }

    /* light mobile text */
    body.theme-light .users-container table td {
        color: var(--orange-main);
    }

    .users-container table td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        width: 40%;
        padding-left: 5px;
        font-weight: 600;
        text-align: left;
        font-size: 0.8rem;
        opacity: 0.85;
    }

    .button-wrapper {
        margin-top: 15px;
    }
}
</style>
@endsection
