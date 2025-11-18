<div class="sidebar" id="sidebar">
    <div class="logo-container">
        <img src="{{ asset('images/logo.png') }}" alt="Logo">
    </div>
    <h2>IMS</h2>

    {{-- Admin Links --}}
    @if(auth()->user()->hasRole('admin'))
        <a href="{{ route('admin.dashboard') }}" 
           class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
           Dashboard
        </a>

        <a href="#" class="">Inventory</a>

        <a href="{{ route('admin.products') }}" 
           class="{{ request()->routeIs('admin.products') ? 'active' : '' }}">
           Products
        </a>

        <a href="#">Suppliers</a>

        <a href="{{ route('admin.sales.pos') }}" 
        class="{{ request()->routeIs('admin.sales.pos') ? 'active' : '' }}">
    POS</a>


        <a href="#">Sales</a>
        <a href="#">Reports</a>
        <a href="#">Notifications</a>
        <a href="#">Settings</a>

        <a href="{{ route('admin.users.index') }}"
           class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
           Users
        </a>

        <a href="{{ route('admin.users.create') }}"
           class="{{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
           Create User
        </a>
    @endif

    {{-- Manager Links --}}
    @if(auth()->user()->hasRole('manager'))
        <a href="{{ route('admin.dashboard') }}" 
           class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
           Dashboard
        </a>

        <a href="{{ route('admin.products') }}" 
           class="{{ request()->routeIs('admin.products') ? 'active' : '' }}">
           Products
        </a>

        <a href="#">Inventory</a>
        <a href="#">POS</a>
        <a href="#">Sales</a>
        <a href="#">Reports</a>
    @endif

    {{-- Cashier Links --}}
    @if(auth()->user()->hasRole('cashier'))
        <a href="{{ route('cashier.dashboard') }}"
           class="{{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}">
           Dashboard
        </a>

        <a href="{{ route('cashier.products') }}"
           class="{{ request()->routeIs('cashier.products') ? 'active' : '' }}">
           Products
        </a>

        <a href="#">POS</a>
        <a href="#">Low Stock Alerts</a>
    @endif

    <div class="quick-pos" onclick="alert('Redirect to POS Page')">Quick POS</div>

    <a href="#" onclick="document.getElementById('logoutForm').submit()">Logout</a>
</div>
