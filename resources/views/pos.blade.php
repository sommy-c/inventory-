<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS System</title>
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
  
       
</head>
<body>

<div id="flash-message" class="flash-message"></div>

<div class="pos-wrapper">

    <!-- SIDEBAR -->
    <aside class="pos-sidebar">
        <a href="{{ route('dashboard') }}" class="side-btn hold">Home</a>
        <button class="side-btn danger" id="cancel-sale">Cancel</button>
        <button class="side-btn" id="calculator-btn">Calculator</button>
        <button class="side-btn hold" id="sales-history">Sales Draft</button>
        <button class="side-btn hold" id="hold-sale">Hold Sale</button>
        
    </aside>

    <!-- MAIN PANEL -->
    <main class="pos-main">

        <!-- HEADER INFO -->
        <section class="top-info">
            <button id="add-customer-btn">+ Add Customer</button>

            <div class="info-box">
                <label>Customer</label>
                <select id="customer-select">
                    <option value="">Walk-in Customer</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="info-box">
                <label>Receipt Number</label>
                <input type="text" id="receipt-number" value="{{ rand(1000,9999) }}" readonly>
            </div>

            <div class="info-box">
                <label>Employee</label>
                <input type="text" value="{{ Auth::user()->name }}" readonly>
            </div>

            <div class="info-box time-box">
                <p>{{ now()->format('h:i:s A') }}</p>
                <p>{{ now()->format('m/d/Y') }}</p>
            </div>
        </section>

        <!-- ITEMS TABLE -->
        <section class="table-area">
            <div class="search-wrapper">
                <input type="text" id="product-search" placeholder="Search by barcode, SKU, or name">
                <ul id="search-results"></ul>
            </div>
            <table class="pos-table">
                <thead>
                    <tr>
                        <th>Item Number</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Remove</th>
                    </tr>
                </thead>
                <tbody id="items-body"></tbody>
            </table>
        </section>

        <!-- TOTALS -->
        <section class="totals-area">
    <div class="small-totals">
        <div>
            <label>Sub Total</label>
            <span id="sub-total">0.00</span>
        </div>
        <div>
            <label>Discounts</label>
            <span id="discount">0.00</span>
        </div>
        <div>
            <label>Tax</label>
            <span id="tax">0.00</span>
        </div>
    </div>

    <div class="big-total">
        <p>Total</p>
        <h1 id="grand-total">0.00</h1>

        <!-- NEW: Make Payment button inside totals -->
        <button id="make-payment" class="side-btn">
            CheckOut
        </button>
    </div>
</section>


    </main>
</div>

<!-- Full Page Loading Overlay -->
<div id="loading-overlay">
    <div class="spinner"></div>
</div>

@include('pos.modals')

