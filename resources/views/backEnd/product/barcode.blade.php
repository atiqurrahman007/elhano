@extends('backEnd.layouts.master')
@section('title', 'Barcode Print')
@section('css')
<link href="{{ asset('public/backEnd') }}/assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
<style>
/* ── Print Styles ─────────────────────────────── */
@page { margin: 4mm; }
@media print {
    header, footer, .no-print,
    .left-side-menu, .navbar-custom,
    .page-title-box { display: none !important; }
    body { font-size: 9px !important; margin: 0 !important; background: #fff; }
    .print-grid {
        display: grid !important;
        grid-template-columns: repeat(var(--cols, 3), 1fr);
        gap: 3mm;
        padding: 0;
    }
    .label-card {
        border: 1px solid #ccc !important;
        page-break-inside: avoid;
        break-inside: avoid;
    }
}

/* ── Screen Styles ───────────────────────────── */
.print-grid {
    display: grid;
    grid-template-columns: repeat(3, 200px);
    gap: 12px;
    justify-content: center;
    margin: 0 auto;
}
.label-card {
    border: 1.5px solid #222;
    border-radius: 5px;
    background: #fff;
    padding: 6px 8px 4px;
    text-align: center;
    color: #000;
}
.label-shop-name {
    font-size: 13px;
    font-weight: 900;
    letter-spacing: .5px;
    text-transform: uppercase;
    line-height: 1.2;
    border-bottom: 1px solid #000;
    padding-bottom: 2px;
    margin-bottom: 3px;
}
.label-product-name {
    font-size: 10px;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.label-variant {
    font-size: 9px;
    color: #333;
    margin-bottom: 2px;
}
.label-price {
    font-size: 14px;
    font-weight: 900;
    letter-spacing: .5px;
}
.label-barcode img { max-width: 100%; height: 36px; display: block; margin: 0 auto; }
.label-code {
    font-family: monospace;
    font-size: 9px;
    letter-spacing: 1px;
    margin-top: 1px;
}
/* Controls */
.print-controls { max-width: 800px; margin: 0 auto; }
.col-count-btn { border-radius: 6px; }
.col-count-btn.active { background: #4361ee !important; color: #fff !important; border-color: #4361ee !important; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box no-print">
                <div class="page-title-right">
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary rounded-pill me-1">
                        <i class="ri-box-3-line"></i> Inventory
                    </a>
                </div>
                <h4 class="page-title">Product Barcode Print</h4>
            </div>
        </div>
    </div>

    <div class="row no-print">
        <div class="col-12">
            <div class="card print-controls">
                <div class="card-body">
                    <form action="" class="row g-3 align-items-end">
                        {{-- Product Selector --}}
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Select Product / Variant *</label>
                            <select class="form-control select2 @error('product_id') is-invalid @enderror"
                                    name="product_id" id="product_id" required>
                                <option value="">Choose product…</option>
                                @foreach ($data as $value)
                                    @if ($value->type == 1)
                                        <option value="{{ $value->id }}" data-type="1"
                                            @if(request()->type == 1 && $value->id == request()->product_id) selected @endif>
                                            {{ $value->name }} — ৳{{ $value->new_price }}
                                        </option>
                                    @else
                                        @foreach ($value->variables as $variable)
                                            <option value="{{ $variable->id }}" data-type="0"
                                                @if(request()->type == 0 && $variable->id == request()->product_id) selected @endif>
                                                {{ $value->name }}
                                                @if($variable->size) — {{ $variable->size }}@endif
                                                @if($variable->color) / {{ $variable->color }}@endif
                                                — ৳{{ $variable->new_price }}
                                            </option>
                                        @endforeach
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="type" id="type-input" value="{{ request()->type ?? '' }}">

                        {{-- Copies --}}
                        <div class="col-sm-2">
                            <label class="form-label fw-semibold">Copies</label>
                            <input type="number" name="copies" id="copies-input"
                                   value="{{ request()->copies ?? 1 }}"
                                   min="1" max="200" class="form-control text-center fw-bold"
                                   style="font-size:18px;">
                        </div>

                        {{-- Columns --}}
                        <div class="col-sm-2">
                            <label class="form-label fw-semibold">Label Columns</label>
                            <div class="btn-group w-100" id="col-selector">
                                <button type="button" class="col-count-btn btn btn-outline-secondary" data-cols="2">2</button>
                                <button type="button" class="col-count-btn btn btn-outline-secondary active" data-cols="3">3</button>
                                <button type="button" class="col-count-btn btn btn-outline-secondary" data-cols="4">4</button>
                                <button type="button" class="col-count-btn btn btn-outline-secondary" data-cols="5">5</button>
                            </div>
                            <input type="hidden" name="cols" id="cols-input" value="{{ request()->cols ?? 3 }}">
                        </div>

                        <div class="col-sm-2">
                            <button class="btn btn-primary w-100 fw-bold">Generate Labels</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($barcode != null)
    @php
        $copies = max(1, min(200, (int)(request()->copies ?? 1)));
        $cols   = max(2, min(5, (int)(request()->cols ?? 3)));
    @endphp

    {{-- Print Button --}}
    <div class="row no-print mb-3">
        <div class="col-12 text-center">
            <button onclick="printFunction()" class="btn btn-success px-5 py-2 fw-bold" style="font-size:16px;">
                <i class="ri-printer-line me-1"></i> Print {{ $copies }} Label{{ $copies > 1 ? 's' : '' }}
            </button>
            <a href="{{ url()->current() }}" class="btn btn-outline-secondary ms-2">Reset</a>
        </div>
    </div>

    {{-- Labels Grid --}}
    <div class="print-grid" id="labels-grid" style="--cols: {{ $cols }}">
        @for ($i = 0; $i < $copies; $i++)
        <div class="label-card">
            {{-- Shop Name --}}
            <div class="label-shop-name">{{ $generalsetting->name }}</div>

            {{-- Product Name --}}
            <div class="label-product-name" title="{{ $product->name }}">{{ $product->name }}</div>

            {{-- Variant info (only for variant products) --}}
            @if (request()->type == 0)
                <div class="label-variant">
                    @if($barcode->size)  <span>Size: <strong>{{ $barcode->size }}</strong></span> @endif
                    @if($barcode->color) <span style="margin-left:4px">Color: <strong>{{ $barcode->color }}</strong></span> @endif
                </div>
            @endif

            {{-- Price --}}
            <div class="label-price">৳ {{ number_format($barcode->new_price) }}</div>

            {{-- Barcode (Code 128 — better scanner compatibility) --}}
            <div class="label-barcode" style="margin: 4px 0 2px">
                <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode->pro_barcode, 'C128') }}" alt="{{ $barcode->pro_barcode }}">
            </div>

            {{-- Numeric code under barcode --}}
            <div class="label-code">{{ $barcode->pro_barcode }}</div>
        </div>
        @endfor
    </div>
    @endif
