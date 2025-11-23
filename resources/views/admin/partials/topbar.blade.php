<div class="topbar">
    <div class="topbar-left">
        <button class="topbar-menu-btn" onclick="toggleSidebar()">☰</button>

        <!--
        <div class="topbar-brand">
            <span class="brand-name">
                {{ $topbarStoreName ?? config('app.name', 'IMS') }}
            </span>
            <span class="brand-sub">
                Inventory Management
            </span>
        </div>
        -->
    </div>

    <div class="topbar-right">
        {{-- 🔔 BELL WITH REAL COUNT (low + out + expiring + expired) --}}
        <div class="notifications" onclick="openStockModal()">
            <span class="notif-icon">🔔</span>

            @if(($totalAlerts ?? 0) > 0)
                <span class="notif-count">
                    {{ $totalAlerts }}
                </span>
            @endif
        </div>

        <div class="topbar-user">
            <div class="user-meta">
                <span class="user-name">{{ auth()->user()->name }}</span>
                <span class="user-role">
                    {{ ucfirst(auth()->user()->roles->first()->name ?? '') }}
                </span>
            </div>
        </div>
    </div>
</div>

<form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">
    @csrf
</form>

<!-- STOCK ALERT MODAL -->
<div id="stockModal" class="stock-modal">
    <div class="stock-modal-content">
        <div class="stock-modal-header">
            <div>
                <h3>📦 Stock Alerts</h3>
                <p class="stock-modal-subtitle">
                    Monitor items that need your attention.
                </p>
            </div>
            <button type="button" class="stock-modal-close" onclick="closeStockModal()">×</button>
        </div>

        <div class="stock-modal-grid">
            {{-- Low stock --}}
            <div class="stock-section stock-section-low">
                <div class="stock-section-header">
                    <h4>⚠ Low Stock</h4>
                    <span class="stock-badge">{{ $lowStock->count() }}</span>
                </div>
                <ul class="stock-list">
                    @forelse($lowStock as $item)
                        <li class="stock-item">
                            <a href="{{ route('admin.products.show', $item->id) }}">
                                <div class="stock-item-main">
                                    <span class="stock-item-name">{{ $item->name }}</span>
                                    @if($item->sku)
                                        <span class="stock-item-sku">({{ $item->sku }})</span>
                                    @endif
                                </div>
                                <div class="stock-item-meta">
                                    <span class="stock-pill stock-pill-warning">
                                        {{ $item->quantity }} left
                                    </span>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="empty-text">No low stock items.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Out of stock --}}
            <div class="stock-section stock-section-out">
                <div class="stock-section-header">
                    <h4>❌ Out of Stock</h4>
                    <span class="stock-badge">{{ $outOfStock->count() }}</span>
                </div>
                <ul class="stock-list">
                    @forelse($outOfStock as $item)
                        <li class="stock-item">
                            <a href="{{ route('admin.products.show', $item->id) }}">
                                <div class="stock-item-main">
                                    <span class="stock-item-name">{{ $item->name }}</span>
                                    @if($item->sku)
                                        <span class="stock-item-sku">({{ $item->sku }})</span>
                                    @endif
                                </div>
                                <div class="stock-item-meta">
                                    <span class="stock-pill stock-pill-danger">
                                        0 left
                                    </span>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="empty-text">No out of stock items.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Expiring soon --}}
            <div class="stock-section stock-section-expiring">
                <div class="stock-section-header">
                    <h4>⏰ Expiring Soon (7 days)</h4>
                    <span class="stock-badge">{{ $expiringSoon->count() }}</span>
                </div>
                <ul class="stock-list">
                    @forelse($expiringSoon as $item)
                        <li class="stock-item">
                            <a href="{{ route('admin.products.show', $item->id) }}">
                                <div class="stock-item-main">
                                    <span class="stock-item-name">{{ $item->name }}</span>
                                    @if($item->sku)
                                        <span class="stock-item-sku">({{ $item->sku }})</span>
                                    @endif
                                </div>
                                <div class="stock-item-meta">
                                    <span class="stock-pill stock-pill-info">
                                        {{ $item->expiry_date?->format('Y-m-d') }}
                                    </span>
                                    <span class="stock-pill">
                                        {{ $item->quantity }} in stock
                                    </span>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="empty-text">No products expiring soon.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Expired --}}
            <div class="stock-section stock-section-expired">
                <div class="stock-section-header">
                    <h4>🛑 Expired</h4>
                    <span class="stock-badge">{{ $expiredProducts->count() }}</span>
                </div>
                <ul class="stock-list">
                    @forelse($expiredProducts as $item)
                        <li class="stock-item">
                            <a href="{{ route('admin.products.show', $item->id) }}">
                                <div class="stock-item-main">
                                    <span class="stock-item-name">{{ $item->name }}</span>
                                    @if($item->sku)
                                        <span class="stock-item-sku">({{ $item->sku }})</span>
                                    @endif
                                </div>
                                <div class="stock-item-meta">
                                    <span class="stock-pill stock-pill-danger-outline">
                                        Expired: {{ $item->expiry_date?->format('Y-m-d') }}
                                    </span>
                                    <span class="stock-pill">
                                        {{ $item->quantity }} in stock
                                    </span>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="empty-text">No expired products in stock.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="stock-modal-footer">
            <button onclick="closeStockModal()" class="close-btn" type="button">Close</button>
        </div>
    </div>
