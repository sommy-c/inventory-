{{-- resources/views/partials/sidebar.blade.php (or wherever this is) --}}
@php
    use App\Models\Setting;

    // Appearance settings
    $themeMode    = Setting::get('theme_mode', 'dark');        // dark | light
    $sidebarStyle = Setting::get('sidebar_style', 'compact');  // compact | full

    // Logo path saved in DB, e.g. "settings/logo_xxx.png"
    $logoPath = Setting::get('logo_path');

    // Build URL EXACTLY like Branding page does
    $logoUrl = $logoPath
        ? asset('storage/'.$logoPath)    // => /storage/settings/xxx.png
        : asset('images/logo.png');      // fallback
@endphp

<div class="sidebar sidebar-{{ $sidebarStyle }} theme-{{ $themeMode }}" id="sidebar">
    <div class="logo-container">
        <img src="{{ $logoUrl }}"
             alt="Logo"
             style="max-width: 100%; height: 52px; object-fit: contain; display: block; margin: 8px auto;">
    </div>
    {{-- the rest of your links... --}}


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

        @php
            $inventoryActive = request()->is('admin/purchases*')
                || request()->is('admin/damages*')
                || request()->is('admin/orders*');   // orders included

            $settingsActive  = request()->is('admin/settings*');
        @endphp

        {{-- INVENTORY DROPDOWN --}}
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

            {{-- Orders for Admin --}}
            <a href="{{ route('admin.create') }}"
               class="submenu-link {{ request()->routeIs('admin.create') ? 'active' : '' }}">
                Create Order
            </a>

            <a href="{{ route('admin.index') }}"
               class="submenu-link {{ request()->routeIs('admin.index') && !request('status') ? 'active' : '' }}">
                All Orders
            </a>

            <a href="{{ route('admin.index', ['status' => 'waiting']) }}"
               class="submenu-link {{ request()->routeIs('admin.index') && request('status') === 'waiting' ? 'active' : '' }}">
                Waiting Approval
            </a>

            <a href="{{ route('admin.index', ['status' => 'pending']) }}"
               class="submenu-link {{ request()->routeIs('admin.index') && request('status') === 'pending' ? 'active' : '' }}">
                Pending Supply
            </a>

            <a href="{{ route('admin.index', ['status' => 'supplied']) }}"
               class="submenu-link {{ request()->routeIs('admin.index') && request('status') === 'supplied' ? 'active' : '' }}">
                Supplied Orders
            </a>

            <a href="{{ route('admin.damages.index') }}"
               class="submenu-link {{ request()->routeIs('admin.damages.*') ? 'active' : '' }}">
                Damages
            </a>
        </div>

        {{-- SETTINGS DROPDOWN --}}
        <a href="#"
           id="settingsToggle"
           class="has-submenu {{ $settingsActive ? 'active' : '' }}">
            Settings
            <span class="submenu-caret">{{ $settingsActive ? '▾' : '▸' }}</span>
        </a>

        <div id="settingsMenu" class="submenu {{ $settingsActive ? 'open' : '' }}">
            <a href="{{ url('admin/settings/general') }}"
               class="submenu-link {{ request()->is('admin/settings/general') ? 'active' : '' }}">
                General Settings
            </a>

            <a href="{{ url('admin/settings/branding') }}"
               class="submenu-link {{ request()->is('admin/settings/branding') ? 'active' : '' }}">
                Logo & Branding
            </a>

            <a href="{{ url('admin/settings/appearance') }}"
               class="submenu-link {{ request()->is('admin/settings/appearance') ? 'active' : '' }}">
                Theme & Appearance
            </a>

            <a href="{{ url('admin/settings/vat') }}"
               class="submenu-link {{ request()->is('admin/settings/vat') ? 'active' : '' }}">
                Tax / VAT
            </a>

            <a href="{{ url('admin/settings/currency-exchange') }}"
               class="submenu-link {{ request()->is('admin/settings/currency-exchange') ? 'active' : '' }}">
                Currency & Exchange
            </a>

            <a href="{{ url('admin/settings/receipt') }}"
               class="submenu-link {{ request()->is('admin/settings/receipt') ? 'active' : '' }}">
                Receipt / POS
            </a>

            <a href="{{ url('admin/settings/notifications') }}"
               class="submenu-link {{ request()->is('admin/settings/notifications') ? 'active' : '' }}">
                Notifications
            </a>
        </div>
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

        <a href="{{ route('admin.sales.pos') }}" 
           class="{{ request()->routeIs('admin.sales.pos') ? 'active' : '' }}">
           POS
        </a>

        <a href="{{ route('admin.sales.index') }}" 
           class="{{ request()->routeIs('admin.sales.index') ? 'active' : '' }}">
           Sale report
        </a>

        <a href="{{ route('admin.suppliers.index') }}" 
           class="{{ request()->routeIs('admin.suppliers.index') ? 'active' : '' }}">
           Suppliers
        </a>

        <a href="{{ route('admin.users.index') }}"
           class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
           Users
        </a>

        <a href="{{ route('admin.users.create') }}"
           class="{{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
           Create User
        </a>

        @php
            $inventoryActiveManager = request()->routeIs('admin.purchases.*')
                || request()->routeIs('admin.damages.*')
                || request()->routeIs('admin.orders.*');   // include orders
        @endphp

        <a href="#"
           id="inventoryToggle"
           class="has-submenu {{ $inventoryActiveManager ? 'active' : '' }}">
            Inventory
            <span class="submenu-caret">{{ $inventoryActiveManager ? '▾' : '▸' }}</span>
        </a>

        <div id="inventoryMenu" class="submenu {{ $inventoryActiveManager ? 'open' : '' }}">
            <a href="{{ route('admin.purchases.create') }}"
               class="submenu-link {{ request()->routeIs('admin.purchases.create') ? 'active' : '' }}">
                Register Purchase
            </a>

            <a href="{{ route('admin.purchases.index') }}"
               class="submenu-link {{ request()->routeIs('admin.purchases.index') ? 'active' : '' }}">
                See All Purchases
            </a>

            {{-- Orders for Manager --}}
            <a href="{{ route('admin.create') }}"
               class="submenu-link {{ request()->routeIs('admin.create') ? 'active' : '' }}">
                Create Order
            </a>

            <a href="{{ route('admin.index') }}"
               class="submenu-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                My Orders
            </a>

            <a href="{{ route('admin.damages.index') }}"
               class="submenu-link {{ request()->routeIs('admin.damages.*') ? 'active' : '' }}">
                Damages
            </a>
        </div>
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
           POS
        </a>

        <a href="#">Low Stock Alerts</a>
    @endif

    {{-- 🔢 Calculator link (visible for all roles) --}}
    <a href="#" id="calculatorToggle">
        Calculator
    </a>

    {{-- Common Logout --}}
    <a href="#" onclick="event.preventDefault();document.getElementById('logoutForm').submit();">
        Logout
    </a>
