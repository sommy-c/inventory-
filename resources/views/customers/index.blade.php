@extends('admin.layout')

@section('title', 'Customers')

@section('content')
<div class="customers-page">

    <div class="page-header">
        <h1>Customers</h1>

        <form action="{{ route('admin.customers.index') }}" method="GET" class="search-form">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Search by name, email, phone..."
            >
            <button type="submit">Search</button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid-layout">
        {{-- Customers table --}}
       {{-- Customers table --}}
<div class="card table-card">
    <div class="card-header">
        <h2>Customer List</h2>
        <span class="badge">{{ $customers->total() }} total</span>
    </div>

    {{-- Bulk message button + hint --}}
    <div class="bulk-actions">
        <button type="button" class="btn-primary" id="bulkMessageBtn">
            Message selected / all
        </button>
        <span class="bulk-hint">
            Select customers in the table, or leave none selected to target all on this page.
        </span>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="selectAllCustomers">
                    </th>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    {{-- phone/address removed from table --}}
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            @forelse($customers as $index => $customer)
                <tr
                    class="customer-row"
                    data-name="{{ $customer->name }}"
                    data-email="{{ $customer->email }}"
                    data-phone="{{ $customer->phone }}"
                    data-address="{{ $customer->address }}"
                    data-created="{{ optional($customer->created_at)->format('Y-m-d H:i') }}"
                    data-last-visit="{{ optional($customer->last_visit)->format('Y-m-d H:i') }}"
                >
                    <td>
                        <input
                            type="checkbox"
                            class="select-customer"
                            data-email="{{ $customer->email }}"
                            data-phone="{{ $customer->phone }}"
                            data-name="{{ $customer->name }}"
                            onclick="event.stopPropagation();"
                        >
                    </td>
                    <td>{{ $customers->firstItem() + $index }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->email ?: '—' }}</td>
                    <td>{{ optional($customer->created_at)->format('Y-m-d') }}</td>
                    <td class="actions-cell">
                        {{-- Email button --}}
                        <button
                            type="button"
                            class="btn-small btn-email"
                            data-open-email="1"
                            data-email="{{ $customer->email }}"
                            data-name="{{ $customer->name }}"
                            @if(!$customer->email) disabled @endif
                            onclick="event.stopPropagation();"
                        >
                            Email
                        </button>

                        {{-- Text button --}}
                        <button
                            type="button"
                            class="btn-small btn-sms"
                            data-open-sms="1"
                            data-phone="{{ $customer->phone }}"
                            data-name="{{ $customer->name }}"
                            @if(!$customer->phone) disabled @endif
                            onclick="event.stopPropagation();"
                        >
                            Text
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;">No customers found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $customers->links() }}
    </div>
</div>


        {{-- New customer form --}}
        <div class="card form-card-custom">
            <h3>Add New Customer</h3>

            <form action="{{ route('admin.customers.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
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

                <button type="submit" class="submit-btn">Save Customer</button>
            </form>
        </div>
    </div>
</div>


{{-- =============== CUSTOMER DETAILS MODAL =============== --}}
<div id="customerDetailsModal" class="modal-overlay hidden">
    <div class="modal-card modal-card-sm">
        <div class="modal-header">
            <h2 id="cd_name">Customer Details</h2>
            <button type="button" class="modal-close customer-details-close" aria-label="Close">&times;</button>
        </div>

        <div class="modal-body customer-details-body">
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value" id="cd_email">—</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span class="detail-value" id="cd_phone">—</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Address:</span>
                <span class="detail-value" id="cd_address">—</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created at:</span>
                <span class="detail-value" id="cd_created">—</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Last visit:</span>
                <span class="detail-value" id="cd_last_visit">—</span>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-secondary customer-details-close">Close</button>
            <button type="button" class="btn-primary" id="cd_sendEmailBtn">Email</button>
            <button type="button" class="btn-primary" id="cd_sendSmsBtn">Text</button>
        </div>
    </div>
</div>


