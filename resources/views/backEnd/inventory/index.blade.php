@extends('backEnd.layouts.master')
@section('title', 'Inventory Management')
@section('css')
<style>
    .stat-card { border-radius: 10px; padding: 18px 20px; color: #fff; position: relative; overflow: hidden; }
    .stat-card .stat-icon { font-size: 40px; opacity: 0.25; position: absolute; right: 15px; top: 10px; }
    .stat-card h3 { font-size: 32px; font-weight: 800; margin: 0; }
    .stat-card p  { margin: 0; font-size: 13px; opacity: 0.9; }
    .bg-inventory  { background: linear-gradient(135deg,#4361ee,#3a0ca3); }
    .bg-lowstock   { background: linear-gradient(135deg,#f77f00,#d62828); }
    .bg-outofstock { background: linear-gradient(135deg,#c9184a,#800f2f); }
    .bg-variants   { background: linear-gradient(135deg,#0077b6,#023e8a); }
    .badge-ok  { background:#198754; color:#fff; }
    .badge-low { background:#fd7e14; color:#fff; }
    .badge-out { background:#dc3545; color:#fff; }
    .stock-badge { padding: 3px 9px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .barcode-mono { font-family: monospace; font-size: 13px; letter-spacing: 1px; }
    .search-box { max-width: 400px; }
    .action-btns a { margin-right: 4px; }
</style>
@endsection
@section('content')
<div class="container-fluid">
    {{-- Page Title --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('inventory.scan') }}" class="btn btn-success rounded-pill me-1">
                        <i class="ri-barcode-line"></i> Scanner / POS
                    </a>
                    <a href="{{ route('inventory.log') }}" class="btn btn-primary rounded-pill me-1">
                        <i class="ri-history-line"></i> Scan Log
                    </a>
                    <a href="{{ route('products.barcode') }}" class="btn btn-dark rounded-pill">
                        <i class="ri-printer-line"></i> Print Barcode
                    </a>
                </div>
                <h4 class="page-title">Inventory Management</h4>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="stat-card bg-inventory">
                <i class="ri-box-3-line stat-icon"></i>
                <h3>{{ $totalSimple }}</h3>
                <p>Simple Products</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card bg-variants">
                <i class="ri-stack-line stat-icon"></i>
                <h3>{{ $totalVariant }}</h3>
                <p>Variant SKUs</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card bg-lowstock">
                <i class="ri-alert-line stat-icon"></i>
                <h3>{{ $lowStockSimple + $lowStockVariant }}</h3>
                <p>Low Stock Items</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card bg-outofstock">
                <i class="ri-close-circle-line stat-icon"></i>
                <h3>{{ $outOfStockSimple + $outOfStockVariant }}</h3>
                <p>Out of Stock</p>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="row mb-3">
        <div class="col-12">
            <form action="{{ route('inventory.index') }}" method="GET" class="d-flex gap-2 search-box">
                <input type="text" name="search" value="{{ $search }}" class="form-control"
                    placeholder="Search by name or barcode…">
                <button class="btn btn-primary px-3">Search</button>
                @if($search)
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary px-3">Clear</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Simple Products Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <h5 class="mb-0">Simple Products</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm nowrap w-100 mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="5%">Image</th>
                                    <th>Name</th>
                                    <th>Barcode</th>
                                    <th>Category</th>
                                    <th>Purchase</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse ($simpleProducts as $key => $p)
                                @php
                                    $stockClass = $p->stock <= 0 ? 'badge-out' : ($p->stock <= $p->stock_alert ? 'badge-low' : 'badge-ok');
                                @endphp
                                <tr>
                                    <td>{{ $simpleProducts->firstItem() + $key }}</td>
                                    <td>
                                        @if($p->image)
                                            <img src="{{ asset($p->image->image) }}" class="rounded" width="36" height="36" style="object-fit:cover">
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $p->name }}</td>
                                    <td><span class="barcode-mono">{{ $p->pro_barcode ?? '—' }}</span></td>
                                    <td>{{ $p->category ? $p->category->name : '—' }}</td>
                                    <td>{{ number_format($p->purchase_price) }}</td>
                                    <td>{{ number_format($p->new_price) }}</td>
                                    <td><span class="stock-badge {{ $stockClass }}">{{ $p->stock }}</span></td>
                                    <td>
                                        @if($p->status)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="action-btns">
                                        <a href="{{ route('products.barcode') }}?product_id={{ $p->id }}&type=1"
                                           class="btn btn-xs btn-dark btn-sm" title="Print Barcode">
                                            <i class="ri-printer-line"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $p->id) }}"
                                           class="btn btn-xs btn-info btn-sm" title="Edit">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted py-4">No products found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-3 py-2">
                        {{ $simpleProducts->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Variant SKUs Table --}}
    <div class="row mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2">
                    <h5 class="mb-0">Variant SKUs</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm nowrap w-100 mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="5%">Image</th>
                                    <th>Product</th>
                                    <th>Barcode</th>
                                    <th>Size</th>
                                    <th>Color</th>
                                    <th>Purchase</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse ($variables as $key => $v)
                                @php
                                    $alert = $v->product ? $v->product->stock_alert : 5;
                                    $stockClass = $v->stock <= 0 ? 'badge-out' : ($v->stock <= $alert ? 'badge-low' : 'badge-ok');
                                @endphp
                                <tr>
                                    <td>{{ $variables->firstItem() + $key }}</td>
                                    <td>
                                        @if($v->product && $v->product->image)
                                            <img src="{{ asset($v->product->image->image) }}" class="rounded" width="36" height="36" style="object-fit:cover">
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $v->product ? $v->product->name : '—' }}</td>
                                    <td><span class="barcode-mono">{{ $v->pro_barcode ?? '—' }}</span></td>
                                    <td>{{ $v->size ?? '—' }}</td>
                                    <td>{{ $v->color ?? '—' }}</td>
                                    <td>{{ number_format($v->purchase_price) }}</td>
                                    <td>{{ number_format($v->new_price) }}</td>
                                    <td><span class="stock-badge {{ $stockClass }}">{{ $v->stock }}</span></td>
                                    <td class="action-btns">
                                        <a href="{{ route('products.barcode') }}?product_id={{ $v->id }}&type=0"
                                           class="btn btn-xs btn-dark btn-sm" title="Print Barcode">
                                            <i class="ri-printer-line"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted py-4">No variants found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-3 py-2">
                        {{ $variables->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
