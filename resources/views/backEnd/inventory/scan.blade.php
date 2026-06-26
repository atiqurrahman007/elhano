@extends('backEnd.layouts.master')
@section('title', 'Barcode Scanner — POS Inventory')
@section('css')
<style>
    /* ── POS Scanner Layout ─────────────────────────── */
    .scanner-wrap {
        max-width: 680px;
        margin: 0 auto;
    }
    .scan-input-box {
        border: 3px solid #4361ee;
        border-radius: 12px;
        padding: 10px 18px;
        font-size: 22px;
        font-family: monospace;
        letter-spacing: 2px;
        outline: none;
        width: 100%;
        transition: border-color .2s, box-shadow .2s;
    }
    .scan-input-box:focus {
        border-color: #198754;
        box-shadow: 0 0 0 4px rgba(25,135,84,.15);
    }
    .scan-icon-wrap {
        background: #4361ee;
        color: #fff;
        width: 54px;
        height: 54px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        flex-shrink: 0;
    }
    /* ── Product Card ───────────────────────────────── */
    #product-card {
        display: none;
        border-radius: 14px;
        border: 2px solid #dee2e6;
        overflow: hidden;
    }
    .product-card-header {
        background: linear-gradient(135deg, #4361ee, #3a0ca3);
        color: #fff;
        padding: 14px 20px;
    }
    .product-card-header h5 { margin: 0; font-size: 17px; font-weight: 700; }
    .product-card-header small { opacity: .8; font-size: 12px; }
    .product-img-thumb {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #eee;
    }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 16px; }
    .info-item label { font-size: 11px; color: #888; text-transform: uppercase; font-weight: 600; margin: 0; }
    .info-item span  { font-size: 15px; font-weight: 700; color: #222; display: block; }
    .stock-big { font-size: 36px !important; color: #198754 !important; line-height: 1; }
    .stock-low  { color: #fd7e14 !important; }
    .stock-zero { color: #dc3545 !important; }
    /* ── Action Tabs ────────────────────────────────── */
    .action-tabs .nav-link { border-radius: 8px 8px 0 0; font-weight: 600; color: #555; }
    .action-tabs .nav-link.active { background: #4361ee; color: #fff; border-color: #4361ee; }
    /* ── Alerts ─────────────────────────────────────── */
    #scan-alert { display: none; }
    #success-toast {
        position: fixed; bottom: 24px; right: 24px; z-index: 9999;
        background: #198754; color: #fff;
        border-radius: 10px; padding: 14px 22px;
        font-weight: 600; font-size: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,.25);
        display: none;
        animation: slideIn .3s ease;
    }
    @keyframes slideIn { from { transform: translateY(30px); opacity:0 } to { transform: translateY(0); opacity:1 } }
    /* ── History ─────────────────────────────────────── */
    #scan-history .history-item {
        padding: 8px 12px;
        border-left: 4px solid #4361ee;
        background: #f8f9ff;
        border-radius: 0 8px 8px 0;
        margin-bottom: 6px;
        font-size: 13px;
    }
    #scan-history .history-item.receive { border-left-color: #198754; }
    #scan-history .history-item.adjust  { border-left-color: #fd7e14; }
    #scan-history .history-item.error   { border-left-color: #dc3545; background: #fff5f5; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Page Title --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary rounded-pill me-1">
                        <i class="ri-arrow-left-line"></i> Back to Inventory
                    </a>
                    <a href="{{ route('inventory.log') }}" class="btn btn-primary rounded-pill">
                        <i class="ri-history-line"></i> Scan Log
                    </a>
                </div>
                <h4 class="page-title"><i class="ri-barcode-line me-1"></i> Barcode Scanner — POS</h4>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="scanner-wrap">

                {{-- Scan Input --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <label class="form-label fw-bold mb-2" style="font-size:15px;">
                            <i class="ri-barcode-line text-primary me-1"></i>
                            Scan Barcode / Enter Manually
                        </label>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="scan-icon-wrap">
                                <i class="ri-barcode-line"></i>
                            </div>
                            <input type="text" id="barcode-input" class="scan-input-box"
                                placeholder="Scan or type barcode…"
                                autocomplete="off" autofocus>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            Point scanner at barcode or type code and press <kbd>Enter</kbd>
                        </small>

                        {{-- Error alert --}}
                        <div id="scan-alert" class="alert alert-danger mt-2 mb-0 py-2"></div>
                    </div>
                </div>

                {{-- Product Card (hidden until scan) --}}
                <div id="product-card" class="card mb-3">
                    <div class="product-card-header d-flex align-items-center gap-3">
                        <img id="pc-image" class="product-img-thumb d-none" src="" alt="Product Image">
                        <div>
                            <h5 id="pc-name">—</h5>
                            <small id="pc-barcode-label">Barcode: —</small>
                        </div>
                        <span id="pc-variant-badge" class="ms-auto badge bg-light text-dark d-none" style="font-size:12px;"></span>
                    </div>
                    <div class="card-body">
                        <div class="info-grid mb-3">
                            <div class="info-item">
                                <label>Category</label>
                                <span id="pc-category">—</span>
                            </div>
                            <div class="info-item">
                                <label>Barcode</label>
                                <span id="pc-barcode" style="font-family:monospace;letter-spacing:1px;">—</span>
                            </div>
                            <div class="info-item">
                                <label>Purchase Price</label>
                                <span id="pc-purchase">—</span>
                            </div>
                            <div class="info-item">
                                <label>Selling Price</label>
                                <span id="pc-price">—</span>
                            </div>
                            <div class="info-item">
                                <label>Current Stock</label>
                                <span id="pc-stock" class="stock-big">—</span>
                            </div>
                        </div>

                        {{-- Action Tabs --}}
                        <ul class="nav nav-tabs action-tabs mb-3" id="action-tab">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-receive">
                                    <i class="ri-add-circle-line me-1"></i> Receive Stock
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-adjust">
                                    <i class="ri-settings-3-line me-1"></i> Adjust Stock
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            {{-- Receive Tab --}}
                            <div class="tab-pane fade show active" id="tab-receive">
                                <div class="row g-2 align-items-end">
                                    <div class="col-5">
                                        <label class="form-label fw-semibold">Quantity to Add</label>
                                        <input type="number" id="receive-qty" class="form-control form-control-lg"
                                               value="1" min="1" style="font-size:22px;font-weight:700;">
                                    </div>
                                    <div class="col-7">
                                        <label class="form-label fw-semibold">Note (optional)</label>
                                        <input type="text" id="receive-note" class="form-control"
                                               placeholder="e.g. Supplier delivery">
                                    </div>
                                    <div class="col-12 mt-2">
                                        <button id="btn-receive" class="btn btn-success w-100 py-2 fw-bold"
                                                style="font-size:16px;">
                                            <i class="ri-add-circle-line me-1"></i> Add Stock
                                        </button>
                                    </div>
                                </div>
                            </div>
                            {{-- Adjust Tab --}}
                            <div class="tab-pane fade" id="tab-adjust">
                                <div class="row g-2 align-items-end">
                                    <div class="col-4">
                                        <label class="form-label fw-semibold">Type</label>
                                        <select id="adjust-type" class="form-select">
                                            <option value="add">Add (+)</option>
                                            <option value="subtract">Subtract (−)</option>
                                            <option value="set">Set Exact</option>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label fw-semibold">Quantity</label>
                                        <input type="number" id="adjust-qty" class="form-control form-control-lg"
                                               value="1" min="0" style="font-size:20px;font-weight:700;">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label fw-semibold">Note</label>
                                        <input type="text" id="adjust-note" class="form-control"
                                               placeholder="Reason…">
                                    </div>
                                    <div class="col-12 mt-2">
                                        <button id="btn-adjust" class="btn btn-warning w-100 py-2 fw-bold"
                                                style="font-size:16px;">
                                            <i class="ri-settings-3-line me-1"></i> Adjust Stock
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Session History --}}
                <div class="card">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <strong>Session History</strong>
                        <button class="btn btn-sm btn-outline-secondary" onclick="clearHistory()">Clear</button>
                    </div>
                    <div class="card-body p-2" id="scan-history">
                        <p class="text-muted text-center my-2" id="no-history-msg">No scans yet this session.</p>
                    </div>
                </div>

            </div>{{-- /scanner-wrap --}}
        </div>
    </div>
</div>

{{-- Success Toast --}}
<div id="success-toast"></div>
@endsection

@section('script')
<script>
// ─── State ──────────────────────────────────────────
let currentProduct = null;

// ─── Auto-submit on Enter ────────────────────────────
document.getElementById('barcode-input').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        doLookup(this.value.trim());
    }
});

// ─── Lookup ──────────────────────────────────────────
function doLookup(barcode) {
    if (!barcode) return;
    hideAlert();

    fetch("{{ route('inventory.lookup') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ barcode })
    })
    .then(r => r.json())
    .then(data => {
        if (data.found) {
            showProduct(data);
            addHistory('lookup', `Scanned: <strong>${data.name}</strong> — Barcode: ${data.barcode} — Stock: ${data.stock}`, barcode);
        } else {
            showAlert(data.message || 'Product not found.');
            addHistory('error', `Not found: <strong>${barcode}</strong>`, barcode);
            hideProductCard();
        }
    })
    .catch(() => showAlert('Network error. Please try again.'));
}

