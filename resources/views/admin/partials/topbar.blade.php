<div class="topbar">
    <button onclick="toggleSidebar()">☰</button>

    <div class="user">
        <!-- 🔔 BELL WITH COUNT -->
        <div class="notifications" onclick="openStockModal()" style="position:relative; cursor:pointer;">
            🔔
            <span class="notif-count">
                {{ $lowStockCount + $outOfStockCount }}
            </span>
        </div>

        <img src="{{ asset('images/user.png') }}" alt="User Avatar">
        <span>{{ auth()->user()->name }}</span>
    </div>
</div>

<form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">
    @csrf
</form>


<!-- STOCK ALERT MODAL -->
<div id="stockModal" class="stock-modal">
    <div class="stock-modal-content">
        <h3>📦 Stock Alerts</h3>

        <h4>⚠ Low Stock</h4>
        <ul>
            @forelse($lowStock as $item)
                <li>
                    <a href="{{ route('admin.products.show', $item->id) }}">
                        {{ $item->name }} ({{ $item->sku }}) — {{ $item->quantity }} left
                    </a>
                </li>
            @empty
                <li style="color:gray;">No low stock items.</li>
            @endforelse
        </ul>

        <h4 style="margin-top:15px;">❌ Out of Stock</h4>
        <ul>
            @forelse($outOfStock as $item)
                <li>
                    <a href="{{ route('admin.products.show', $item->id) }}">
                        {{ $item->name }} ({{ $item->sku }}) — 0 left
                    </a>
                </li>
            @empty
                <li style="color:gray;">No out of stock items.</li>
            @endforelse
        </ul>

        <button onclick="closeStockModal()" class="close-btn">Close</button>
    </div>
</div>


<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}

/* 🔔 OPEN MODAL */
function openStockModal() {
    document.getElementById('stockModal').style.display = 'block';
}

/* ❌ CLOSE MODAL */
function closeStockModal() {
    document.getElementById('stockModal').style.display = 'none';
}

/* Close when clicking outside */
window.addEventListener('click', function(e){
    if (e.target.id === 'stockModal') {
        closeStockModal();
    }
});
</script>
