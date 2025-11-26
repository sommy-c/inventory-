<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS System</title>
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
  
       
</head>


<style>
:root {
    --orange-main: #c05621;
    --orange-strong: #9a3412;
    --orange-light: #f97316;
    --orange-light-hover: #ea580c;
    --border-soft: rgba(192,132,45,0.35);
    --muted-text: #7c2d12;
}

/* ========== BODY / GLOBAL ========== */
body.theme-light {
    background: #faf5f0;
    color: var(--orange-main);
}

body.theme-dark {
    background: #020617;
    color: #f9fafb;
}

/* prevent any heavy overlays coming from global css (if any) */
body.theme-light::after {
    background: rgba(255,255,255,0.08);
}

/* ========== FLASH MESSAGE ========== */
#flash-message.flash-message {
    position: fixed;
    top: 10px;
    right: 10px;
    z-index: 9000;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 14px;
}

/* light */
body.theme-light #flash-message.flash-message {
    background: rgba(255,255,255,0.95);
    color: var(--orange-main);
    border: 1px solid var(--border-soft);
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

/* dark */
body.theme-dark #flash-message.flash-message {
    background: rgba(15,23,42,0.95);
    color: #e5e7eb;
    border: 1px solid rgba(148,163,184,0.5);
}

/* ========== POS LAYOUT ========== */
.pos-wrapper {
    display: flex;
    gap: 16px;
    max-width: 1280px;
    margin: 16px auto;
    padding: 12px;
    box-sizing: border-box;
}

/* light wrapper card */
body.theme-light .pos-wrapper {
    background: rgba(255,255,255,0.22);
    border-radius: 18px;
    box-shadow: 0 18px 45px rgba(0,0,0,0.06);
}

/* dark wrapper */
body.theme-dark .pos-wrapper {
    background: rgba(15,23,42,0.92);
    border-radius: 18px;
    box-shadow: 0 18px 45px rgba(0,0,0,0.7);
}

/* SIDEBAR */
.pos-sidebar {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 12px;
    min-width: 190px;
    max-width: 240px;
    border-radius: 14px;
    box-sizing: border-box;
}

/* light sidebar */
body.theme-light .pos-sidebar {
    background: rgba(255,255,255,0.92);
    border: 1px solid var(--border-soft);
}

/* dark sidebar */
body.theme-dark .pos-sidebar {
    background: rgba(15,23,42,0.96);
    border: 1px solid rgba(30,64,175,0.7);
}

/* MAIN PANEL */
.pos-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-width: 0; /* allow shrinking */
}

/* SECTIONS */
.top-info,
.table-area,
.totals-area {
    border-radius: 14px;
    padding: 12px 14px;
    box-sizing: border-box;
}

/* light sections */
body.theme-light .top-info,
body.theme-light .table-area,
body.theme-light .totals-area {
    background: rgba(255,255,255,0.95);
    border: 1px solid var(--border-soft);
}

/* dark sections */
body.theme-dark .top-info,
body.theme-dark .table-area,
body.theme-dark .totals-area {
    background: rgba(15,23,42,0.96);
    border: 1px solid rgba(31,41,55,0.9);
}

/* ========== SIDEBAR BUTTONS ========== */
.side-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 10px;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 600;
    border: 1px solid transparent;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s ease, color 0.15s ease,
                border-color 0.15s ease, transform 0.1s ease,
                box-shadow 0.15s ease;
}

/* dark default side buttons */
body.theme-dark .side-btn {
    background: rgba(15,23,42,0.98);
    color: #e5e7eb;
    border-color: rgba(55,65,81,0.9);
}
body.theme-dark .side-btn:hover {
    background: rgba(37,99,235,0.9);
    border-color: rgba(59,130,246,1);
    transform: translateY(-1px);
}

/* light default side buttons */
body.theme-light .side-btn {
    background: rgba(255,255,255,0.98);
    color: var(--orange-main);
    border-color: var(--border-soft);
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}
body.theme-light .side-btn:hover {
    background: var(--orange-light);
    color: #fff;
    border-color: var(--orange-light-hover);
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(248,148,6,0.25);
}

/* danger button (Cancel) */
body.theme-dark .side-btn.danger {
    background: rgba(239,68,68,0.15);
    color: #fecaca;
    border-color: rgba(239,68,68,0.8);
}
body.theme-dark .side-btn.danger:hover {
    background: rgba(239,68,68,0.3);
}

