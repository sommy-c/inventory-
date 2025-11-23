<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale; // 👈 add this
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q');

        // 1) Get customers (NO with('sales') here)
        $customers = Customer::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // 2) Attach last_visit per customer using email/phone/name
        $customers->getCollection()->transform(function ($customer) {
            $lastSale = Sale::query()
                ->when($customer->email, function ($q) use ($customer) {
                    $q->orWhere('customer_email', $customer->email);
                })
                ->when($customer->phone, function ($q) use ($customer) {
                    $q->orWhere('customer_phone', $customer->phone);
                })
                ->orWhere('customer_name', $customer->name)
                ->latest('created_at')
                ->first();

            // Add a dynamic property so Blade can use it
            $customer->last_visit = optional($lastSale)->created_at;

            return $customer;
        });

        return view('customers.index', [
            'customers' => $customers,
            'search'    => $search,
        ]);
    }

   public function store(Request $request)
{
    $data = $request->validate([
        'name'   => 'required|string|max:255',
        'email'  => 'nullable|email|max:255|unique:customers,email',
        'phone'  => 'nullable|string|max:50',
        'address'=> 'nullable|string|max:500',
    ]);

    Customer::create($data);

    return redirect()
        ->route('admin.customers.index')
        ->with('success', 'Customer created successfully.');
}

}
