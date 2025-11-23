@extends('admin.layout')

@section('title', 'Suppliers')

@section('content')
<div class="customers-page"><!-- reuse same layout styles -->

    <div class="page-header">
        <h1>Suppliers</h1>

        <form action="{{ route('admin.suppliers.index') }}" method="GET" class="search-form">
            {{-- Supplier search --}}
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Search supplier (name, email, phone)"
            >

            {{-- Product filter --}}
            <input
                type="text"
                name="product"
                value="{{ $productFilter ?? '' }}"
                placeholder="Filter by product name or SKU"
                class="filter-input"
            >

            {{-- Payment status filter --}}
            <select name="payment_status" class="filter-select">
                <option value="">Payment status (all)</option>
                <option value="paid"    @if(($paymentStatus ?? '') === 'paid') selected @endif>Paid</option>
                <option value="unpaid"  @if(($paymentStatus ?? '') === 'unpaid') selected @endif>Unpaid</option>
                <option value="partial" @if(($paymentStatus ?? '') === 'partial') selected @endif>Partial</option>
            </select>

            <button type="submit">Filter</button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid-layout">
        {{-- ====== SUPPLIERS TABLE CARD ====== --}}
        <div class="card table-card">
            <div class="card-header">
                <h2>Supplier List</h2>
                <span class="badge">{{ $suppliers->total() }} total</span>
            </div>

            {{-- Bulk message button + hint --}}
            <div class="bulk-actions">
                <button type="button" class="btn-primary" id="bulkMessageBtn">
                    Message selected / all
                </button>
                <span class="bulk-hint">
                    Select suppliers in the table, or leave none selected to target all on this page.
                </span>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAllSuppliers">
                            </th>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($suppliers as $index => $supplier)
                        <tr
                            class="supplier-row"
                            data-id="{{ $supplier->id }}"
                        >
                            <td>
                                <input
                                    type="checkbox"
                                    class="select-supplier"
                                    data-email="{{ $supplier->email }}"
                                    data-phone="{{ $supplier->phone }}"
                                    data-name="{{ $supplier->name }}"
                                    onclick="event.stopPropagation();"
                                >
                            </td>
                            <td>{{ $suppliers->firstItem() + $index }}</td>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->email ?: '—' }}</td>
                            <td>{{ optional($supplier->created_at)->format('Y-m-d') }}</td>
                            <td class="actions-cell">
                                @role('admin')
                                {{-- Delete button (admin only) --}}
                                <button
                                    type="button"
                                    class="btn-small btn-delete"
                                    data-id="{{ $supplier->id }}"
                                    data-name="{{ $supplier->name }}"
                                    onclick="event.stopPropagation();"
                                    title="Delete Supplier"
                                >
                                    🗑
                                </button>
                                @endrole

                                {{-- Email button --}}
                                <button
                                    type="button"
                                    class="btn-small btn-email"
                                    data-open-email="1"
                                    data-email="{{ $supplier->email }}"
                                    data-name="{{ $supplier->name }}"
                                    @if(!$supplier->email) disabled @endif
                                    onclick="event.stopPropagation();"
                                >
                                    Email
                                </button>

                                {{-- Text button --}}
                                <button
                                    type="button"
                                    class="btn-small btn-sms"
                                    data-open-sms="1"
                                    data-phone="{{ $supplier->phone }}"
                                    data-name="{{ $supplier->name }}"
                                    @if(!$supplier->phone) disabled @endif
                                    onclick="event.stopPropagation();"
                                >
                                    Text
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;">No suppliers found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $suppliers->links() }}
            </div>
        </div>

        {{-- ====== ADD SUPPLIER FORM CARD ====== --}}
        <div class="card form-card-custom">
            <h3>Add New Supplier</h3>

            <form action="{{ route('admin.suppliers.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Name <span class="required">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}">
                    @error('email')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}">
                    @error('phone')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input id="address" type="text" name="address" value="{{ old('address') }}">
                    @error('address')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="submit-btn">Save Supplier</button>
            </form>
        </div>
    </div>
</div>

{{-- ======= SUPPLIER DETAILS MODAL ======= --}}
<div id="supplierDetailsModal" class="modal-overlay hidden">
    <div class="modal-card modal-card-sm">
        <div class="modal-header">
            <h2 id="sd_name">Supplier Details</h2>
            <button type="button" class="modal-close supplier-details-close" aria-label="Close">&times;</button>
        </div>

        <div class="modal-body customer-details-body">
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value" id="sd_email">—</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span class="detail-value" id="sd_phone">—</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Address:</span>
                <span class="detail-value" id="sd_address">—</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created at:</span>
                <span class="detail-value" id="sd_created">—</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Last supply:</span>
                <span class="detail-value" id="sd_last_supply">—</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment:</span>
                <span class="detail-value" id="sd_payment_status">—</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Products:</span>
                <div class="detail-value">
                    <ul id="sd_products_list" class="products-list"></ul>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-secondary supplier-details-close">Close</button>
            <button type="button" class="btn-primary" id="sd_sendEmailBtn">Email</button>
            <button type="button" class="btn-primary" id="sd_sendSmsBtn">Text</button>
        </div>
    </div>