</div>

<script>
function printFunction() { window.print(); }
</script>
@endsection

@section('script')
<script src="{{ asset('public/backEnd/') }}/assets/libs/select2/js/select2.min.js"></script>
<script>
$(document).ready(function () {
    $(".select2").select2();

    // Sync type hidden input on product selection
    $('#product_id').on('change', function () {
        var type = $(this).find(':selected').data('type');
        $('#type-input').val(type !== undefined ? type : '');
    });

    // Column selector buttons
    var savedCols = {{ request()->cols ?? 3 }};
    $('.col-count-btn').removeClass('active');
    $('.col-count-btn[data-cols="' + savedCols + '"]').addClass('active');
    updateGrid(savedCols);

    $('#col-selector').on('click', '.col-count-btn', function () {
        var cols = $(this).data('cols');
        $('.col-count-btn').removeClass('active');
        $(this).addClass('active');
        $('#cols-input').val(cols);
        updateGrid(cols);
    });

    function updateGrid(cols) {
        var labelW = cols <= 2 ? 240 : cols <= 3 ? 200 : cols <= 4 ? 175 : 150;
        $('#labels-grid').css({
            'grid-template-columns': 'repeat(' + cols + ', ' + labelW + 'px)',
            '--cols': cols
        });
    }
});
</script>
@endsection