</div>


{{-- Topbar + Stock modal styles (theme-aware) --}}
<style>
:root {
    --orange-main: #c05621;
    --orange-strong: #9a3412;
    --orange-light: #f97316;
    --orange-light-hover: #ea580c;
    --border-soft: rgba(192,132,45,0.35);
}

/* ========== TOPBAR ========== */
.topbar {
    position: sticky;
    top: 0;
    z-index: 40;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    backdrop-filter: blur(10px);
    transition: background 0.2s ease, border-color 0.2s ease;
}

/* Dark topbar */
body.theme-dark .topbar {
    background: rgba(15, 23, 42, 0.98);
    border-bottom: 1px solid rgba(148, 163, 184, 0.4);
}

/* Light topbar */
body.theme-light .topbar {
    background: rgba(255,255,255,0.97);
    border-bottom: 1px solid rgba(209,213,219,0.95);
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Menu button */
.topbar-menu-btn {
    border: 1px solid transparent;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 18px;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease, transform 0.08s ease;
}

/* Dark menu button */
body.theme-dark .topbar-menu-btn {
    background: rgba(30, 64, 175, 0.9);
    color: #e5e7eb;
    border-color: rgba(30,64,175,0.9);
}
body.theme-dark .topbar-menu-btn:hover {
    background: rgba(37, 99, 235, 1);
    border-color: rgba(37, 99, 235, 1);
    transform: translateY(-1px);
}

/* Light menu button */
body.theme-light .topbar-menu-btn {
    background: var(--orange-light);
    color: #fff7ed;
    border-color: var(--orange-light-hover);
}
body.theme-light .topbar-menu-btn:hover {
    background: var(--orange-light-hover);
    transform: translateY(-1px);
}

/* Brand (if re-enabled) */
.topbar-brand {
    display: flex;
    flex-direction: column;
    line-height: 1.1;
}
.brand-name {
    font-size: 18px;
    font-weight: 700;
}
.brand-sub {
    font-size: 11px;
}

/* Brand theme colors */
body.theme-dark .brand-name { color: #f9fafb; }
body.theme-dark .brand-sub  { color: #9ca3af; }

body.theme-light .brand-name { color: var(--orange-strong); }
body.theme-light .brand-sub  { color: #6b7280; }

.topbar-right {
    display: flex;
    align-items: center;
    gap: 16px;
}

/* ========== NOTIFICATIONS ========== */
.notifications {
    position: relative;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px;
    border-radius: 999px;
    transition: background 0.15s ease, transform 0.08s ease;
}
.notif-icon {
    font-size: 20px;
}

/* Dark bell hover */
body.theme-dark .notifications:hover {
    background: rgba(31,41,55,0.85);
    transform: translateY(-1px);
}

/* Light bell hover */
body.theme-light .notifications:hover {
    background: rgba(254,243,199,0.9);
    transform: translateY(-1px);
}

.notif-count {
    position: absolute;
    top: -6px;
    right: -8px;
    font-size: 11px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

/* Dark badge */
body.theme-dark .notif-count {
    background: #ef4444;
    color: #f9fafb;
}

/* Light badge (more orange-ish) */
body.theme-light .notif-count {
    background: var(--orange-light);
    color: #fefce8;
}

/* ========== USER INFO ========== */
.topbar-user {
    display: flex;
    align-items: center;
    gap: 8px;
}

.user-meta {
    display: flex;
    flex-direction: column;
    line-height: 1.1;
}

.user-name {
    font-size: 13px;
    font-weight: 600;
}
.user-role {
    font-size: 11px;
}

/* Dark text */
body.theme-dark .user-name { color: #e5e7eb; }
body.theme-dark .user-role { color: #9ca3af; }

/* Light text */
body.theme-light .user-name { color: var(--orange-strong); }
body.theme-light .user-role { color: #6b7280; }

/* ========== STOCK MODAL (THEME AWARE) ========== */
.stock-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 999;
    padding: 24px 12px;
    backdrop-filter: blur(4px);
}

/* Overlay */
body.theme-dark .stock-modal {
    background: rgba(15, 23, 42, 0.75);
}
body.theme-light .stock-modal {
    background: rgba(15, 23, 42, 0.20);
}

/* Modal content base */
.stock-modal-content {
    max-width: 720px;
    margin: 0 auto;
    border-radius: 18px;
    border: 1px solid;
    padding: 16px 18px 18px;
    max-height: calc(100vh - 80px);
    overflow-y: auto;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
}

/* Dark modal content */
body.theme-dark .stock-modal-content {
    background: radial-gradient(circle at top left, #0f172a, #020617);
    border-color: rgba(148, 163, 184, 0.4);
    color: #e5e7eb;
}

/* Light modal content */
body.theme-light .stock-modal-content {
    background: linear-gradient(to bottom right, #fff7ed, #ffffff);
    border-color: var(--border-soft);
    color: var(--orange-main);
}

.stock-modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 12px;
    gap: 10px;
}
.stock-modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
}

/* Subtitle */
.stock-modal-subtitle {
    margin: 4px 0 0;
    font-size: 12px;
}
body.theme-dark .stock-modal-subtitle { color: #9ca3af; }
body.theme-light .stock-modal-subtitle { color: #6b7280; }

/* Close button */
.stock-modal-close {
    border: none;
    background: transparent;
    font-size: 22px;
    cursor: pointer;
    line-height: 1;
    transition: color 0.15s ease;
}
body.theme-dark .stock-modal-close { color: #9ca3af; }
body.theme-dark .stock-modal-close:hover { color: #f9fafb; }
body.theme-light .stock-modal-close { color: #9ca3af; }
body.theme-light .stock-modal-close:hover { color: #4b5563; }

/* Grid layout */
.stock-modal-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

/* Sections */
.stock-section {
    border-radius: 12px;
    padding: 10px 10px 8px;
    border: 1px solid;
}

/* Dark section bg/border */
body.theme-dark .stock-section {
    background: rgba(15, 23, 42, 0.9);
    border-color: rgba(31, 41, 55, 0.9);
}

/* Light section bg/border */
body.theme-light .stock-section {
    background: rgba(255,255,255,0.98);
    border-color: rgba(229,231,235,0.95);
}

/* Left border accents */
.stock-section-low      { border-left: 3px solid #facc15; }
.stock-section-out      { border-left: 3px solid #ef4444; }
.stock-section-expiring { border-left: 3px solid #38bdf8; }
.stock-section-expired  { border-left: 3px solid #f97316; }

.stock-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
}
.stock-section-header h4 {
    margin: 0;
    font-size: 13px;
    font-weight: 600;
}

/* Section titles theme */
body.theme-dark .stock-section-header h4 { color: #e5e7eb; }
body.theme-light .stock-section-header h4 { color: var(--orange-strong); }

.stock-badge {
    border-radius: 999px;
    padding: 2px 8px;
    font-size: 11px;
}

/* Badge theme */
body.theme-dark .stock-badge {
    background: rgba(15, 23, 42, 1);
    color: #e5e7eb;
    border: 1px solid rgba(148, 163, 184, 0.7);
}
body.theme-light .stock-badge {
    background: #fffbeb;
    color: var(--orange-main);
    border: 1px solid var(--border-soft);
}

/* Lists */
.stock-list {
    list-style: none;
    padding-left: 0;
    margin: 0;
}
.stock-item {
    font-size: 13px;
    padding: 5px 4px;
    border-radius: 8px;
    transition: background 0.15s ease, transform 0.05s ease;
}
.stock-item + .stock-item {
    margin-top: 2px;
}
.stock-item a {
    text-decoration: none;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
body.theme-dark .stock-item a { color: #e5e7eb; }
body.theme-light .stock-item a { color: var(--orange-main); }

body.theme-dark .stock-item:hover {
    background: rgba(31, 41, 55, 0.95);
    transform: translateY(-1px);
}
body.theme-light .stock-item:hover {
    background: rgba(254,243,199,0.9);
    transform: translateY(-1px);
}

.stock-item-main {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    align-items: baseline;
}
.stock-item-name {
    font-weight: 500;
}
.stock-item-sku {
    font-size: 11px;
}
body.theme-dark .stock-item-sku { color: #9ca3af; }
body.theme-light .stock-item-sku { color: #6b7280; }

.stock-item-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 2px;
}

/* Pills */
.stock-pill {
    font-size: 11px;
    padding: 2px 7px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.7);
}

/* Base colors for dark/light */
body.theme-dark .stock-pill {
    background: rgba(15, 23, 42, 0.9);
    color: #e5e7eb;
}
body.theme-light .stock-pill {
    background: #f9fafb;
    color: var(--orange-main);
}

/* Specialized pills */
.stock-pill-warning {
    border-color: #facc15;
    background: rgba(250, 204, 21, 0.08);
    color: #facc15;
}
.stock-pill-danger {
    border-color: #ef4444;
    background: rgba(239, 68, 68, 0.12);
    color: #fecaca;
}
.stock-pill-danger-outline {
    border-color: #f97316;
    background: rgba(248, 113, 113, 0.08);
    color: #fed7aa;
}
.stock-pill-info {
    border-color: #38bdf8;
    background: rgba(56, 189, 248, 0.08);
    color: #bae6fd;
}

.empty-text {
    font-size: 12px;
    padding: 4px 2px;
}
body.theme-dark .empty-text { color: #6b7280; }
body.theme-light .empty-text { color: #9ca3af; }

/* Footer */
.stock-modal-footer {
    margin-top: 14px;
    text-align: right;
}
.close-btn {
    padding: 6px 14px;
    font-size: 13px;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    transition: background 0.15s ease, transform 0.08s ease, box-shadow 0.15s ease;
}

/* Dark close button */
body.theme-dark .close-btn {
    background: rgba(37, 99, 235, 0.9);
    color: #f9fafb;
}
body.theme-dark .close-btn:hover {
    background: rgba(37, 99, 235, 1);
    transform: translateY(-1px);
}

/* Light close button (orange) */
body.theme-light .close-btn {
    background: var(--orange-light);
    color: #fff7ed;
}
body.theme-light .close-btn:hover {
    background: var(--orange-light-hover);
    transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 768px) {
    .stock-modal-content {
        max-width: 100%;
        padding: 14px 12px 16px;
    }
    .stock-modal-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .brand-sub {
        display: none;
    }
    .topbar {
        padding-inline: 10px;
    }
    .topbar-right {
        gap: 10px;
    }
    .user-meta {
        display: none; /* just bell on small screens */
    }
}
</style>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

/* 🔔 OPEN MODAL + HIDE BADGE FOR THIS SESSION */
function openStockModal() {
    const modal = document.getElementById('stockModal');
    if (modal) modal.style.display = 'block';

    const notifCount = document.querySelector('.notif-count');
    if (notifCount) {
        notifCount.style.display = 'none';
    }
}

/* ❌ CLOSE MODAL */
function closeStockModal() {
    const modal = document.getElementById('stockModal');
    if (modal) modal.style.display = 'none';
}

/* Close when clicking outside content */
window.addEventListener('click', function(e){
    const modal = document.getElementById('stockModal');
    if (modal && e.target === modal) {
        closeStockModal();
    }
});
</script>