body.theme-light .side-btn.danger {
    background: rgba(248,113,113,0.08);
    color: #b91c1c;
    border-color: rgba(239,68,68,0.8);
}
body.theme-light .side-btn.danger:hover {
    background: rgba(248,113,113,0.22);
    color: #7f1d1d;
}

/* ========== TOP INFO BAR ========== */
.top-info {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.top-info #add-customer-btn {
    padding: 8px 14px;
    border-radius: 999px;
    border: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease, transform 0.1s ease;
}

/* dark */
body.theme-dark .top-info #add-customer-btn {
    background: rgba(37,99,235,0.9);
    color: #f9fafb;
}
body.theme-dark .top-info #add-customer-btn:hover {
    background: rgba(37,99,235,1);
    transform: translateY(-1px);
}

/* light */
body.theme-light .top-info #add-customer-btn {
    background: var(--orange-light);
    color: #fff;
}
body.theme-light .top-info #add-customer-btn:hover {
    background: var(--orange-light-hover);
    transform: translateY(-1px);
}

/* info boxes */
.info-box {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 140px;
    flex: 1 1 140px;
}

.info-box label {
    font-size: 12px;
    font-weight: 600;
}

/* inputs / selects in info box */
.info-box input,
.info-box select {
    padding: 6px 8px;
    border-radius: 8px;
    border: 1px solid transparent;
    font-size: 13px;
    outline: none;
}

/* dark */
body.theme-dark .info-box input,
body.theme-dark .info-box select {
    background: #020617;
    color: #e5e7eb;
    border-color: rgba(55,65,81,0.9);
}

/* light */
body.theme-light .info-box input,
body.theme-light .info-box select {
    background: rgba(255,255,255,0.98);
    color: var(--orange-main);
    border-color: rgba(209,213,219,0.9);
}

/* time box text */
.time-box p {
    font-size: 13px;
}

/* ========== PRODUCT SEARCH / TABLE ========== */
.search-wrapper {
    margin-bottom: 8px;
    position: relative;
}

/* search input */
#product-search {
    width: 100%;
    padding: 9px 10px;
    border-radius: 8px;
    border: 1px solid transparent;
    font-size: 14px;
    outline: none;
}

/* dark */
body.theme-dark #product-search {
    background: #020617;
    color: #e5e7eb;
    border-color: rgba(55,65,81,0.9);
}

/* light */
body.theme-light #product-search {
    background: rgba(255,255,255,0.98);
    color: var(--orange-main);
    border-color: rgba(209,213,219,0.9);
}

/* search results dropdown (basic) */
#search-results {
    position: absolute;
    left: 0;
    right: 0;
    top: calc(100% + 4px);
    list-style: none;
    max-height: 200px;
    overflow-y: auto;
    border-radius: 8px;
    padding: 4px 0;
    margin: 0;
    z-index: 50;
}

/* dark */
body.theme-dark #search-results {
    background: rgba(15,23,42,0.98);
    border: 1px solid rgba(55,65,81,0.9);
}

/* light */
body.theme-light #search-results {
    background: rgba(255,255,255,0.98);
    border: 1px solid rgba(209,213,219,0.9);
}

/* table wrapper for responsiveness */
.table-area {
    overflow-x: auto;
}

/* table */
.pos-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 720px;
}

/* header */
.pos-table thead {
    background: linear-gradient(90deg, #1d4ed8, #2563eb);
}

/* light header tweak */
body.theme-light .pos-table thead {
    background: linear-gradient(90deg, #fed7aa, #fdba74);
}

.pos-table th,
.pos-table td {
    padding: 10px 8px;
    text-align: left;
    font-size: 13px;
}

/* head text */
body.theme-dark .pos-table th {
    color: #f9fafb;  /* ✅ light text on dark gradient */
}
body.theme-light .pos-table th {
    color: var(--orange-strong);
}

/* rows */
body.theme-dark .pos-table tbody tr {
    border-bottom: 1px solid rgba(55,65,81,0.85);
}
body.theme-dark .pos-table tbody tr:nth-child(even) {
    background: rgba(15,23,42,0.92);
}
body.theme-dark .pos-table tbody tr:nth-child(odd) {
    background: rgba(15,23,42,0.98);
}
body.theme-dark .pos-table tbody tr:hover {
    background: rgba(30,64,175,0.35);
}

/* light rows */
body.theme-light .pos-table tbody tr {
    border-bottom: 1px solid rgba(229,231,235,0.9);
}
body.theme-light .pos-table tbody tr:nth-child(even) {
    background: rgba(255,255,255,0.98);
}
body.theme-light .pos-table tbody tr:nth-child(odd) {
    background: rgba(255,255,255,0.93);
}
body.theme-light .pos-table tbody tr:hover {
    background: rgba(254,243,199,0.85);
}

/* text */
body.theme-dark .pos-table td {
    color: #e5e7eb;
}
body.theme-light .pos-table td {
    color: var(--orange-main);
}

/* ========== TOTALS AREA ========== */
.totals-area {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: stretch;
    flex-wrap: wrap;
    margin-top: 10px;
}

.small-totals {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 220px;
}

.small-totals > div {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    padding: 4px 8px;
    border-radius: 8px;
}

/* light theme styling */
body.theme-light .small-totals > div {
    background: rgba(255,255,255,0.9);
    border: 1px solid var(--border-soft);
}

/* dark theme styling */
body.theme-dark .small-totals > div {
    background: rgba(15,23,42,0.96);
    border: 1px solid rgba(55,65,81,0.8);
}

.small-totals label {
    font-weight: 600;
    font-size: 12px;
}

/* numbers on the right */
.small-totals span {
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}

/* highlight grand total */
body.theme-light #grand-total {
    color: var(--orange-strong);
}
body.theme-dark #grand-total {
    color: #e5e7eb;
}