</div>

{{-- ======= DELETE SUPPLIER MODAL ======= --}}
@role('admin')
<div id="deleteSupplierModal" class="modal-overlay hidden">
    <div class="modal-card modal-card-sm">
        <div class="modal-header">
            <h2>Delete Supplier</h2>
            <button type="button" class="modal-close delete-modal-close" aria-label="Close">&times;</button>
        </div>

        <div class="modal-body">
            <p id="deleteSupplierText">Are you sure you want to delete this supplier?</p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-secondary delete-modal-cancel">Cancel</button>

            <form id="deleteSupplierForm" method="POST" style="margin:0;"enctype="multipart/form-data"
>
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-primary btn-danger-delete">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endrole

{{-- ======= MESSAGE MODAL (same as customers) ======= --}}
<div id="messageModal" class="modal-overlay hidden">
    <div class="modal-card">
        <div class="modal-header">
            <h2 id="messageModalTitle">Send Message</h2>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>

        <div class="modal-tabs">
            <button type="button" class="modal-tab active" data-tab="email">Email</button>
            <button type="button" class="modal-tab" data-tab="sms">Text</button>
        </div>

        <div class="modal-body">
            {{-- Email form --}}
            <form id="emailForm" class="modal-form" action="#" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group-inline">
                    <div class="form-group">
                        <label for="email_to">To</label>
                        <input id="email_to" type="email" name="to" placeholder="recipient@example.com">
                    </div>
                    <div class="form-group">
                        <label for="email_cc">CC</label>
                        <input id="email_cc" type="text" name="cc" placeholder="cc1@example.com, cc2@example.com">
                    </div>
                    <div class="form-group">
                        <label for="email_bcc">BCC</label>
                        <input id="email_bcc" type="text" name="bcc" placeholder="bcc1@example.com, bcc2@example.com">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email_subject">Subject</label>
                    <input id="email_subject" type="text" name="subject" placeholder="Subject">
                </div>

                <div class="form-group">
                    <label for="email_message">Message</label>
                    <textarea id="email_message" name="message" rows="5" placeholder="Write your message..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary modal-cancel">Cancel</button>
                    <button type="button" class="btn-primary" id="sendEmailBtn">Send Email</button>
                </div>
            </form>

            {{-- SMS form --}}
            <form id="smsForm" class="modal-form hidden" action="#" method="POST">
                @csrf
                <div class="form-group">
                    <label for="sms_to">Phone</label>
                    <input id="sms_to" type="text" name="phone" placeholder="+1 555 123 4567">
                </div>

                <div class="form-group">
                    <label for="sms_message">Message</label>
                    <textarea id="sms_message" name="message" rows="4" placeholder="Type your text message..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary modal-cancel">Cancel</button>
                    <button type="button" class="btn-primary" id="sendSmsBtn">Send Text</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* tiny extra for products list inside details modal */
    .products-list {
        list-style: disc;
        margin: 0;
        padding-left: 18px;
        max-height: 140px;
        overflow-y: auto;
        font-size: 13px;
    }

    .products-list li {
        margin-bottom: 4px;
    }

    .filter-select,
    .filter-input {
        padding: 10px 12px;
        border-radius: 999px;
        border: 1px solid #374151;
        background: #020617;
        color: white;
        font-size: 14px;
        outline: none;
    }

    .filter-select:focus,
    .filter-input:focus {
        border-color: #2563eb;
    }

    .btn-small.btn-delete {
        background: rgba(239, 68, 68, 0.85);
    }
    .btn-small.btn-delete:hover {
        background: rgba(239, 68, 68, 1);
    }

    .btn-danger-delete {
        background: #ef4444;
    }
    .btn-danger-delete:hover {
        background: #dc2626;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ========= MESSAGE MODAL ELEMENTS =========
    const modalOverlay = document.getElementById('messageModal');
    const modalTitle   = document.getElementById('messageModalTitle');

    const emailForm = document.getElementById('emailForm');
    const smsForm   = document.getElementById('smsForm');

    const emailTo      = document.getElementById('email_to');
    const emailSubject = document.getElementById('email_subject');
    const emailMessage = document.getElementById('email_message');

    const smsTo      = document.getElementById('sms_to');
    const smsMessage = document.getElementById('sms_message');

    const sendEmailBtn = document.getElementById('sendEmailBtn');
    const sendSmsBtn   = document.getElementById('sendSmsBtn');

    const tabs = document.querySelectorAll('.modal-tab');

    const bulkMessageBtn    = document.getElementById('bulkMessageBtn');
    const selectAllCheckbox = document.getElementById('selectAllSuppliers');

    // ========= SUPPLIER DETAILS MODAL =========
    const supplierDetailsModal = document.getElementById('supplierDetailsModal');
    const sdName          = document.getElementById('sd_name');
    const sdEmail         = document.getElementById('sd_email');
    const sdPhone         = document.getElementById('sd_phone');
    const sdAddress       = document.getElementById('sd_address');
    const sdCreated       = document.getElementById('sd_created');
    const sdLastSupply    = document.getElementById('sd_last_supply');
    const sdPaymentStatus = document.getElementById('sd_payment_status');
    const sdProductsList  = document.getElementById('sd_products_list');
    const sdSendEmail     = document.getElementById('sd_sendEmailBtn');
    const sdSendSms       = document.getElementById('sd_sendSmsBtn');

    let currentDetailEmail = '';
    let currentDetailPhone = '';

    // ========= DELETE MODAL ELEMENTS (ADMIN ONLY) =========
    const deleteModal      = document.getElementById('deleteSupplierModal');
    const deleteForm       = document.getElementById('deleteSupplierForm');
    const deleteText       = document.getElementById('deleteSupplierText');

    // ========= HELPERS =========
    function getRowCheckboxes() {
        return Array.from(document.querySelectorAll('.select-supplier'));
    }

    function setActiveTab(tabName) {
        tabs.forEach(tab => {
            const isActive = tab.dataset.tab === tabName;
            tab.classList.toggle('active', isActive);
        });

        if (tabName === 'email') {
            emailForm.classList.remove('hidden');
            smsForm.classList.add('hidden');
        } else {
            smsForm.classList.remove('hidden');
            emailForm.classList.add('hidden');
        }
    }

    function openMessageModal(forType, data) {
        // reset forms
        emailForm.reset();
        smsForm.reset();
        emailSubject.value = '';
        emailMessage.value = '';
        smsMessage.value   = '';

        if (forType === 'email') {
            setActiveTab('email');
            emailTo.value = data.email || '';
            modalTitle.textContent = data.title || (`Email ${data.name || ''}`).trim();
        } else {
            setActiveTab('sms');
            smsTo.value = data.phone || '';
            modalTitle.textContent = data.title || (`Text ${data.name || ''}`).trim();
        }

        modalOverlay.classList.remove('hidden');
    }

    function closeMessageModal() {
        modalOverlay.classList.add('hidden');
    }

    function openSupplierDetails(data) {
        currentDetailEmail = data.email || '';
        currentDetailPhone = data.phone || '';

        sdName.textContent          = data.name || 'Supplier Details';
        sdEmail.textContent         = data.email || '—';
        sdPhone.textContent         = data.phone || '—';
        sdAddress.textContent       = data.address || '—';
        sdCreated.textContent       = data.created_at || '—';
        sdLastSupply.textContent    = data.last_supply || '—';
        sdPaymentStatus.textContent = data.payment_status || '—';

        sdProductsList.innerHTML = '';
        (data.products || []).forEach(p => {
            const li = document.createElement('li');
            li.textContent = p.sku ? `${p.name} (${p.sku})` : p.name;
            sdProductsList.appendChild(li);
        });

        supplierDetailsModal.classList.remove('hidden');
    }

    function closeSupplierDetails() {
        supplierDetailsModal.classList.add('hidden');
    }

    function fetchAndOpenDetailsById(id) {
        const url = "{{ url('admin/suppliers') }}/" + id + "/details";

        fetch(url, {
            headers: { 'Accept': 'application/json' },
        })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw data;
            }
            openSupplierDetails(data);
        })
        .catch(err => {
            console.error('Supplier details error:', err);
            alert('Could not load supplier details.');
        });
    }

    // ========= ROW CLICK -> FETCH DETAILS =========
    document.querySelectorAll('.supplier-row').forEach(row => {
        row.addEventListener('click', (e) => {
            if (e.target.closest('button') || e.target.closest('input[type="checkbox"]')) {
                return;
            }

            const id = row.dataset.id;
            fetchAndOpenDetailsById(id);
        });
    });

    // Close supplier details modal
    document.querySelectorAll('.supplier-details-close').forEach(btn => {
        btn.addEventListener('click', closeSupplierDetails);
    });

    supplierDetailsModal.addEventListener('click', (e) => {
        if (e.target === supplierDetailsModal) {
            closeSupplierDetails();
        }
    });

    // From supplier details -> open message modal
    sdSendEmail.addEventListener('click', () => {
        if (!currentDetailEmail) {
            alert('This supplier does not have an email address.');
            return;
        }
        closeSupplierDetails();
        openMessageModal('email', {
            email: currentDetailEmail,
            name: sdName.textContent,
        });
    });

    sdSendSms.addEventListener('click', () => {
        if (!currentDetailPhone) {
            alert('This supplier does not have a phone number.');
            return;
        }
        closeSupplierDetails();
        openMessageModal('sms', {
            phone: currentDetailPhone,
            name: sdName.textContent,
        });
    });

    // ========= SINGLE ROW EMAIL/SMS BUTTONS =========
    document.querySelectorAll('[data-open-email]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            openMessageModal('email', {
                email: btn.dataset.email,
                name: btn.dataset.name,
            });
        });
    });

    document.querySelectorAll('[data-open-sms]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            openMessageModal('sms', {
                phone: btn.dataset.phone,
                name: btn.dataset.name,
            });
        });
    });

    // ========= SELECT ALL CHECKBOX =========
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const checked = this.checked;
            getRowCheckboxes().forEach(cb => {
                cb.checked = checked;
            });
        });
    }

    // ========= BULK MESSAGE =========
    if (bulkMessageBtn) {
        bulkMessageBtn.addEventListener('click', () => {
            const allCheckboxes = getRowCheckboxes();
            const selected = allCheckboxes.filter(cb => cb.checked);

            const targets = selected.length ? selected : allCheckboxes;

            const emails = targets
                .map(cb => cb.dataset.email)
                .filter(e => e && e.trim().length > 0);

            const phones = targets
                .map(cb => cb.dataset.phone)
                .filter(p => p && p.trim().length > 0);

            if (emails.length === 0 && phones.length === 0) {
                alert('No email or phone data available for the selected suppliers.');
                return;
            }

            const label = selected.length
                ? `${selected.length} selected supplier(s)`
                : `${targets.length} supplier(s) on this page`;

            openMessageModal('email', {
                email: emails.join(', '),
                title: `Message ${label}`,
            });

            smsTo.value = phones.join(', ');
        });
    }

    // ========= CLOSE MESSAGE MODAL =========
    document.querySelectorAll('.modal-close, .modal-cancel').forEach(btn => {
        btn.addEventListener('click', closeMessageModal);
    });

    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) {
            closeMessageModal();
        }
    });

    // ========= TAB SWITCHING =========
    tabs.forEach(tab => {
        tab.addEventListener('click', () => setActiveTab(tab.dataset.tab));
    });

    // ========= SEND EMAIL VIA BACKEND =========
    sendEmailBtn.addEventListener('click', () => {
        const payload = {
            to: emailTo.value,
            cc: document.getElementById('email_cc').value,
            bcc: document.getElementById('email_bcc').value,
            subject: emailSubject.value,
            message: emailMessage.value,
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch("{{ route('admin.messages.email') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw data;
            }
            alert(data.message || 'Email sent successfully.');
            closeMessageModal();
        })
        .catch(err => {
            console.error('Email send error:', err);
            alert(err.message || 'Failed to send email.');
        });
    });

    // ========= SEND SMS VIA BACKEND =========
    sendSmsBtn.addEventListener('click', () => {
        const payload = {
            phone: smsTo.value,
            message: smsMessage.value,
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch("{{ route('admin.messages.sms') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw data;
            }
            alert(data.message || 'Text message(s) sent successfully.');
            closeMessageModal();
        })
        .catch(err => {
            console.error('SMS send error:', err);
            alert(err.message || 'Failed to send text message.');
        });
    });

    // ========= DELETE SUPPLIER (ADMIN ONLY) =========
    if (deleteModal && deleteForm && deleteText) {
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id   = btn.dataset.id;
                const name = btn.dataset.name;

                deleteText.textContent = `Are you sure you want to delete "${name}"?`;
                deleteForm.action = "{{ url('admin/suppliers') }}/" + id;

                deleteModal.classList.remove('hidden');
            });
        });

        document.querySelectorAll('.delete-modal-close, .delete-modal-cancel').forEach(btn => {
            btn.addEventListener('click', () => {
                deleteModal.classList.add('hidden');
            });
        });

        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.classList.add('hidden');
            }
        });
    }
});
</script>
@endpush
