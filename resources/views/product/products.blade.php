@extends('admin.layout')
@section('title','Products')



@section('content')
<div class="products-container">
    <h3 class="page-title">Products</h3>
 {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <!-- Search Bar -->
    <form method="GET" action="{{ url()->current() }}" class="search-form">
        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search products by name, SKU, category..." autofocus>
        <button type="submit">Search</button>
        
    </form>

    <!-- Add Product Button (Admin Only) -->
    @if(auth()->user()->hasRole('admin'))
    <div class="button-wrapper">
        <a href="{{ route('admin.products.create') }}" class="create-btn">+ Add Product</a>
    </div>
    @endif

    <!-- Products Table -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Supplier</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    @if(auth()->user()->hasAnyRole(['admin','manager']))
                    <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td data-label="SKU">{{ $product->sku }}</td>
                    <td data-label="Name">
                        <a href="{{ auth()->user()->hasRole('cashier') 
                                   ? route('cashier.products.show', $product->id) 
                                   : route('admin.products.show', $product->id) }}">
                            {{ $product->name }}
                        </a>
                    </td>
                    <td data-label="Category">{{ $product->category ?? '-' }}</td>
                    <td data-label="Brand">{{ $product->brand ?? '-' }}</td>
                    <td data-label="Supplier">{{ $product->supplier ?? '-' }}</td>
                    <td data-label="Quantity">{{ $product->quantity }}</td>
                    <td data-label="Status">{{ ucfirst($product->status) }}</td>

                    @if(auth()->user()->hasAnyRole(['admin','manager']))
                    <td data-label="Actions">
                        <!-- Edit (Admin & Manager) -->
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="edit-btn">Edit</a>

                        <!-- Toggle suspend/activate (Admin & Manager) -->
                        <form action="{{ route('admin.products.toggle', $product->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="toggle-btn">
                                {{ $product->is_suspended ? 'Activate' : 'Suspend' }}
                            </button>
                        </form>

                        <!-- Delete (Admin Only) -->
                        @if(auth()->user()->hasRole('admin'))
                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this product?');">
                                Delete
                            </button>
                        </form>
                        @endif
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;">No products found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper">
        {{ $products->withQueryString()->links() }}
    </div>
</div>


<!-- Full Page Loading Overlay -->
<div id="loading-overlay">
    <div class="spinner"></div>
</div>

<!-- Styles (unchanged from previous version) -->
<style>
.products-container { padding: 20px; min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: rgba(255,255,255,0.05); color: #fff; }
.page-title { font-size: 28px; font-weight: 600; margin-bottom: 20px; text-align: center; }
.search-form { display: flex; margin-bottom: 15px; }
.search-form input { flex: 1; padding: 8px 12px; border-radius: 6px 0 0 6px; border: none; outline: none; font-size: 0.9rem; }
.search-form button { padding: 8px 16px; border: none; background-color: rgba(37, 99, 235, 0.8); color: #fff; font-weight: 600; border-radius: 0 6px 6px 0; cursor: pointer; transition: 0.2s; }
.search-form button:hover { background-color: rgba(37, 99, 235, 1); }
.button-wrapper { margin-bottom: 15px; }
.create-btn { display: inline-block; padding: 10px 20px; background-color: rgba(37, 99, 235, 0.8); color: #fff; font-weight: 600; border-radius: 6px; text-decoration: none; transition: 0.2s; }
.create-btn:hover { background-color: rgba(37, 99, 235, 1); }
.table-wrapper { overflow-x: auto; background: rgba(255,255,255,0.1); border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
table { width: 100%; border-collapse: collapse; min-width: 700px; }
thead { background-color: rgba(37, 99, 235, 0.8); color: #fff; }
thead th { padding: 12px 15px; text-align: left; font-weight: 600; }
tbody tr { border-bottom: 1px solid rgba(255,255,255,0.2); transition: background 0.2s ease; }
tbody tr:hover { background-color: rgba(255,255,255,0.1); }
tbody td { padding: 12px 15px; }
.edit-btn, .delete-btn, .toggle-btn { display: inline-block; padding: 6px 12px; margin-right: 5px; font-size: 0.9rem; font-weight: 600; border-radius: 6px; text-decoration: none; border: none; cursor: pointer; transition: background 0.3s ease, transform 0.2s ease; }
.edit-btn { background-color: rgba(16, 185, 129, 0.8); color: #fff; } .edit-btn:hover { background-color: rgba(16, 185, 129,1); transform: translateY(-1px); }
.toggle-btn { background-color: rgba(234, 179, 8, 0.8); color: #fff; } .toggle-btn:hover { background-color: rgba(234, 179, 8,1); transform: translateY(-1px); }
.delete-btn { background-color: rgba(239,68,68,0.8); color: #fff; } .delete-btn:hover { background-color: rgba(239,68,68,1); transform: translateY(-1px); }
.pagination-wrapper { margin-top: 15px; }
@media(max-width:768px){ table { min-width:600px; } thead th, tbody td { padding:6px 8px; font-size:0.8rem; } .edit-btn, .delete-btn, .toggle-btn, .create-btn, .search-form button { padding:4px 8px; font-size:0.75rem; } .page-title{ font-size:22px; } .search-form input { padding:6px 10px; font-size:0.85rem; } }
/* Loading Overlay */
#loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
#loading-overlay .spinner {
    border: 6px solid rgba(255,255,255,0.3);
    border-top: 6px solid #2563eb;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.querySelector('.search-form input[name="search"]');
    const searchForm = document.querySelector('.search-form');
    const loadingOverlay = document.getElementById('loading-overlay');

    if (searchInput && searchForm && loadingOverlay) {
        let lastTime = 0;
        let buffer = "";

        searchInput.focus();

        searchInput.addEventListener("keydown", function(e) {
            const now = Date.now();

            if (now - lastTime < 50) {
                buffer += e.key;
            } else {
                buffer = e.key;
            }

            lastTime = now;

            if (e.key === "Enter") {
                e.preventDefault();
                searchInput.value = buffer.trim();
                buffer = "";

                // Show full-page loading
                loadingOverlay.style.display = 'flex';
                searchForm.submit();
            }
        });

        // Show loading on button click
        searchForm.addEventListener('submit', function() {
            loadingOverlay.style.display = 'flex';
        });
    }
});
</script>
@endsection
