@extends('backEnd.layouts.master')
@section('title', 'Scan Log — Inventory History')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('inventory.scan') }}" class="btn btn-success rounded-pill me-1">
                        <i class="ri-barcode-line"></i> Scanner
                    </a>
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary rounded-pill">
                        <i class="ri-arrow-left-line"></i> Inventory
                    </a>
                </div>
                <h4 class="page-title"><i class="ri-history-line me-1"></i> Scan Log</h4>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form action="{{ route('inventory.log') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1 fw-semibold">Search</label>
                    <input type="text" name="search" value="{{ $search }}"
                        class="form-control" placeholder="Barcode or product name…">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1 fw-semibold">Action</label>
                    <select name="action" class="form-select">
                        <option value="">All Actions</option>
                        <option value="receive"   @selected($action=='receive')>Receive</option>
                        <option value="adjust"    @selected($action=='adjust')>Adjust</option>
                        <option value="lookup"    @selected($action=='lookup')>Lookup</option>
                        <option value="not_found" @selected($action=='not_found')>Not Found</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1 fw-semibold">Date From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1 fw-semibold">Date To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('inventory.log') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm nowrap w-100 mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="4%">#</th>
                            <th width="12%">Time</th>
                            <th width="14%">Barcode</th>
                            <th>Product</th>
                            <th width="8%">Variant</th>
                            <th width="8%">Action</th>
                            <th width="7%">Before</th>
                            <th width="7%">Change</th>
                            <th width="7%">After</th>
                            <th width="8%">By</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($logs as $key => $log)
                        <tr>
                            <td>{{ $logs->firstItem() + $key }}</td>
                            <td style="font-size:12px">
                                {{ $log->created_at->format('d M y') }}<br>
                                <span class="text-muted">{{ $log->created_at->format('h:i A') }}</span>
                            </td>
                            <td><code style="font-size:12px">{{ $log->barcode }}</code></td>
                            <td>{{ $log->product ? $log->product->name : '<span class="text-danger">N/A</span>' }}</td>
                            <td>
                                @if($log->variable)
                                    <small>
                                        @if($log->variable->size)  S:{{ $log->variable->size }} @endif
                                        @if($log->variable->color) C:{{ $log->variable->color }} @endif
                                    </small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeColor = match($log->action) {
                                        'receive'   => 'success',
                                        'adjust'    => 'warning',
                                        'lookup'    => 'info',
                                        'not_found' => 'danger',
                                        default     => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeColor }}">{{ ucfirst(str_replace('_',' ',$log->action)) }}</span>
                            </td>
                            <td class="text-center fw-bold">{{ $log->qty_before }}</td>
                            <td class="text-center fw-bold {{ $log->qty_change >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $log->qty_change >= 0 ? '+' : '' }}{{ $log->qty_change }}
                            </td>
                            <td class="text-center fw-bold">{{ $log->qty_after }}</td>
                            <td style="font-size:12px">{{ $log->user ? $log->user->name : 'System' }}</td>
                            <td style="font-size:12px; max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap"
                                title="{{ $log->note }}">
                                {{ $log->note ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                No scan log entries found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2">
                {{ $logs->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