// ─── Show Product Card ───────────────────────────────
function showProduct(data) {
    currentProduct = data;

    document.getElementById('pc-name').textContent = data.name;
    document.getElementById('pc-barcode-label').textContent = 'Barcode: ' + data.barcode;
    document.getElementById('pc-barcode').textContent = data.barcode;
    document.getElementById('pc-category').textContent = data.category;
    document.getElementById('pc-purchase').textContent = '৳ ' + Number(data.purchase_price).toLocaleString();
    document.getElementById('pc-price').textContent = '৳ ' + Number(data.price).toLocaleString();

    const stockEl = document.getElementById('pc-stock');
    stockEl.textContent = data.stock;
    stockEl.className = 'stock-big';
    if (data.stock <= 0) stockEl.classList.add('stock-zero');
    else if (data.stock <= data.stock_alert) stockEl.classList.add('stock-low');

    // Image
    const imgEl = document.getElementById('pc-image');
    if (data.image) { imgEl.src = data.image; imgEl.classList.remove('d-none'); }
    else { imgEl.classList.add('d-none'); }

    // Variant badge
    const badge = document.getElementById('pc-variant-badge');
    if (data.type === 'variable') {
        let parts = [];
        if (data.size)  parts.push('Size: ' + data.size);
        if (data.color) parts.push('Color: ' + data.color);
        badge.textContent = parts.join(' | ');
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }

    document.getElementById('product-card').style.display = 'block';
    document.getElementById('receive-qty').focus();
}