.big-total {
    margin-left: auto;
    text-align: right;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 4px;
}

.big-total p {
    margin: 0;
    font-size: 13px;
    opacity: 0.8;
}

.big-total h1 {
    margin: 0;
    font-size: 26px;
    font-variant-numeric: tabular-nums;
}

/* override checkout button look to match theme (uses .side-btn) */
body.theme-light .big-total #make-payment {
    background: var(--orange-light);
    color: #fff;
    border-color: var(--orange-light-hover);
}
body.theme-light .big-total #make-payment:hover {
    background: var(--orange-light-hover);
}

/* ========== QTY WRAPPER & BUTTONS ========== */
.qty-wrapper {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* dark theme qty buttons */
body.theme-dark .qty-btn {
    min-width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid rgba(148, 163, 184, 0.7);
    background: rgba(15, 23, 42, 0.95);
    color: #e5e7eb;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: background 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
}
body.theme-dark .qty-btn:hover {
    background: rgba(37, 99, 235, 0.9);
    border-color: rgba(59, 130, 246, 1);
    transform: translateY(-1px);
}

/* light theme qty buttons */
body.theme-light .qty-btn {
    min-width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid var(--border-soft);
    background: rgba(255,255,255,0.98);
    color: var(--orange-main);
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: background 0.15s ease, border-color 0.15s ease, transform 0.1s ease, box-shadow 0.1s ease;
}
body.theme-light .qty-btn:hover {
    background: var(--orange-light);
    color: #fff;
    border-color: var(--orange-light-hover);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(248,148,6,0.3);
}

.qty-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    transform: none;
}

/* QTY INPUT */
body.theme-dark .qty-input {
    width: 50px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid rgba(148, 163, 184, 0.7);
    background: #020617;
    color: #e5e7eb;
    font-size: 14px;
    text-align: center;
    padding: 2px 4px;
}

body.theme-light .qty-input {
    width: 50px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid rgba(209,213,219,0.9);
    background: rgba(255,255,255,0.98);
    color: var(--orange-main);
    font-size: 14px;
    text-align: center;
    padding: 2px 4px;
}

.qty-input:focus {
    outline: none;
}

/* dark focus */
body.theme-dark .qty-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.7);
}

/* light focus */
body.theme-light .qty-input:focus {
    border-color: var(--orange-light);
    box-shadow: 0 0 0 1px rgba(249,115,22,0.6);
}

/* ========== DELETE ITEM BUTTON ========== */
.delete-item {
    border: none;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    white-space: nowrap;
    transition: background 0.15s ease, transform 0.1s ease, box-shadow 0.1s ease;
}

/* dark */
body.theme-dark .delete-item {
    background: rgba(239, 68, 68, 0.9);
    color: #fee2e2;
}
body.theme-dark .delete-item:hover {
    background: rgba(220, 38, 38, 1);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(220, 38, 38, 0.55);
}

/* light */
body.theme-light .delete-item {
    background: rgba(248,113,113,0.08);
    color: #b91c1c;
}
body.theme-light .delete-item:hover {
    background: rgba(248,113,113,0.22);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(248,113,113,0.4);
}

.delete-item svg,
.delete-item i {
    font-size: 14px;
}

/* small square delete button (if used) */
.delete-btn {
    background: #e3342f;
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0;
    font-size: 16px;
    transition: background 0.2s ease;
}