<!-- Receipt Modal -->
<div class="modal" id="receipt-modal">
    <div class="modal-content">
        <span class="close" data-close="receipt-modal">&times;</span>
        <h3>Receipt</h3>

        <div id="receipt-content"></div>

        <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: flex-end;">
            <button id="receipt-continue-btn" type="button" class="save-btn">
                Save &amp; Continue
            </button>

            <button id="print-receipt-btn" type="button" class="print-btn">
                Print Receipt
            </button>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const itemsBody       = document.getElementById('items-body');
    const loadingOverlay  = document.getElementById('loading-overlay');
    const flashMessage    = document.getElementById('flash-message');
    let cartItems = [];

    // Store / receipt meta
    const STORE_NAME    = "{{ config('app.name', 'My Store') }}";
    const STORE_ADDRESS = "123 Main Street, City, Country";   // <- change
    const STORE_PHONE   = "000-000-0000";                     // <- change
    const STORE_LOGO    = "{{ asset('images/logo.png') }}";   // <- change
    const CASHIER_NAME  = "{{ Auth::user()->name }}";

    const salesHistoryBody = document.getElementById('sales-history-body');

    // HOLD modal elements (for Accept / Cancel flow)
    const holdNumberInput  = document.getElementById('hold-number');
    const holdSummaryBody  = document.getElementById('hold-summary-body');
    const holdSummaryTotal = document.getElementById('hold-summary-total');
    const holdConfirmBtn   = document.getElementById('hold-confirm-btn');
    const holdCancelBtn    = document.getElementById('hold-cancel-btn');
    const cancelSaleBtn    = document.getElementById('cancel-sale');
    const newSaleBtn       = document.getElementById('new-sale');

    const printSaleBtn     = document.getElementById('print-sale');

    // ==============================
    // FLASH MESSAGES
    // ==============================
    function showMessage(type, message) {
        if (!flashMessage) return;
        flashMessage.textContent = message;
        flashMessage.className   = 'flash-message ' + type;
        flashMessage.style.display = 'block';
        setTimeout(() => {
            flashMessage.style.display = 'none';
        }, 4000);
    }

    // ==============================
    // SEARCH PRODUCTS (SUGGESTIONS) + ENTER TO ADD
    // ==============================
    const searchInput   = document.getElementById('product-search');
    const searchResults = document.getElementById('search-results');
    let typingTimer;
    const typingDelay = 250; // ms

    function hideSearchResults() {
        if (!searchResults) return;
        searchResults.style.display = 'none';
        searchResults.innerHTML = '';
    }

    if (searchInput && searchResults) {
        // Focus for scanners
        searchInput.focus();

        // Typing: debounce and show suggestions via /search-products
        searchInput.addEventListener('input', () => {
            clearTimeout(typingTimer);

            const q = searchInput.value.trim();
            if (!q) {
                hideSearchResults();
                return;
            }

            typingTimer = setTimeout(searchProducts, typingDelay);
        });

        // ENTER: add to cart via /add-to-cart (and close suggestions)
        searchInput.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(typingTimer);
                hideSearchResults();   // S1: close immediately
                quickAddProduct();
            }

            // ESC to just close suggestions
            if (e.key === 'Escape') {
                clearTimeout(typingTimer);
                hideSearchResults();
            }
        });
    }

    // GET /pos/search-products → suggestions only
    async function searchProducts() {
        const query = searchInput.value.trim();
        if (!query) {
            hideSearchResults();
            return;
        }

        try {
            const res  = await fetch(`{{ route('admin.sales.search-products') }}?name=${encodeURIComponent(query)}`);
            const data = await res.json();

            if (!Array.isArray(data) || !data.length) {
                hideSearchResults();
                return;
            }

            renderSearchResults(data);
        } catch (err) {
            console.error('[searchProducts] ERROR:', err);
            showMessage('error', 'Error searching products');
            hideSearchResults();
        }
    }

    // POST /pos/add-to-cart → ENTER / barcode flow
    async function quickAddProduct() {
        clearTimeout(typingTimer);
        hideSearchResults(); // S1: ensure dropdown is closed

        const query = searchInput.value.trim();
        if (!query) return;

        try {
            const res = await fetch(`{{ route('admin.sales.add-to-cart') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ search: query })
            });

            if (!res.ok) {
                let data = null;
                try { data = await res.json(); } catch (_) {}
                showMessage('error', (data && data.error) || 'Product not found');
                return;
            }

            const p = await res.json();  // single product
            addToCart(p);                // scanning same item again increases qty

            // S1: reset search box completely after add
            searchInput.value = '';
            hideSearchResults();
            searchInput.focus();
        } catch (err) {
            console.error('[quickAddProduct] ERROR:', err);
            showMessage('error', 'Error adding product');
            hideSearchResults();
        }
    }

    function renderSearchResults(data) {
        searchResults.innerHTML = '';

        data.forEach(p => {
            const li = document.createElement('li');
            li.textContent = `${p.name} - ${p.sku} - $${p.selling_price}`;
            li.style.cursor = 'pointer';

            li.addEventListener('click', () => {
                // Add clicked item
                addToCart(p);

                // S1: immediately clear & hide after click
                clearTimeout(typingTimer);
                searchInput.value = '';
                hideSearchResults();
                searchInput.focus();
            });

            searchResults.appendChild(li);
        });

        searchResults.style.display = 'block';
    }

    // ==============================
    // CART
    // ==============================
    function addToCart(p) {
    // Normalize price from both endpoints
    let rawPrice = p.price ?? p.selling_price;

    if (rawPrice === undefined || rawPrice === null) {
        console.error("Product has no price:", p);
        showMessage("error", "Product has no price");
        return;
    }

    const price = Number(rawPrice);
    if (isNaN(price)) {
        console.error("Invalid price format:", rawPrice);
        showMessage("error", "Invalid product price");
        return;
    }

    // 🔥 NEW: read stock (comes from backend search/add APIs)
    const stock = Number(p.quantity ?? 0);

    if (stock <= 0) {
        showMessage("error", `${p.name} is OUT OF STOCK`);
        return;
    }

    // Check if product already in cart
    const exists = cartItems.find(i => i.id === p.id);

    if (exists) {
        // 🔥 Prevent overselling
        if (exists.qty + 1 > stock) {
            showMessage('error', `Only ${stock} left in stock for ${p.name}`);
            return;
        }

        exists.qty += 1; // increase quantity
    } else {
        // New item, set qty=1 but prevent if no stock
        cartItems.push({
            id: p.id,
            sku: p.sku,
            name: p.name,
            price: price,
            qty: 1,
            stock: stock    // NEW: store stock to validate qty changes
        });
    }

    renderCart();
}


    function renderCart() {
        itemsBody.innerHTML = '';
        let subtotal = 0;

        cartItems.forEach(i => {
            const lineTotal = i.price * i.qty;
            subtotal += lineTotal;
           itemsBody.innerHTML += `
    <tr>
        <td>${i.sku}</td>
        <td>
            ${i.name}
            ${i.stock !== undefined && i.stock < i.qty
                ? `<span style="color:red; font-size:12px;"> 🔴 OUT OF STOCK</span>`
                : ``}
        </td>
        <td>${i.price.toFixed(2)}</td>
        <td>
            <div class="qty-wrapper">
                <button type="button" class="qty-btn" 
                    onclick="changeQty(${i.id}, -1)"
                    ${i.stock !== undefined && i.stock < 1 ? 'disabled' : ''}
                >-</button>

                <input type="number" min="1" value="${i.qty}"
                    onchange="updateQty(${i.id}, this.value)"
                    ${i.stock !== undefined && i.stock < i.qty ? 'style="border:1px solid red;"' : ''}
                >

                <button type="button" class="qty-btn"
                    onclick="changeQty(${i.id}, 1)"
                    ${(i.stock !== undefined && i.qty >= i.stock) ? 'disabled' : ''}
                >+</button>
            </div>
        </td>
        <td><button onclick="removeItem(${i.id})">Delete</button></td>
    </tr>
`;

        });

        document.getElementById('sub-total').textContent   = subtotal.toFixed(2);
        document.getElementById('grand-total').textContent = subtotal.toFixed(2);

        if (printSaleBtn) {
            printSaleBtn.disabled = cartItems.length === 0;
        }
    }

    // expose qty/update to inline handlers
   window.updateQty = function(id, qty) {
    const item = cartItems.find(i => i.id === id);
    if (!item) return;

    const newQty = parseInt(qty) || 1;

    if (newQty > item.stock) {
        showMessage("error", `Only ${item.stock} left in stock`);
        item.qty = item.stock;
    } else {
        item.qty = newQty < 1 ? 1 : newQty;
    }

    renderCart();
};


   window.changeQty = function(id, delta) {
    const item = cartItems.find(i => i.id === id);
    if (!item) return;

    let newQty = item.qty + delta;

    if (newQty > item.stock) {
        showMessage("error", `Only ${item.stock} left in stock`);
        newQty = item.stock;
    }

    if (newQty < 1) newQty = 1;

    item.qty = newQty;
    renderCart();
};


    window.removeItem = function(id) {
        cartItems = cartItems.filter(i => i.id !== id);
        renderCart();
    };

    function resetSaleState(message = 'Sale cancelled.') {
        // Clear cart items
        cartItems = [];
        renderCart(); // this will zero subtotal / total

        // Reset totals explicitly
        const subTotalEl   = document.getElementById('sub-total');
        const discountEl   = document.getElementById('discount');
        const taxEl        = document.getElementById('tax');
        const grandTotalEl = document.getElementById('grand-total');

        if (subTotalEl)   subTotalEl.textContent   = '0.00';
        if (discountEl)   discountEl.textContent   = '0.00';
        if (taxEl)        taxEl.textContent        = '0.00';
        if (grandTotalEl) grandTotalEl.textContent = '0.00';

        // Reset customer to Walk-in (optional)
        const customerSelect = document.getElementById('customer-select');
        if (customerSelect) customerSelect.value = '';

        // Generate a new receipt number
        const receiptNumberInput = document.getElementById('receipt-number');
        if (receiptNumberInput) {
            const newNum = Math.floor(1000 + Math.random() * 9000);
            receiptNumberInput.value = newNum;
        }

        // Clear search box & suggestions
        if (searchInput) {
            searchInput.value = '';
        }
        hideSearchResults();
        if (searchInput) searchInput.focus();

        // Close any open modals
        ['payment-modal', 'hold-modal', 'sales-history-modal', 'receipt-modal', 'customer-modal']
            .forEach(id => {
                const m = document.getElementById(id);
                if (m) m.style.display = 'none';
            });

        // Optional message
        showMessage('success', message);
    }

    // ==============================
    // MODALS OPEN/CLOSE
    // ==============================
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.style.display = 'flex';
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.style.display = 'none';
    }

    // Close buttons (×)
    document.querySelectorAll('.close').forEach(span => {
        span.addEventListener('click', () => closeModal(span.dataset.close));
    });

    // Click outside modal-content closes the modal
    window.addEventListener('click', e => {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    });

    // ==============================
    // BUTTONS THAT OPEN MODALS
    // ==============================
    const addCustomerBtn   = document.getElementById('add-customer-btn');
    const makePaymentBtn   = document.getElementById('make-payment');
    const calculatorBtn    = document.getElementById('calculator-btn');
    const holdSaleBtn      = document.getElementById('hold-sale');
    const salesHistoryBtn  = document.getElementById('sales-history');

    if (addCustomerBtn) {
        addCustomerBtn.addEventListener('click', () => openModal('customer-modal'));
    }

    if (makePaymentBtn) {
        makePaymentBtn.addEventListener('click', () => {
            const total = document.getElementById('grand-total').textContent || '0.00';
            document.getElementById('modal-total').textContent = total;
            document.getElementById('amount-paid').value = total;
            updateChange();
            openModal('payment-modal');
        });
    }

    if (calculatorBtn) {
        calculatorBtn.addEventListener('click', () => openModal('calculator-modal'));
    }

    // NEW: open Hold modal with current cart
    if (holdSaleBtn) {
        holdSaleBtn.addEventListener('click', () => {
            if (cartItems.length === 0) {
                showMessage('error', 'No items to hold');
                return;
            }
            openHoldModalForCurrentCart();
        });
    }

    // Sales History -> load held (paused) sales from server
    if (salesHistoryBtn) {
        salesHistoryBtn.addEventListener('click', () => {
            loadHeldSalesFromServer();
        });
    }

    // CANCEL SALE button — also closes suggestions
    if (cancelSaleBtn) {
        cancelSaleBtn.addEventListener('click', () => {
            resetSaleState('Sale cancelled.');
        });
    }

    if (newSaleBtn) {
        newSaleBtn.addEventListener('click', () => {
            resetSaleState('New sale started.');
        });
    }

    // ==============================
    // HOLD SALE FLOW (ACCEPT / CANCEL)
    // ==============================
    function openHoldModalForCurrentCart() {
        if (!holdSummaryBody || !holdSummaryTotal) {
            console.warn('Hold modal elements not found.');
            return;
        }

        holdSummaryBody.innerHTML = '';
        let subtotal = 0;

        cartItems.forEach(item => {
            const lineTotal = item.price * item.qty;
            subtotal += lineTotal;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.name}</td>
                <td style="text-align:right;">${item.qty}</td>
                <td style="text-align:right;">${lineTotal.toFixed(2)}</td>
            `;
            holdSummaryBody.appendChild(tr);
        });

        holdSummaryTotal.textContent = subtotal.toFixed(2);
        if (holdNumberInput) {
            holdNumberInput.value = `HOLD-${Date.now()}`;
        }

        openModal('hold-modal');
    }

    // Cancel hold
    if (holdCancelBtn) {
        holdCancelBtn.addEventListener('click', () => {
            closeModal('hold-modal');
        });
    }

    // Accept & hold (send to backend)
    if (holdConfirmBtn) {
        holdConfirmBtn.addEventListener('click', async () => {
            if (cartItems.length === 0) {
                showMessage('error', 'No items in cart to hold');
                return;
            }

            const holdRef = (holdNumberInput && holdNumberInput.value.trim())
                ? holdNumberInput.value.trim()
                : `HOLD-${Date.now()}`;

            const customerSelect = document.getElementById('customer-select');
            const customerName = customerSelect?.selectedOptions[0]?.text || 'Walk-in Customer';

            const payload = {
                items: cartItems.map(i => ({
                    id: i.id,
                    sku: i.sku,
                    name: i.name,
                    price: i.price,
                    qty: i.qty,
                })),
                hold_number: holdRef,
                customer_name: customerName,
                customer_phone: null,
                customer_email: null,
                payment_method: 'cash',
            };

            loadingOverlay.style.display = 'flex';

            try {
                const res = await fetch("{{ route('admin.sales.pause') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload)
                });

                if (!res.ok) {
                    const text = await res.text();
                    console.error('[hold] HTTP error', res.status, text);
                    showMessage('error', 'Could not hold sale. Check server logs.');
                    return;
                }

                const data = await res.json();
                showMessage('success', data.success || 'Sale held successfully');

                cartItems = [];
                renderCart();
                closeModal('hold-modal');
            } catch (err) {
                console.error('[hold] ERROR', err);
                showMessage('error', 'Error holding sale');
            } finally {
                loadingOverlay.style.display = 'none';
            }
        });
    }

    // ==============================
    // SALES HISTORY (HELD SALES FROM SERVER)
    // ==============================
    const deleteHeldUrlTemplate = "{{ route('admin.sales.held.destroy', ['sale' => '__ID__']) }}";
    const resumeHeldUrlTemplate = "{{ route('admin.sales.resume', ['sale' => '__ID__']) }}";

    async function loadHeldSalesFromServer() {
        if (!salesHistoryBody) return;

        loadingOverlay.style.display = 'flex';

        try {
            const res = await fetch("{{ route('admin.sales.held') }}", {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!res.ok) {
                const text = await res.text();
                console.error('[heldSales] HTTP error', res.status, text);
                showMessage('error', 'Failed to load held sales');
                return;
            }

            const sales = await res.json();
            salesHistoryBody.innerHTML = '';

            if (!Array.isArray(sales) || sales.length === 0) {
                salesHistoryBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align:center;">No held sales.</td>
                    </tr>
                `;
            } else {
                sales.forEach(s => {
                    const row = document.createElement('tr');
                    const dateString = s.created_at ?? '';

                    row.innerHTML = `
                        <td>${s.hold_number || s.id}</td>
                        <td>${s.customer_name || 'Walk-in'}</td>
                        <td>${Number(s.total).toFixed(2)}</td>
                        <td>${dateString}</td>
                        <td style="text-align:center;">
                            <button 
                                type="button" 
                                class="held-resume-btn" 
                                data-id="${s.id}" 
                                title="Resume this sale">
                                🔄
                            </button>
                        </td>
                        <td style="text-align:center;">
                            <button 
                                type="button" 
                                class="held-delete-btn" 
                                data-id="${s.id}" 
                                title="Delete held sale">
                                🗑️
                            </button>
                        </td>
                    `;
                    salesHistoryBody.appendChild(row);
                });
            }

            openModal('sales-history-modal');
        } catch (err) {
            console.error('[heldSales] ERROR', err);
            showMessage('error', 'Error loading held sales');
        } finally {
            loadingOverlay.style.display = 'none';
        }
    }

    if (salesHistoryBody) {
    salesHistoryBody.addEventListener('click', async (e) => {
        const resumeBtn = e.target.closest('.held-resume-btn');
        const deleteBtn = e.target.closest('.held-delete-btn');

        // =============================
        // RESUME HELD SALE
        // =============================
        if (resumeBtn) {
            const id = resumeBtn.dataset.id;
            if (!id) return;

            const url = resumeHeldUrlTemplate.replace('__ID__', id);

            try {
                loadingOverlay.style.display = 'flex';

                const res = await fetch(url, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                });

                if (!res.ok) {
                    const text = await res.text();
                    console.error('[resumeHeld] HTTP error', res.status, text);
                    showMessage('error', 'Failed to resume held sale');
                    return;
                }

                const sale = await res.json();

                if (!sale.items || !Array.isArray(sale.items) || sale.items.length === 0) {
                    showMessage('error', 'No items found for this held sale');
                    return;
                }

                cartItems = [];

                for (let item of sale.items) {
                    const prodId  = item.product_id;
                    const sku     = item.sku;
                    const name    = item.name;
                    const price   = Number(item.price);
                    const qty     = Number(item.qty);

                    // GET real live product stock
                    const product = await fetch(`/products/json/${prodId}`)
                        .then(r => r.json())
                        .catch(() => null);

                    let stock = product?.quantity ?? 0;

                    // Add to cart
                    cartItems.push({
                        id: prodId,
                        sku: sku,
                        name: name,
                        price: price,
                        qty: qty,
                        stock: stock,    // <-- important
                    });

                    // If held qty > current stock → warn user
                    if (qty > stock) {
                        showMessage(
                            'error',
                            `⚠ ${name} only has ${stock} left (held sale requested ${qty}).`
                        );
                    }
                }

                renderCart();
                showMessage('success', 'Held sale resumed into cart');
                closeModal('sales-history-modal');

            } catch (err) {
                console.error('[resumeHeld] ERROR', err);
                showMessage('error', 'Error resuming held sale');
            } finally {
                loadingOverlay.style.display = 'none';
            }

            return;
        }

        // =============================
        // DELETE HELD SALE
        // =============================
        if (deleteBtn) {
            const id = deleteBtn.dataset.id;
            if (!id) return;

            if (!confirm('Delete this held sale?')) return;

            const url = deleteHeldUrlTemplate.replace('__ID__', id);

            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json',
                    },
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    showMessage('error', data.error || 'Failed to delete held sale');
                    return;
                }

                showMessage('success', 'Held sale deleted');
                deleteBtn.closest('tr').remove();

                if (!salesHistoryBody.querySelector('tr')) {
                    salesHistoryBody.innerHTML = `
                        <tr><td colspan="6" style="text-align:center;">No held sales.</td></tr>
                    `;
                }

            } catch (err) {
                console.error('[deleteHeld] ERROR', err);
                showMessage('error', 'Error deleting held sale');
            }
        }
    });
}


    // ==============================
    // PAYMENT (button id="submit-payment") + RECEIPT MODAL
    // ==============================
    const submitPaymentBtn   = document.getElementById('submit-payment');
    const amountPaidInput    = document.getElementById('amount-paid');
    const receiptContent     = document.getElementById('receipt-content');
    const printReceiptBtn    = document.getElementById('print-receipt-btn');
    const receiptContinueBtn = document.getElementById('receipt-continue-btn');

    function updateChange() {
        const total  = parseFloat(document.getElementById('modal-total').textContent) || 0;
        const paid   = parseFloat(amountPaidInput.value) || 0;
        const change = paid - total;
        document.getElementById('change').textContent = change > 0 ? change.toFixed(2) : '0.00';
    }
    

    if (amountPaidInput) {
        amountPaidInput.addEventListener('input', updateChange);
    }

    if (submitPaymentBtn) {
        submitPaymentBtn.addEventListener('click', async () => {
            if (cartItems.length === 0) {
                showMessage('error', 'No items in cart');
                return;
                
            }
             for (let i of cartItems) {
            if (i.qty > i.stock) {
                showMessage('error', `${i.name} has only ${i.stock} in stock.`);
                return; // stop checkout
            }
        }

            const paid  = parseFloat(amountPaidInput.value) || 0;
            const total = parseFloat(document.getElementById('modal-total').textContent) || 0;
            if (paid < total) {
                if (!confirm('Amount paid is less than total. Continue?')) {
                    return;
                }
            }

            const payload = {
                items: cartItems,
                amount_paid: paid,
                payment_method: document.getElementById('payment-method').value,
                customer_name: document.getElementById('customer-select').selectedOptions[0].text
            };

            loadingOverlay.style.display = 'flex';

            try {
                const res = await fetch("{{ route('admin.sales.checkout') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload)
                });

                if (!res.ok) {
                    const text = await res.text();
                    console.error('[checkout] HTTP error', res.status, text);
                    showMessage('error', 'Payment failed. Check server logs.');
                    return;
                }

                let data;
                try {
                    data = await res.json();
                } catch (e) {
                    console.error('[checkout] JSON parse error', e);
                    showMessage('error', 'Server did not return valid JSON.');
                    return;
                }

                showMessage('success', data.success || 'Payment completed');

                // Build receipt content for modal
                if (receiptContent) {
                    let itemsRows = "";
                    cartItems.forEach(i => {
                        const lineTotal = i.price * i.qty;
                        itemsRows += `
                            <tr>
                                <td>${i.name}</td>
                                <td style="text-align:right;">${i.qty}</td>
                                <td style="text-align:right;">${i.price.toFixed(2)}</td>
                                <td style="text-align:right;">${lineTotal.toFixed(2)}</td>
                            </tr>
                        `;
                    });

                    const change = paid - total;
                    const saleId = data.sale_id ?? '';

                    receiptContent.innerHTML = `
                        <style>
                            .receipt-wrapper {
                                width: 260px;
                                font-family: "Courier New", monospace;
                                font-size: 12px;
                                margin: 0 auto;
                                text-align: center;
                            }
                            .receipt-wrapper h2 {
                                margin: 4px 0;
                                font-size: 14px;
                                letter-spacing: 1px;
                            }
                            .receipt-logo {
                                max-width: 60px;
                                max-height: 60px;
                                margin-bottom: 4px;
                            }
                            .receipt-line {
                                border-top: 1px dashed #000;
                                margin: 6px 0;
                            }
                            .receipt-section {
                                margin: 6px 0;
                                text-align: left;
                            }
                            .receipt-section p {
                                margin: 2px 0;
                            }
                            .receipt-items {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 4px;
                            }
                            .receipt-items th,
                            .receipt-items td {
                                padding: 2px 0;
                            }
                            .receipt-items th {
                                border-bottom: 1px solid #000;
                                font-weight: bold;
                            }
                            .receipt-totals {
                                margin-top: 6px;
                                text-align: right;
                            }
                            .receipt-totals p {
                                margin: 2px 0;
                            }
                            .receipt-barcode {
                                margin-top: 8px;
                                padding-top: 4px;
                                border-top: 1px dashed #000;
                                font-size: 10px;
                                letter-spacing: 3px;
                            }
                        </style>

                        <div class="receipt-wrapper">
                            <div>
                                <img src="${STORE_LOGO}" alt="Logo" class="receipt-logo">
                            </div>
                            <h2>${STORE_NAME}</h2>
                            <p>${STORE_ADDRESS}</p>
                            <p>${STORE_PHONE}</p>
                            <div class="receipt-line"></div>
                            <p><strong>RECEIPT</strong></p>
                            <div class="receipt-line"></div>

                            <div class="receipt-section">
                                <p><strong>Customer:</strong> ${payload.customer_name || 'Walk-in Customer'}</p>
                                <p><strong>Cashier:</strong> ${CASHIER_NAME}</p>
                                <p><strong>Payment:</strong> ${payload.payment_method}</p>
                                <p><strong>Date:</strong> {{ now()->format('Y-m-d H:i') }}</p>
                            </div>

                            <div class="receipt-line"></div>

                            <table class="receipt-items">
                                <thead>
                                    <tr>
                                        <th style="text-align:left;">Item</th>
                                        <th style="text-align:right;">Qty</th>
                                        <th style="text-align:right;">Price</th>
                                        <th style="text-align:right;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsRows}
                                </tbody>
                            </table>

                            <div class="receipt-totals">
                                <p><strong>Total:</strong> ${total.toFixed(2)}</p>
                                <p><strong>Paid:</strong> ${paid.toFixed(2)}</p>
                                <p><strong>Change:</strong> ${(change > 0 ? change.toFixed(2) : '0.00')}</p>
                            </div>

                            <div class="receipt-barcode">
                                ${String(saleId).padStart(10, '0')}
                            </div>

                            <p style="margin-top:6px;">Thank you for shopping!</p>
                        </div>
                    `;

                    openModal('receipt-modal');
                }

                // Clear cart
                cartItems = [];
                renderCart();
                closeModal('payment-modal');
            } catch (err) {
                console.error(err);
                showMessage('error', 'Error processing payment');
            } finally {
                loadingOverlay.style.display = 'none';
            }
        });
    }

    if (printReceiptBtn && receiptContent) {
        printReceiptBtn.addEventListener('click', () => {
            const printWindow = window.open('', '', 'width=800,height=600');
            printWindow.document.write('<html><head><title>Receipt</title>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(receiptContent.innerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        });
    }

    // SAVE & CONTINUE button inside receipt modal
    if (receiptContinueBtn) {
        receiptContinueBtn.addEventListener('click', () => {
            const receiptModal = document.getElementById('receipt-modal');
            if (receiptModal) receiptModal.style.display = 'none';

            const receiptNumberInput = document.getElementById('receipt-number');
            if (receiptNumberInput) {
                const newNum = Math.floor(1000 + Math.random() * 9000);
                receiptNumberInput.value = newNum;
            }

            if (searchInput) searchInput.focus();
            showMessage('success', 'Sale saved — ready for the next customer.');
        });
    }

    // ==============================
    // CALCULATOR
    // ==============================
    const calcDisplay = document.getElementById('calc-display');
    const calcButtons = document.querySelectorAll('#calculator-modal .calc-buttons button');
    let calcExpression = '';

    if (calcDisplay && calcButtons.length) {
        calcButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const val = btn.dataset.value;
                if (val === 'C') {
                    calcExpression = '';
                    calcDisplay.value = '';
                } else if (val === '=') {
                    try {
                        const result = Function('"use strict"; return (' + calcExpression + ')')();
                        calcDisplay.value = result;
                        calcExpression = String(result);
                    } catch {
                        calcDisplay.value = 'Error';
                        calcExpression = '';
                    }
                } else {
                    calcExpression += val;
                    calcDisplay.value = calcExpression;
                }
            });
        });
    }
});
</script>
</body>
</html> 