</div>

{{-- Calculator Modal --}}
<div id="calcModal" class="calc-modal">
    <div class="calc-modal-content">
        <div class="calc-modal-header">
            <h3>Calculator</h3>
            <button type="button" class="calc-close-btn" id="calcCloseBtn">×</button>
        </div>
        <div class="calc-display">
            <input type="text" id="calcScreen" readonly>
        </div>
        <div class="calc-grid">
            <button data-key="7">7</button>
            <button data-key="8">8</button>
            <button data-key="9">9</button>
            <button data-key="/" class="op">÷</button>

            <button data-key="4">4</button>
            <button data-key="5">5</button>
            <button data-key="6">6</button>
            <button data-key="*" class="op">×</button>

            <button data-key="1">1</button>
            <button data-key="2">2</button>
            <button data-key="3">3</button>
            <button data-key="-" class="op">−</button>

            <button data-key="0">0</button>
            <button data-key="." class="op">.</button>
            <button data-key="C" class="secondary">C</button>
            <button data-key="=" class="primary">=</button>
        </div>
    </div>
</div>

{{-- Calculator styles, theme-aware --}}
<style>
    :root {
        --orange-main: #c05621;
        --orange-strong: #9a3412;
        --orange-light: #f97316;
        --orange-light-hover: #ea580c;
    }

    .calc-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        padding: 24px 12px;
        backdrop-filter: blur(3px);
    }

    /* Dark overlay */
    body.theme-dark .calc-modal {
        background: rgba(15, 23, 42, 0.7);
    }

    /* Light overlay */
    body.theme-light .calc-modal {
        background: rgba(15, 23, 42, 0.25);
    }

    .calc-modal-content {
        max-width: 320px;
        margin: 0 auto;
        border-radius: 16px;
        border: 1px solid;
        padding: 14px 14px 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.7);
    }

    /* Dark body */
    body.theme-dark .calc-modal-content {
        background: #020617;
        border-color: rgba(148, 163, 184, 0.6);
        color: #e5e7eb;
    }

    /* Light body */
    body.theme-light .calc-modal-content {
        background: #ffffff;
        border-color: rgba(209,213,219,0.9);
        color: var(--orange-main);
    }

    .calc-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .calc-modal-header h3 {
        margin: 0;
        font-size: 16px;
    }

    .calc-close-btn {
        border: none;
        background: transparent;
        font-size: 20px;
        cursor: pointer;
        transition: color 0.15s ease;
    }
    body.theme-dark .calc-close-btn {
        color: #9ca3af;
    }
    body.theme-dark .calc-close-btn:hover {
        color: #f9fafb;
    }
    body.theme-light .calc-close-btn {
        color: #9ca3af;
    }
    body.theme-light .calc-close-btn:hover {
        color: #4b5563;
    }

    .calc-display {
        margin-bottom: 10px;
    }
    #calcScreen {
        width: 100%;
        padding: 8px 10px;
        border-radius: 10px;
        border: 1px solid;
        font-size: 16px;
        text-align: right;
    }

    body.theme-dark #calcScreen {
        background: #020617;
        border-color: #334155;
        color: #e5e7eb;
    }
    body.theme-light #calcScreen {
        background: #f9fafb;
        border-color: rgba(209,213,219,0.95);
        color: var(--orange-main);
    }

    .calc-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 6px;
    }

    .calc-grid button {
        border: none;
        border-radius: 10px;
        padding: 8px 0;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.15s ease, transform 0.05s ease;
    }

    /* Base numbers */
    body.theme-dark .calc-grid button {
        background: #0f172a;
        color: #e5e7eb;
    }
    body.theme-light .calc-grid button {
        background: #f3f4f6;
        color: var(--orange-strong);
    }

    .calc-grid button:hover {
        transform: translateY(-1px);
    }
    body.theme-dark .calc-grid button:hover {
        background: #1f2937;
    }
    body.theme-light .calc-grid button:hover {
        background: #e5e7eb;
    }

    /* Operators */
    body.theme-dark .calc-grid button.op {
        background: #1d283a;
    }
    body.theme-light .calc-grid button.op {
        background: #fee2e2;
        color: #b91c1c;
    }

    /* Clear */
    body.theme-dark .calc-grid button.secondary {
        background: #4b5563;
        color: #e5e7eb;
    }
    body.theme-light .calc-grid button.secondary {
        background: #fee2e2;
        color: #b91c1c;
    }

    /* Equals */
    body.theme-dark .calc-grid button.primary {
        background: #2563eb;
        color: #eff6ff;
    }
    body.theme-dark .calc-grid button.primary:hover {
        background: #1d4ed8;
    }

    body.theme-light .calc-grid button.primary {
        background: var(--orange-light);
        color: #fff7ed;
    }
    body.theme-light .calc-grid button.primary:hover {
        background: var(--orange-light-hover);
    }
   