.delete-btn:hover {
    background: #cc1f1a;
}

.delete-btn:active {
    transform: scale(0.95);
}

.delete-icon {
    pointer-events: none;
    line-height: 1;
}

/* ========== MODALS (RECEIPT, etc.) ========== */
.modal {
    display: none; /* your JS controls visibility */
    position: fixed;
    inset: 0;
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

/* basic override backdrop */
body.theme-dark .modal {
    background: rgba(15,23,42,0.8);
}
body.theme-light .modal {
    background: rgba(15,23,42,0.35);
}

/* content */
.modal-content {
    max-width: 500px;
    width: 90%;
    border-radius: 14px;
    padding: 16px 18px 18px;
}

/* dark */
body.theme-dark .modal-content {
    background: rgba(15,23,42,0.97);
    color: #f9fafb;
    border: 1px solid rgba(148,163,184,0.6);
}

/* light */
body.theme-light .modal-content {
    background: rgba(255,255,255,0.98);
    color: var(--orange-main);
    border: 1px solid var(--border-soft);
    box-shadow: 0 18px 45px rgba(0,0,0,0.18);
}

.modal-content h3 {
    margin-top: 0;
    margin-bottom: 8px;
}

/* close button */
.modal-content .close {
    float: right;
    font-size: 20px;
    cursor: pointer;
}

/* action buttons inside modal */
.save-btn,
.print-btn {
    padding: 8px 14px;
    border-radius: 999px;
    border: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease, transform 0.1s ease;
}

/* dark save/print */
body.theme-dark .save-btn {
    background: rgba(34,197,94,0.9);
    color: #ecfdf5;
}
body.theme-dark .save-btn:hover {
    background: rgba(22,163,74,1);
    transform: translateY(-1px);
}

body.theme-dark .print-btn {
    background: rgba(37,99,235,0.9);
    color: #eff6ff;
}
body.theme-dark .print-btn:hover {
    background: rgba(37,99,235,1);
    transform: translateY(-1px);
}

/* light save/print */
body.theme-light .save-btn {
    background: rgba(22,163,74,0.08);
    color: #166534;
    border: 1px solid rgba(22,163,74,0.7);
}
body.theme-light .save-btn:hover {
    background: rgba(22,163,74,0.2);
    transform: translateY(-1px);
}

body.theme-light .print-btn {
    background: var(--orange-light);
    color: #fff;
}
body.theme-light .print-btn:hover {
    background: var(--orange-light-hover);
    transform: translateY(-1px);
}

/* ========== LOADING OVERLAY ========== */
#loading-overlay {
    position: fixed;
    inset: 0;
    display: none;
    z-index: 9999;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
}

/* dark overlay */
body.theme-dark #loading-overlay {
    background: rgba(0,0,0,0.55);
}

/* softened light overlay */
body.theme-light #loading-overlay {
    background: rgba(255,255,255,0.07);
    backdrop-filter: none;
}

#loading-overlay .spinner {
    border-radius: 999px;
    width: 46px;
    height: 46px;
    border: 6px solid rgba(148,163,184,0.4);
    border-top: 6px solid #2563eb;
    animation: spin 0.9s linear infinite;
}

/* light spinner accent */
body.theme-light #loading-overlay .spinner {
    border: 6px solid rgba(255,255,255,0.5);
    border-top: 6px solid #ea580c;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* ========== RESPONSIVE ========== */
/* ========== RESPONSIVE (desktop → laptop → tablet → phone) ========== */

/* Big / normal desktops – just soften spacing a bit */
@media (max-width: 1400px) {
    .pos-wrapper {
        max-width: 1100px;
        margin: 12px auto;
        padding: 10px;
    }

    .pos-sidebar {
        min-width: 180px;
        max-width: 230px;
    }
}

/* Small laptops / large tablets in landscape (≤ 1200px) 
   👉 Still keep sidebar LEFT and main on the RIGHT */
@media (max-width: 1200px) {
    .pos-wrapper {
        max-width: 100%;
        margin: 10px auto;
        padding: 10px;
    }

    .pos-sidebar {
        min-width: 170px;
        max-width: 220px;
    }

    .pos-main {
        min-width: 0;
    }

    .top-info {
        flex-wrap: wrap;
    }

    .info-box {
        flex: 1 1 180px;
    }

    .totals-area {
        flex-wrap: wrap;
        gap: 10px;
    }

    .small-totals {
        flex: 1 1 260px;
    }

    .big-total {
        flex: 0 0 220px;
        text-align: right;
    }
}