{{-- ================= MODAL: MESSAGE USERS ================= --}}
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
            <form id="emailForm" class="modal-form" action="#" method="POST">
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

            {{-- SMS / Text form --}}
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


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ========== MESSAGE MODAL (existing) ==========
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

    // Bulk button + checkboxes
    const bulkMessageBtn   = document.getElementById('bulkMessageBtn');
    const selectAllCheckbox = document.getElementById('selectAllCustomers');

    // ========== CUSTOMER DETAILS MODAL (NEW) ==========
    const customerDetailsModal = document.getElementById('customerDetailsModal');
    const cdName       = document.getElementById('cd_name');
    const cdEmail      = document.getElementById('cd_email');
    const cdPhone      = document.getElementById('cd_phone');
    const cdAddress    = document.getElementById('cd_address');
    const cdCreated    = document.getElementById('cd_created');
    const cdLastVisit  = document.getElementById('cd_last_visit');
    const cdSendEmail  = document.getElementById('cd_sendEmailBtn');
    const cdSendSms    = document.getElementById('cd_sendSmsBtn');

    let currentDetailEmail = '';
    let currentDetailPhone = '';

    function getRowCheckboxes() {
        return Array.from(document.querySelectorAll('.select-customer'));
    }

    function openModal(forType, data) {
        // Reset forms
        emailForm.reset();
        smsForm.reset();
        emailSubject.value = '';
        emailMessage.value = '';
        smsMessage.value   = '';

        if (forType === 'email') {
            setActiveTab('email');
            emailTo.value = data.email || '';
            modalTitle.textContent = data.title || `Email ${data.name || ''}`.trim();
        } else {
            setActiveTab('sms');
            smsTo.value = data.phone || '';
            modalTitle.textContent = data.title || `Text ${data.name || ''}`.trim();
        }

        modalOverlay.classList.remove('hidden');
    }

    function closeModal() {
        modalOverlay.classList.add('hidden');
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

    // ========== CUSTOMER DETAILS HELPERS (NEW) ==========
    function openCustomerDetailsModal(data) {
        currentDetailEmail = data.email || '';
        currentDetailPhone = data.phone || '';

        cdName.textContent      = data.name || 'Customer Details';
        cdEmail.textContent     = data.email || '—';
        cdPhone.textContent     = data.phone || '—';
        cdAddress.textContent   = data.address || '—';
        cdCreated.textContent   = data.created || '—';
        cdLastVisit.textContent = data.last_visit || '—';

        customerDetailsModal.classList.remove('hidden');
    }

    function closeCustomerDetailsModal() {
        customerDetailsModal.classList.add('hidden');
    }

    // Make table rows clickable to show full customer details
    document.querySelectorAll('.customer-row').forEach(row => {
        row.addEventListener('click', (e) => {
            // Safety: ignore if click is on a button or checkbox
            if (e.target.closest('button') || e.target.closest('input[type="checkbox"]')) {
                return;
            }

            openCustomerDetailsModal({
                name:       row.dataset.name,
                email:      row.dataset.email,
                phone:      row.dataset.phone,
                address:    row.dataset.address,
                created:    row.dataset.created,
                last_visit: row.dataset.lastVisit,
            });
        });
    });

    // Close details modal
    document.querySelectorAll('.customer-details-close').forEach(btn => {
        btn.addEventListener('click', closeCustomerDetailsModal);
    });

    // Close details modal when clicking overlay
    if (customerDetailsModal) {
        customerDetailsModal.addEventListener('click', (e) => {
            if (e.target === customerDetailsModal) {
                closeCustomerDetailsModal();
            }
        });
    }

    // From details modal: jump into message modal (email)
    if (cdSendEmail) {
        cdSendEmail.addEventListener('click', () => {
            if (!currentDetailEmail) {
                alert('This customer does not have an email address.');
                return;
            }
            closeCustomerDetailsModal();
            openModal('email', {
                email: currentDetailEmail,
                name: cdName.textContent,
            });
        });
    }

    // From details modal: jump into message modal (sms)
    if (cdSendSms) {
        cdSendSms.addEventListener('click', () => {
            if (!currentDetailPhone) {
                alert('This customer does not have a phone number.');
                return;
            }
            closeCustomerDetailsModal();
            openModal('sms', {
                phone: currentDetailPhone,
                name: cdName.textContent,
            });
        });
    }

    // ========== OPEN MESSAGE MODAL FROM ROW BUTTONS ==========
    document.querySelectorAll('[data-open-email]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation(); // don't trigger row click
            openModal('email', {
                email: btn.dataset.email,
                name: btn.dataset.name,
            });
        });
    });

    document.querySelectorAll('[data-open-sms]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            openModal('sms', {
                phone: btn.dataset.phone,
                name: btn.dataset.name,
            });
        });
    });

    // ========== SELECT-ALL + BULK MESSAGE ==========
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const checked = this.checked;
            getRowCheckboxes().forEach(cb => {
                cb.checked = checked;
            });
        });
    }

    if (bulkMessageBtn) {
        bulkMessageBtn.addEventListener('click', () => {
            const allCheckboxes = getRowCheckboxes();
            const selected = allCheckboxes.filter(cb => cb.checked);

            // If none selected, use all on current page
            const targets = selected.length ? selected : allCheckboxes;

            const emails = targets
                .map(cb => cb.dataset.email)
                .filter(e => e && e.trim().length > 0);

            const phones = targets
                .map(cb => cb.dataset.phone)
                .filter(p => p && p.trim().length > 0);

            if (emails.length === 0 && phones.length === 0) {
                alert('No email or phone data available for the selected customers.');
                return;
            }

            const label = selected.length
                ? `${selected.length} selected customer(s)`
                : `${targets.length} customer(s) on this page`;

            // Default: open on Email tab with all emails pre-filled
            openModal('email', {
                email: emails.join(', '),
                title: `Message ${label}`,
            });

            // Also pre-fill SMS phone list (user can switch tab)
            smsTo.value = phones.join(', ');
        });
    }

    // ========== MESSAGE MODAL CLOSE / SWITCH / SEND ==========
    document.querySelectorAll('.modal-close, .modal-cancel').forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    // Close on background click for message modal
    if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                closeModal();
            }
        });
    }

    // Tab switching (email/text)
    tabs.forEach(tab => {
        tab.addEventListener('click', () => setActiveTab(tab.dataset.tab));
    });

    // For now, just demo what would be sent.
    // Hook up to your backend (Mail / SMS provider) later.

       // For now, just demo what would be sent.
    // Hook up to your backend (Mail / SMS provider) later.

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
            closeModal();
        })
        .catch(err => {
            console.error('Email send error:', err);
            alert(err.message || 'Failed to send email.');
        });
    });

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
            closeModal();
        })
        .catch(err => {
            console.error('SMS send error:', err);
            alert(err.message || 'Failed to send text message.');
        });
    });

});
</script>
@endpush