:root {
    --orange-main: #c05621;
    --orange-strong: #9a3412;
    --orange-light: #f97316;
    --orange-light-hover: #ea580c;
}

/* BASE SIDEBAR LINK STYLE */
.sidebar a {
    display: block;
    padding: 8px 14px;
    border-radius: 999px;
    margin: 3px 10px;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.15s ease, color 0.15s ease, transform 0.08s ease;
}

/* Submenu links a bit smaller and indented */
.sidebar .submenu-link {
    padding-left: 26px;
    font-size: 0.85rem;
}

/* ---------- DARK THEME ---------- */
.sidebar.theme-dark a {
    color: #e5e7eb;
    background: transparent;
}

.sidebar.theme-dark a:hover {
    background: rgba(31, 41, 55, 0.95);
    color: #f9fafb;
    transform: translateX(1px);
}

/* active main links (Dashboard / Products / etc.) */
.sidebar.theme-dark a.active {
    background: rgba(37, 99, 235, 0.18);
    color: #bfdbfe;
}

/* active submenu links */
.sidebar.theme-dark .submenu-link.active {
    background: rgba(37, 99, 235, 0.35);
    color: #eff6ff;
}

/* dropdown parent (Inventory / Settings) when active */
.sidebar.theme-dark .has-submenu.active {
    background: rgba(37, 99, 235, 0.25);
    color: #eff6ff;
}

