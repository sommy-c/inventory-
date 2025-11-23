
<!-- Add Customer Modal -->
<!-- Add Customer Modal -->
<div class="modal" id="customer-modal">
    <div class="modal-content">
        <span class="close" data-close="customer-modal">&times;</span>
        <h3>Add Customer</h3>

        <form id="customer-form">
            @csrf 
            
            <label>Name</label>
            <input type="text" name="name" required>

            <label>Phone</label>
            <input type="text" name="phone">

            <label>Email</label>
            <input type="email" name="email">

            <label>Address</label>
            <input type="text" name="address">

            <div id="customer-error" style="display:none;color:#fecaca;font-size:13px;margin-top:6px;"></div>

            <button type="submit">Save Customer</button>
        </form>
    </div>
</div>


<!-- Payment Modal -->
<div class="modal" id="payment-modal">
    <div class="modal-content">
        <span class="close" data-close="payment-modal">&times;</span>
        <h3>Payment</h3>
        <div>
            <label>Total:</label> <span id="modal-total">0.00</span>
        </div>
        <div>
            <label>Payment Method</label>
            <select id="payment-method">
                <option value="cash">Cash</option>
                <option value="card">Card</option>
            </select>
        </div>
        <div>
            <label>Amount Paid</label>
            <input type="number" id="amount-paid" value="0" min="0">
        </div>
        <div>
            <label>Change:</label> <span id="change">0.00</span>
        </div>
        <button id="submit-payment">Complete Payment</button>
        
        <hr>
        <div id="sales-summary">
            <!-- Optional: summary of items sold -->
        </div>
    </div>
</div>

<!-- Calculator Modal -->
<div class="modal" id="calculator-modal">
    <div class="modal-content">
        <span class="close" data-close="calculator-modal">&times;</span>
        <h3>Calculator</h3>
        <input type="text" id="calc-display" disabled>
        <div class="calc-buttons">
            <button data-value="7">7</button>
            <button data-value="8">8</button>
            <button data-value="9">9</button>
            <button data-value="/">/</button>
            <button data-value="4">4</button>
            <button data-value="5">5</button>
            <button data-value="6">6</button>
            <button data-value="*">*</button>
            <button data-value="1">1</button>
            <button data-value="2">2</button>
            <button data-value="3">3</button>
            <button data-value="-">-</button>
            <button data-value="0">0</button>
            <button data-value=".">.</button>
            <button data-value="C">C</button>
            <button data-value="+">+</button>
            <button data-value="=">=</button>
        </div>
    </div>
</div>

<!-- Hold Sales Modal -->
<!-- Hold Sales Modal (for CURRENT cart) -->
<div class="modal" id="hold-modal">
    <div class="modal-content">
        <span class="close" data-close="hold-modal">&times;</span>
        <h3>Hold Current Sale</h3>

        <p style="font-size:0.85rem; color:#64748b; margin-bottom:0.75rem;">
            Give this held sale a reference so you can find it quickly in Sales History.
        </p>

        <label for="hold-number">Hold Reference</label>
        <input type="text" id="hold-number" placeholder="e.g. Table 4, Order 23, 0803-123-4567">

        <div class="hold-summary" style="margin-top:0.75rem;">
            <table class="pos-table" style="font-size:0.8rem;">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th style="text-align:right;">Qty</th>
                        <th style="text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody id="hold-summary-body">
                    <!-- filled by JS -->
                </tbody>
            </table>
            <div style="margin-top:0.5rem; text-align:right;">
                <strong>Total: ₦<span id="hold-summary-total">0.00</span></strong>
            </div>
        </div>

        <div class="modal-actions" style="margin-top:1rem; display:flex; justify-content:flex-end; gap:0.5rem;">
            <button type="button" class="btn-ghost" id="hold-cancel-btn">Cancel</button>
            <button type="button" class="primary-btn" id="hold-confirm-btn">Accept & Hold</button>
        </div>
    </div>
</div>


<!-- Held sales -->
 <!-- Sales History Modal (Held Sales) -->
<!-- Sales History Modal (Held Sales) -->
<div class="modal" id="sales-history-modal">
    <div class="modal-content">
        <span class="close" data-close="sales-history-modal">&times;</span>
        <h3>Held Sales</h3>

        <table class="pos-table">
            <thead>
                <tr>
                    <th>Hold # / ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Resume</th>  <!-- 👈 new column -->
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody id="sales-history-body">
                <!-- Filled by JS -->
            </tbody>
        </table>
    </div>
</div>