/* True tablet layout (≤ 992px) 
   👉 STACK: sidebar on top, main below */
@media (max-width: 992px) {
    .pos-wrapper {
        flex-direction: column;
        max-width: 100%;
        margin: 10px auto;
        padding: 10px;
    }

    .pos-sidebar {
        width: 100%;
        min-width: 0;
        max-width: 100%;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: flex-start;
        gap: 8px;
        box-sizing: border-box;
    }

    .side-btn {
        flex: 1 1 160px;      /* 2–3 buttons per row on tablets */
        justify-content: center;
        font-size: 13px;
        padding: 7px 10px;
    }

    .pos-main {
        width: 100%;
        min-width: 0;
    }

    .top-info {
        flex-wrap: wrap;
        gap: 10px;
    }

    .info-box {
        flex: 1 1 160px;
    }

    .table-area {
        padding: 10px;
    }

    .totals-area {
        flex-wrap: wrap;
        gap: 10px;
        align-items: flex-start;
    }

    .small-totals,
    .big-total {
        flex: 1 1 240px;
        width: 100%;
    }

    .big-total {
        text-align: right;
    }
}

/* Phones / small tablets (≤ 768px) – fully stacked */
@media (max-width: 768px) {
    .pos-wrapper {
        flex-direction: column;
        padding: 8px;
        margin: 6px;
        border-radius: 12px;
    }

    .pos-sidebar {
        padding: 8px;
        gap: 6px;
        flex-wrap: wrap;
    }

    .side-btn {
        flex: 1 1 120px;
        font-size: 12px;
        padding: 6px 8px;
    }

    .top-info {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }

    .info-box {
        width: 100%;
        flex: 1 1 auto;
        min-width: 0;
    }

    .totals-area {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }

    .small-totals,
    .big-total {
        width: 100%;
    }

    .big-total {
        text-align: right;
    }

    .table-area {
        margin: 0 -4px;
        padding: 8px 4px;
        overflow-x: auto;
    }

    .pos-table {
        min-width: 560px;
        font-size: 12px;
    }

    .pos-table th,
    .pos-table td {
        padding: 8px 6px;
    }

    body {
        overflow-x: hidden;
    }
}


</style>


@php
    use App\Models\Setting;

    $logoPath = Setting::get('logo_path');
    $logoUrl  = $logoPath
        ? asset('storage/' . $logoPath)
        : asset('images/logo.png');
@endphp




<body
    class="theme-light"
    data-vat-percent="{{ Setting::vatPercent() }}"
    data-store-name="{{ Setting::get('store_name', config('app.name')) }}"
    data-store-address="{{ Setting::get('store_address', '') }}"
    data-store-phone="{{ Setting::get('store_phone', '') }}"
    data-store-logo="{{ Setting::get('logo_path')
        ? asset('storage/' . Setting::get('logo_path'))
        : asset('images/logo.png')
    }}"
    data-currency-symbol="{{ Setting::get('currency_symbol', '₦') }}"
    data-currency-code="{{ Setting::get('currency_code', 'NGN') }}"
    data-currency-position="{{ Setting::get('currency_position', 'left') }}"
    data-show-vat-on-receipt="{{ Setting::get('show_vat_on_receipt', '1') }}"
    data-show-customer-on-receipt="{{ Setting::get('show_customer_on_receipt', '1') }}"
    data-receipt-footer="{{ Setting::get('receipt_footer', 'Thank you for shopping!') }}"
>




<div id="flash-message" class="flash-message"></div>

<div class="pos-wrapper">
    

    <!-- SIDEBAR -->
    <aside class="pos-sidebar">
        <div class="logo-container">
        <img src="{{ $logoUrl }}"
             alt="Logo"
             style="max-width: 100%; height: 52px; object-fit: contain; display: block; margin: 8px auto;">
    </div>
        <a href="{{ route('admin.dashboard') }}" class="side-btn hold">Home</a>
        <button class="side-btn danger" id="cancel-sale">Cancel</button>
        <button class="side-btn" id="calculator-btn">Calculator</button>
        <button class="side-btn hold" id="sales-history">Sales Draft</button>
        <button class="side-btn hold" id="hold-sale">Hold Sale</button>
        <button id="theme-toggle" class="side-btn">
    🌓 Theme
</button>

        
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
                <input type="text" id="product-search" placeholder="Search by barcode, SKU, or name"autofocus>
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

    // ==============================