/* caret in dark theme */
.sidebar.theme-dark .submenu-caret {
    float: right;
    font-size: 0.8rem;
    color: #9ca3af;
}
.sidebar.theme-dark .has-submenu.active .submenu-caret {
    color: #bfdbfe;
}

/* ---------- LIGHT THEME ---------- */
.sidebar.theme-light a {
    color: var(--orange-main);
    background: transparent;
}

.sidebar.theme-light a:hover {
    background: rgba(255, 247, 237, 0.9);
    color: var(--orange-strong);
    transform: translateX(1px);
}

/* active main links (Dashboard / Products / etc.) */
.sidebar.theme-light a.active {
    background: rgba(249, 115, 22, 0.16);      /* orange glow */
    color: var(--orange-strong);
    box-shadow: 0 0 0 1px rgba(249,115,22,0.35);
}

/* active submenu links */
.sidebar.theme-light .submenu-link.active {
    background: rgba(249, 115, 22, 0.28);
    color: #7c2d12;
    box-shadow: 0 0 0 1px rgba(248,171,79,0.5);
}

/* dropdown parent (Inventory / Settings) when active */
.sidebar.theme-light .has-submenu.active {
    background: rgba(254, 243, 199, 0.95);
    color: var(--orange-strong);
    box-shadow: inset 0 0 0 1px rgba(251, 191, 36, 0.55);
}

/* caret in light theme */
.sidebar.theme-light .submenu-caret {
    float: right;
    font-size: 0.8rem;
    color: #9ca3af;
}
.sidebar.theme-light .has-submenu.active .submenu-caret {
    color: var(--orange-light);
}

/* ---------- SUBMENU CONTAINER ---------- */
.sidebar {
    max-height: 100vh;
    overflow-y: auto;
}

.sidebar .submenu {
    display: none;
    margin-top: 2px;
}

.sidebar .submenu.open {
    display: block;
}


/* tiny tweak so submenu doesn't look cramped */
.sidebar .submenu a {
    margin: 2px 10px;
}

/* optional: slight divider look for inventory/settings groups */
.sidebar .has-submenu {
    margin-top: 6px;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function setupToggle(toggleId, menuId) {
        const toggle = document.getElementById(toggleId);
        const menu   = document.getElementById(menuId);

        if (!toggle || !menu) return;

        toggle.addEventListener('click', function (e) {
            e.preventDefault();

            const isOpen = menu.classList.contains('open');
            menu.classList.toggle('open', !isOpen);

            const caret = toggle.querySelector('.submenu-caret');
            if (caret) {
                caret.textContent = isOpen ? '▸' : '▾';
            }
        });
    }

    // Works for whichever role is currently rendered
    setupToggle('inventoryToggle', 'inventoryMenu');
    setupToggle('settingsToggle', 'settingsMenu');

    // === CALCULATOR LOGIC ===
    const calcToggle   = document.getElementById('calculatorToggle');
    const calcModal    = document.getElementById('calcModal');
    const calcCloseBtn = document.getElementById('calcCloseBtn');
    const calcScreen   = document.getElementById('calcScreen');
    const calcButtons  = calcModal ? calcModal.querySelectorAll('button[data-key]') : [];

    let calcExpr = '';

    function openCalcModal() {
        if (!calcModal) return;
        calcModal.style.display = 'block';
        calcScreen.value = calcExpr || '';
    }

    function closeCalcModal() {
        if (!calcModal) return;
        calcModal.style.display = 'none';
    }

    if (calcToggle) {
        calcToggle.addEventListener('click', function (e) {
            e.preventDefault();
            openCalcModal();
        });
    }

    if (calcCloseBtn) {
        calcCloseBtn.addEventListener('click', function () {
            closeCalcModal();
        });
    }

    if (calcModal) {
        calcModal.addEventListener('click', function (e) {
            if (e.target === calcModal) {
                closeCalcModal();
            }
        });
    }

    calcButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const key = this.dataset.key;

            if (key === 'C') {
                calcExpr = '';
                calcScreen.value = '';
                return;
            }

            if (key === '=') {
                if (!calcExpr) return;
                try {
                    const result = Function('"use strict"; return (' + calcExpr + ')')();
                    calcExpr = result.toString();
                    calcScreen.value = calcExpr;
                } catch (e) {
                    calcScreen.value = 'Error';
                    calcExpr = '';
                }
                return;
            }

            // append digit or operator
            calcExpr += key;
            calcScreen.value = calcExpr;
        });
    });
});
</script>
@endpush
