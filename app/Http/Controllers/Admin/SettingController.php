<?php

// app/Http/Controllers/Admin/SettingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
 
    // ---------------- GENERAL ----------------
    public function general()
    {
        return view('settings.general', [
            'store_name'   => Setting::get('store_name', config('app.name', 'My Store')),
            'store_email'  => Setting::get('store_email'),
            'store_phone'  => Setting::get('store_phone'),
            'store_address'=> Setting::get('store_address'),
        ]);
    }

    public function updateGeneral(Request $request)
    {
        $data = $request->validate([
            'store_name'    => 'required|string|max:255',
            'store_email'   => 'nullable|email|max:255',
            'store_phone'   => 'nullable|string|max:50',
            'store_address' => 'nullable|string|max:500',
        ]);

        Setting::set('store_name',    $data['store_name']);
        Setting::set('store_email',   $data['store_email'] ?? null);
        Setting::set('store_phone',   $data['store_phone'] ?? null);
        Setting::set('store_address', $data['store_address'] ?? null);

        return back()->with('success', 'General settings updated.');
    }

    // ---------------- BRANDING ----------------
    public function branding()
    {
        return view('settings.branding', [
            'logo_path'    => Setting::get('logo_path'),
            'favicon_path' => Setting::get('favicon_path'),
        ]);
    }

    public function updateBranding(Request $request)
    {
        $data = $request->validate([
            'logo'    => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:1024',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('settings', 'public');
            Setting::set('logo_path', $path);
        }

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('settings', 'public');
            Setting::set('favicon_path', $path);
        }

        return back()->with('success', 'Branding updated.');
    }

    // ---------------- APPEARANCE ----------------
    public function appearance()
    {
        return view('settings.appearance', [
            'theme_mode'    => Setting::get('theme_mode', 'dark'), // dark | light
            'primary_color' => Setting::get('primary_color', '#2563eb'),
            'sidebar_style' => Setting::get('sidebar_style', 'compact'), // compact | full
        ]);
    }

    public function updateAppearance(Request $request)
{
    $data = $request->validate([
        'theme_mode'    => 'required|in:dark,light',
        'primary_color' => 'required|string|max:20',
        'sidebar_style' => 'required|in:compact,full',
    ]);

    Setting::set('theme_mode',    $data['theme_mode']);
    Setting::set('primary_color', $data['primary_color']);
    Setting::set('sidebar_style', $data['sidebar_style']);

    return back()->with('success', 'Appearance updated.');
}

    // ---------------- VAT / TAX ----------------
    public function vat()
    {
        return view('settings.vat', [
            'vat_rate'       => Setting::vatPercent(),           // e.g. 7.5
            'vat_inclusive'  => Setting::get('vat_inclusive', '1'), // 1 or 0
            'vat_label'      => Setting::get('vat_label', 'VAT'),
        ]);
    }

    public function updateVat(Request $request)
    {
        $data = $request->validate([
            'vat_rate'      => 'required|numeric|min:0|max:100',
            'vat_inclusive' => 'nullable|boolean',
            'vat_label'     => 'nullable|string|max:50',
        ]);

        Setting::set('vat_rate', (string) $data['vat_rate']);
        Setting::set('vat_inclusive', $request->boolean('vat_inclusive') ? '1' : '0');
        Setting::set('vat_label', $data['vat_label'] ?? 'VAT');

        return back()->with('success', 'VAT settings updated.');
    }

    // ---------------- CURRENCY & EXCHANGE ----------------
    public function currencyExchange()
    {
        return view('settings.currency-exchange', [
            'currency_code'    => Setting::get('currency_code', 'NGN'),
            'currency_symbol'  => Setting::get('currency_symbol', '₦'),
            'exchange_rate'    => Setting::get('exchange_rate', '1'),
            'show_currency_code'=> Setting::get('show_currency_code', '0'),
        ]);
    }

    public function updateCurrencyExchange(Request $request)
    {
        $data = $request->validate([
            'currency_code'   => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:5',
            'exchange_rate'   => 'required|numeric|min:0.0001',
            'show_currency_code' => 'nullable|boolean',
        ]);

        Setting::set('currency_code', $data['currency_code']);
        Setting::set('currency_symbol', $data['currency_symbol']);
        Setting::set('exchange_rate', (string) $data['exchange_rate']);
        Setting::set('show_currency_code', $request->boolean('show_currency_code') ? '1' : '0');

        return back()->with('success', 'Currency & exchange settings updated.');
    }

    // ---------------- RECEIPT / POS ----------------
    public function receipt()
    {
        return view('settings.receipt', [
            'receipt_footer'         => Setting::get('receipt_footer', 'Thank you for shopping!'),
            'show_vat_on_receipt'    => Setting::get('show_vat_on_receipt', '1'),
            'show_customer_on_receipt'=> Setting::get('show_customer_on_receipt', '1'),
            'enable_whatsapp_receipt'=> Setting::get('enable_whatsapp_receipt', '0'),
        ]);
    }

    public function updateReceipt(Request $request)
    {
        $data = $request->validate([
            'receipt_footer'          => 'nullable|string|max:255',
            'show_vat_on_receipt'     => 'nullable|boolean',
            'show_customer_on_receipt'=> 'nullable|boolean',
            'enable_whatsapp_receipt' => 'nullable|boolean',
        ]);

        Setting::set('receipt_footer', $data['receipt_footer'] ?? 'Thank you for shopping!');
        Setting::set('show_vat_on_receipt', $request->boolean('show_vat_on_receipt') ? '1' : '0');
        Setting::set('show_customer_on_receipt', $request->boolean('show_customer_on_receipt') ? '1' : '0');
        Setting::set('enable_whatsapp_receipt', $request->boolean('enable_whatsapp_receipt') ? '1' : '0');

        return back()->with('success', 'Receipt / POS settings updated.');
    }

    // ---------------- NOTIFICATIONS ----------------
    public function notifications()
    {
        return view('settings.notifications', [
            'low_stock_threshold'      => Setting::get('low_stock_threshold', '5'),
            'notify_admin_email'       => Setting::get('notify_admin_email'),
            'notify_on_low_stock'      => Setting::get('notify_on_low_stock', '1'),
            'notify_on_daily_summary'  => Setting::get('notify_on_daily_summary', '0'),
        ]);
    }

    public function updateNotifications(Request $request)
    {
        $data = $request->validate([
            'low_stock_threshold'     => 'required|integer|min:1',
            'notify_admin_email'      => 'nullable|email|max:255',
            'notify_on_low_stock'     => 'nullable|boolean',
            'notify_on_daily_summary' => 'nullable|boolean',
        ]);

        Setting::set('low_stock_threshold', (string) $data['low_stock_threshold']);
        Setting::set('notify_admin_email', $data['notify_admin_email'] ?? null);
        Setting::set('notify_on_low_stock', $request->boolean('notify_on_low_stock') ? '1' : '0');
        Setting::set('notify_on_daily_summary', $request->boolean('notify_on_daily_summary') ? '1' : '0');

        return back()->with('success', 'Notification settings updated.');
    }
}