// STORE / RECEIPT META (Dynamic)
// ==============================
const STORE_NAME    = document.body.dataset.storeName;
const STORE_ADDRESS = document.body.dataset.storeAddress;
const STORE_PHONE   = document.body.dataset.storePhone;
const STORE_LOGO    = document.body.dataset.storeLogo;
const CASHIER_NAME  = "{{ Auth::user()->name }}";

// VAT from settings
const VAT_PERCENT = parseFloat(document.body.dataset.vatPercent || "0");
const VAT_RATE    = VAT_PERCENT / 100;

// ------------- Global Currency Settings --------------


const CURRENCY_SYMBOL   = document.body.dataset.currencySymbol || "₦";
const CURRENCY_CODE     = document.body.dataset.currencyCode || "NGN";
const CURRENCY_POSITION = document.body.dataset.currencyPosition || "left";

// Receipt behavior flags
const SHOW_VAT_ON_RECEIPT =
    (document.body.dataset.showVatOnReceipt || "1") === "1";
const SHOW_CUSTOMER_ON_RECEIPT =
    (document.body.dataset.showCustomerOnReceipt || "1") === "1";
const RECEIPT_FOOTER =
    document.body.dataset.receiptFooter || "Thank you for shopping!";


function formatMoneyJS(amount) {
    const n = Number(amount || 0).toFixed(2);
    return CURRENCY_POSITION === "right"
        ? `${n} ${CURRENCY_SYMBOL}`
        : `${CURRENCY_SYMBOL} ${n}`;
}



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
                hideSearchResults();   // close immediately
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
        hideSearchResults(); // ensure dropdown is closed

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

            // reset search box completely after add
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
            li.textContent = `${p.name} - ${p.sku} - ${formatMoneyJS(p.selling_price)}`;

            li.style.cursor = 'pointer';

            li.addEventListener('click', () => {
                // Add clicked item
                addToCart(p);

                // clear & hide after click
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
    // CART + VAT
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

        // stock
        const stock = Number(p.quantity ?? 0);

        if (stock <= 0) {
            showMessage("error", `${p.name} is OUT OF STOCK`);
            return;
        }

        // VATable? default true if missing
        const isVatable = (p.is_vatable !== undefined && p.is_vatable !== null)
            ? Boolean(p.is_vatable)
            : true;

        // Check if product already in cart
        const exists = cartItems.find(i => i.id === p.id);

        if (exists) {
            // prevent overselling
            if (exists.qty + 1 > stock) {
                showMessage('error', `Only ${stock} left in stock for ${p.name}`);
                return;
            }

            exists.qty += 1; // increase quantity
        } else {
            // New item
            cartItems.push({
                id: p.id,
                sku: p.sku,
                name: p.name,
                price: price,
                qty: 1,
                stock: stock,
                is_vatable: isVatable, // 🔹 track VATable flag
            });
        }

        renderCart();
    }

    function renderCart() {
        itemsBody.innerHTML = '';
        let subtotal = 0;
        let vatableSubtotal = 0;

        cartItems.forEach(i => {
            const lineTotal = i.price * i.qty;
            subtotal += lineTotal;
            if (i.is_vatable) {
                vatableSubtotal += lineTotal;
            }

           itemsBody.innerHTML += `
<tr>
    <td>${i.sku}</td>

    <td>
        ${i.name}
        ${i.stock !== undefined && i.stock < i.qty
            ? `<span class="stock-warning">🔴 OUT OF STOCK</span>`
            : ``}
    </td>

    <td>${formatMoneyJS(i.price)}</td>

    <td>
        <div class="qty-wrapper">
            <button type="button"
                class="qty-btn qty-minus"
                onclick="changeQty(${i.id}, -1)"
                ${(i.stock !== undefined && i.stock < 1) ? 'disabled' : ''}>
                -
            </button>

            <input type="number"
                class="qty-input"
                min="1"
                value="${i.qty}"
                onchange="updateQty(${i.id}, this.value)"
                ${i.stock !== undefined && i.stock < i.qty
                    ? 'style="border:1px solid red;"'
                    : ''}>

            <button type="button"
                class="qty-btn qty-plus"
                onclick="changeQty(${i.id}, 1)"
                ${(i.stock !== undefined && i.qty >= i.stock) ? 'disabled' : ''}>
                +
            </button>
        </div>
    </td>

    <td>
       <button type="button"
        class="delete-btn"
        onclick="removeItem(${i.id})">
    <span class="delete-icon">🗑</span>
</button>

    </td>
</tr>
`;


        });

        const subTotalEl   = document.getElementById('sub-total');
        const discountEl   = document.getElementById('discount');
        const taxEl        = document.getElementById('tax');
        const grandTotalEl = document.getElementById('grand-total');

        const discount = discountEl
            ? (parseFloat(discountEl.textContent) || 0)
            : 0;

        const taxAmount = vatableSubtotal * VAT_RATE;
        const grandTotal = subtotal - discount + taxAmount;

        if (subTotalEl)   subTotalEl.textContent   = subtotal.toFixed(2);
        if (taxEl)        taxEl.textContent        = taxAmount.toFixed(2);
        if (grandTotalEl) grandTotalEl.textContent = grandTotal.toFixed(2);

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
        renderCart(); // this will zero subtotal / tax / total

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

   
    // 👉 NEW: cache customer modal elements
    const customerModal  = document.getElementById('customer-modal');
    const customerForm   = document.getElementById('customer-form');
    const customerSelect = document.getElementById('customer-select');
    const customerError  = document.getElementById('customer-error');

    if (addCustomerBtn) {
        addCustomerBtn.addEventListener('click', () => openModal('customer-modal'));
    }

    // ✅ Handle Add Customer submit via AJAX
    if (customerForm) {
        customerForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (customerError) {
                customerError.style.display = 'none';
                customerError.textContent = '';
            }

            const formData   = new FormData(customerForm);
            const tokenInput = customerForm.querySelector('input[name="_token"]');
            const token      = tokenInput ? tokenInput.value : "{{ csrf_token() }}";

            try {
                const res = await fetch("{{ route('admin.sales.store-customer') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                if (res.status === 422) {
                    const data = await res.json();
                    if (data.errors && customerError) {
                        const messages = Object.values(data.errors).flat();
                        customerError.textContent = messages.join(' ');
                        customerError.style.display = 'block';
                    }
                    return;
                }

                if (!res.ok) {
                    if (customerError) {
                        customerError.textContent = 'Failed to save customer. Try again.';
                        customerError.style.display = 'block';
                    }
                    return;
                }

                const data      = await res.json();
                const customer  = data.customer;

                // ✅ Add to select dropdown
                if (customerSelect && customer) {
                    const opt   = document.createElement('option');
                    opt.value   = customer.id;
                    opt.textContent = customer.name;
                    customerSelect.appendChild(opt);
                    customerSelect.value = customer.id; // select newly created
                }

                // Reset form & close modal
                customerForm.reset();
                if (customerModal) customerModal.style.display = 'none';

                // Optional flash
                if (typeof showMessage === 'function') {
                    showMessage('success', 'Customer added successfully.');
                }

            } catch (err) {
                console.error('Error creating customer', err);
                if (customerError) {
                    customerError.textContent = 'Error creating customer.';
                    customerError.style.display = 'block';
                }
            }
        });
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
        let vatableSubtotal = 0;

        cartItems.forEach(item => {
            const lineTotal = item.price * item.qty;
            subtotal += lineTotal;
            if (item.is_vatable) {
                vatableSubtotal += lineTotal;
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.name}</td>
                <td style="text-align:right;">${item.qty}</td>
                <td style="text-align:right;">${lineTotal.toFixed(2)}</td>
            `;
            holdSummaryBody.appendChild(tr);
        });

        const taxAmount = vatableSubtotal * VAT_RATE;
        const totalWithVat = subtotal + taxAmount;

        holdSummaryTotal.textContent = totalWithVat.toFixed(2); // show VAT-inclusive total
        if (holdNumberInput) {
            holdNumberInput.value = `HOLD-${Date.now()}`;
        }

        openModal('hold-modal');
    }

    if (holdCancelBtn) {
        holdCancelBtn.addEventListener('click', () => {
            closeModal('hold-modal');
        });
    }

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

        // ✅ Just restore the items – no stock limits here
        cartItems = [];

        for (let item of sale.items) {
            const prodId = item.product_id;
            const sku    = item.sku;
            const name   = item.name;
            const price  = Number(item.price);
            const qty    = Number(item.qty);

            // ✅ Only fetch product to know if it is VATable
            let isVatable = true; // sensible default

            try {
                const productRes = await fetch(`/products/json/${prodId}`, {
                    headers: { 'Accept': 'application/json' },
                });

                if (productRes.ok) {
                    const product = await productRes.json();

                    // robust boolean parsing (can be true/false, 0/1, "0"/"1")
                    const raw = product?.is_vatable;
                    if (raw === true || raw === 1 || raw === '1') {
                        isVatable = true;
                    } else if (raw === false || raw === 0 || raw === '0') {
                        isVatable = false;
                    }
                }
            } catch (err) {
                console.warn('[resumeHeld] could not load product for VAT, using default', err);
            }

            // ✅ Do NOT set stock here – let other checks handle it
            cartItems.push({
                id: prodId,
                sku: sku,
                name: name,
                price: price,
                qty: qty,
                is_vatable: isVatable,
            });
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
// PAYMENT + RECEIPT MODAL
// ==============================
const submitPaymentBtn   = document.getElementById('submit-payment');
const amountPaidInput    = document.getElementById('amount-paid');
const receiptContent     = document.getElementById('receipt-content');
const printReceiptBtn    = document.getElementById('print-receipt-btn');
const receiptContinueBtn = document.getElementById('receipt-continue-btn');

let lastSaleId = null; // 👈 store last sale for printing

function updateChange() {
    const total  = parseFloat(document.getElementById('modal-total').textContent) || 0;
    const paid   = parseFloat(amountPaidInput.value) || 0;
    const change = paid - total;

    const changeEl = document.getElementById('change');
    if (changeEl) {
        changeEl.textContent = formatMoneyJS(change > 0 ? change : 0);
    }
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

            const saleId = data.sale_id ?? '';
            lastSaleId = saleId; // 👈 save for printing

            // Build receipt content for modal
            if (receiptContent) {
                let itemsRows = "";
                cartItems.forEach(i => {
                    const lineTotal = i.price * i.qty;
                    itemsRows += `
                        <tr>
                            <td>${i.name}</td>
                            <td style="text-align:right;">${i.qty}</td>
                            <td style="text-align:right;">${formatMoneyJS(i.price)}</td>
                            <td style="text-align:right;">${formatMoneyJS(lineTotal)}</td>
                        </tr>
                    `;
                });

                const displayedSubtotal = parseFloat(document.getElementById('sub-total').textContent) || 0;
                const displayedTax      = parseFloat(document.getElementById('tax').textContent) || 0;
                const displayedTotal    = parseFloat(document.getElementById('grand-total').textContent) || 0;
                const change            = paid - displayedTotal;

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
                       
                        <p>${STORE_ADDRESS}</p>
                        <p>${STORE_PHONE}</p>
                        <div class="receipt-line"></div>
                        <p><strong>RECEIPT</strong></p>
                        <div class="receipt-line"></div>

                        <div class="receipt-section">
                            <p><strong>Customer:</strong> ${
                                SHOW_CUSTOMER_ON_RECEIPT
                                    ? (payload.customer_name || 'Walk-in Customer')
                                    : '***'
                            }</p>

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
                            <p><strong>SubTotal:</strong> ${formatMoneyJS(displayedSubtotal)}</p>

                            ${
                                SHOW_VAT_ON_RECEIPT
                                    ? `<p><strong>Tax (${VAT_PERCENT.toFixed(2)}%):</strong> ${formatMoneyJS(displayedTax)}</p>`
                                    : ''
                            }

                            <p><strong>Total:</strong> ${formatMoneyJS(displayedTotal)}</p>
                            <p><strong>Paid:</strong> ${formatMoneyJS(paid)}</p>
                            <p><strong>Change:</strong> ${formatMoneyJS(change > 0 ? change : 0)}</p>
                        </div>

                        <div class="receipt-barcode">
                            ${String(saleId).padStart(10, '0')}
                        </div>

                        <p style="margin-top:6px;">${RECEIPT_FOOTER}</p>
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

// 👇 PRINT BUTTON: open print-only route (auto print dialog)
if (printReceiptBtn) {
    printReceiptBtn.addEventListener('click', () => {
        if (!lastSaleId) {
            showMessage('error', 'No receipt available to print.');
            return;
        }

        const printUrl = "{{ route('admin.sales.print', ':id') }}".replace(':id', lastSaleId);
        window.open(printUrl, '_blank', 'width=400,height=650');
    });
}

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

function setTheme(theme) {
    const body = document.body;

    if (theme === 'dark') {
        body.classList.remove('theme-light');
        body.classList.add('theme-dark');
    } else {
        body.classList.remove('theme-dark');
        body.classList.add('theme-light');
    }

    // (optional) remember choice
    localStorage.setItem('pos_theme', theme);
}

// restore on load
document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('pos_theme');
    if (saved === 'dark' || saved === 'light') {
        setTheme(saved);
    }
});

// Example: connect to a toggle button
const themeToggle = document.getElementById('theme-toggle'); // e.g. a moon/sun icon
if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        const isDark = document.body.classList.contains('theme-dark');
        setTheme(isDark ? 'light' : 'dark');
    });
}

</script>
