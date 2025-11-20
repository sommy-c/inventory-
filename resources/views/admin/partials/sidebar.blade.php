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

        <a href="{{ route('admin.products') }}" 
           class="{{ request()->routeIs('admin.products') ? 'active' : '' }}">
           Products
        </a>

        <a href="{{ route('admin.suppliers.index') }}" 
           class="{{ request()->routeIs('admin.suppliers.index') ? 'active' : '' }}">
           Suppliers
        </a>

        <a href="{{ route('admin.sales.pos') }}" 
           class="{{ request()->routeIs('admin.sales.pos') ? 'active' : '' }}">
           POS
        </a>

        <a href="{{ route('admin.sales.index') }}" 
           class="{{ request()->routeIs('admin.sales.index') ? 'active' : '' }}">
           Sale report
        </a>

        <a href="{{ route('admin.customers.index') }}" 
           class="{{ request()->routeIs('admin.customers.index') ? 'active' : '' }}">
           Customer
        </a>

        <a href="{{ route('admin.users.index') }}"
           class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
           Users
        </a>

        <a href="{{ route('admin.users.create') }}"
           class="{{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
           Create User
        </a>

        {{-- ================= INVENTORY DROPDOWN ================= --}}
        @php
            $inventoryActive = request()->routeIs('admin.purchases.*');
        @endphp

        <a href="#"
           id="inventoryToggle"
           class="has-submenu {{ $inventoryActive ? 'active' : '' }}">
            Inventory
            <span class="submenu-caret">{{ $inventoryActive ? '▾' : '▸' }}</span>
        </a>

        <div id="inventoryMenu" class="submenu {{ $inventoryActive ? 'open' : '' }}">
            <a href="{{ route('admin.purchases.create') }}"
               class="submenu-link {{ request()->routeIs('admin.purchases.create') ? 'active' : '' }}">
                Register Purchase
            </a>

            <a href="{{ route('admin.purchases.index') }}"
               class="submenu-link {{ request()->routeIs('admin.purchases.index') ? 'active' : '' }}">
                See All Purchases
            </a>
        </div>
        {{-- ====================================================== --}}

        <a href="#">Settings</a>
    @endif
</div>





    {{-- Manager Links --}}
    @if(auth()->user()->hasRole('manager'))
        <a href="{{ route('manager.dashboard') }}" 
           class="{{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
           Dashboard
        </a>

        <a href="{{ route('admin.products') }}" 
           class="{{ request()->routeIs('admin.products') ? 'active' : '' }}">
           Products
        </a>

        <a href="#">Inventory</a>
        <a href="{{ route('admin.sales.pos') }}" 
        class="{{ request()->routeIs('admin.sales.pos') ? 'active' : '' }}">
    POS</a>

       <a href="{{ route('admin.sales.index') }}" 
           class="{{ request()->routeIs('admin.sales.index') ? 'active' : '' }}">
           Products
        </a>

         <a href="{{ route('admin.suppliers.index') }}" 
           class="{{ request()->routeIs('admin.suppliers.index') ? 'active' : '' }}">
          Suppliers
        </a>
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

         <a href="{{ route('admin.sales.pos') }}" 
        class="{{ request()->routeIs('admin.sales.pos') ? 'active' : '' }}">
    POS</a>

        <a href="#">Low Stock Alerts</a>
    @endif

    

    <a href="#" onclick="document.getElementById('logoutForm').submit()">Logout</a>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('inventoryToggle');
    const menu   = document.getElementById('inventoryMenu');

    if (toggle && menu) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            menu.classList.toggle('open');

            const caret = this.querySelector('.submenu-caret');
            if (caret) {
                caret.textContent = menu.classList.contains('open') ? '▾' : '▸';
            }
        });
    }
});
</script>
@endpush