function hideProductCard() {
    document.getElementById('product-card').style.display = 'none';
    currentProduct = null;
}

// ─── Receive Stock ───────────────────────────────────
document.getElementById('btn-receive').addEventListener('click', function () {
    if (!currentProduct) return;
    const qty  = parseInt(document.getElementById('receive-qty').value);
    const note = document.getElementById('receive-note').value;
    if (!qty || qty < 1) { showAlert('Enter a valid quantity.'); return; }

    doStockAction("{{ route('inventory.receive') }}", {
        barcode:     currentProduct.barcode,
        id:          currentProduct.id,
        type:        currentProduct.type,
        qty,
        note
    }, 'receive');
});

// ─── Adjust Stock ────────────────────────────────────
document.getElementById('btn-adjust').addEventListener('click', function () {
    if (!currentProduct) return;
    const qty         = parseInt(document.getElementById('adjust-qty').value);
    const adjust_type = document.getElementById('adjust-type').value;
    const note        = document.getElementById('adjust-note').value;
    if (isNaN(qty)) { showAlert('Enter a valid quantity.'); return; }

    doStockAction("{{ route('inventory.adjust') }}", {
        barcode:     currentProduct.barcode,
        id:          currentProduct.id,
        type:        currentProduct.type,
        qty,
        adjust_type,
        note
    }, 'adjust');
});

// ─── Generic Stock Action ────────────────────────────
function doStockAction(url, payload, actionType) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            // Update stock display
            document.getElementById('pc-stock').textContent = data.new_stock;
            currentProduct.stock = data.new_stock;

            const actionLabel = actionType === 'receive' ? 'Received' : 'Adjusted';
            addHistory(actionType,
                `<strong>${actionLabel}:</strong> ${currentProduct.name} — New Stock: <strong>${data.new_stock}</strong>`,
                currentProduct.barcode
            );

            // Reset inputs
            document.getElementById('receive-qty').value  = 1;
            document.getElementById('receive-note').value = '';
            document.getElementById('adjust-qty').value   = 1;
            document.getElementById('adjust-note').value  = '';

            // Re-focus scanner
            setTimeout(() => {
                const input = document.getElementById('barcode-input');
                input.value = '';
                input.focus();
            }, 1200);
        } else {
            showAlert(data.message);
        }
    })
    .catch(() => showAlert('Network error. Please try again.'));
}

// ─── UI Helpers ──────────────────────────────────────
function showAlert(msg) {
    const el = document.getElementById('scan-alert');
    el.innerHTML = '<i class="ri-error-warning-line me-1"></i>' + msg;
    el.style.display = 'block';
}
function hideAlert() {
    document.getElementById('scan-alert').style.display = 'none';
}
function showToast(msg) {
    const t = document.getElementById('success-toast');
    t.innerHTML = '<i class="ri-checkbox-circle-line me-2"></i>' + msg;
    t.style.display = 'block';
    setTimeout(() => { t.style.display = 'none'; }, 2500);
}

// ─── Session History ─────────────────────────────────
function addHistory(type, message, barcode) {
    document.getElementById('no-history-msg').style.display = 'none';
    const h   = document.getElementById('scan-history');
    const now = new Date().toLocaleTimeString();
    const div = document.createElement('div');
    div.className = `history-item ${type}`;
    div.innerHTML = `<span class="text-muted me-2" style="font-size:11px">${now}</span>${message}`;
    h.insertBefore(div, h.firstChild);
}
function clearHistory() {
    const h = document.getElementById('scan-history');
    h.innerHTML = '<p class="text-muted text-center my-2" id="no-history-msg">No scans yet this session.</p>';
}

// Auto-focus on barcode input when page loads
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('barcode-input').focus();
});
</script>
@endsection
