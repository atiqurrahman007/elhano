@extends('backEnd.layouts.master')
@section('title', 'Barcode Print')
@section('css')
    <link href="{{ asset('public/backEnd') }}/assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <style>
        /* ── Print Styles ─────────────────────────────── */
        @page {
            margin: 4mm;
        }

        @media print {

            header,
            footer,
            .no-print,
            .left-side-menu,
            .navbar-custom,
            .page-title-box {
                display: none !important;
            }

            body {
                font-size: 9px !important;
                margin: 0 !important;
                background: #fff;
            }

            .print-grid-container {
                overflow: visible !important;
                padding: 0 !important;
                background: transparent !important;
                border: none !important;
                margin: 0 !important;
            }

            .print-grid {
                display: grid !important;
                grid-template-columns: repeat(var(--cols, 3), 1fr) !important;
                gap: 3mm !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .label-card {
                border: 1px solid #ccc !important;
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }

        /* ── Screen Styles ───────────────────────────── */
        .print-grid-container {
            overflow-x: auto;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px dashed #dee2e6;
            margin-bottom: 30px;
        }

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

        .label-barcode img {
            max-width: 100%;
            height: 36px;
            display: block;
            margin: 0 auto;
        }

        .label-code {
            font-family: monospace;
            font-size: 9px;
            letter-spacing: 1px;
            margin-top: 1px;
        }

        /* Controls */
        .print-controls {
            margin: 0 auto;
        }

        .col-count-btn {
            border-radius: 6px;
        }

        .col-count-btn.active {
            background: #4361ee !important;
            color: #fff !important;
            border-color: #4361ee !important;
        }

        #selected-products-table .copies-input {
            width: 70px;
            margin: 0 auto;
        }
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
                        <form action="" id="print-barcode-form" method="GET" class="row g-3 align-items-end">
                            {{-- Product Selector --}}
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Select Product / Variant *</label>
                                <div class="d-flex align-items-center">
                                    <div style="flex-grow: 1; min-width: 0;">
                                        <select class="form-control select2" id="product_selector">
                                            <option value="">Choose product…</option>
                                            @foreach ($data as $value)
                                                @if ($value->type == 1)
                                                    @if($value->pro_barcode)
                                                        <option value="{{ $value->id }}" data-type="1" data-name="{{ $value->name }}"
                                                            data-barcode="{{ $value->pro_barcode }}"
                                                            data-price="{{ $value->new_price }}">
                                                            {{ $value->name }} — ৳{{ $value->new_price }} ({{ $value->pro_barcode }})
                                                        </option>
                                                    @endif
                                                @else
                                                    @foreach ($value->variables as $variable)
                                                        @if($variable->pro_barcode)
                                                            <option value="{{ $variable->id }}" data-type="0" data-name="{{ $value->name }}"
                                                                data-size="{{ $variable->size }}" data-color="{{ $variable->color }}"
                                                                data-barcode="{{ $variable->pro_barcode }}"
                                                                data-price="{{ $variable->new_price }}">
                                                                {{ $value->name }}
                                                                @if($variable->size) — {{ $variable->size }}@endif
                                                                @if($variable->color) / {{ $variable->color }}@endif
                                                                — ৳{{ $variable->new_price }} ({{ $variable->pro_barcode }})
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" id="add-to-list-btn" class="btn btn-primary ms-2 px-3 fw-bold"><i
                                            class="ri-add-line"></i> Add</button>
                                </div>
                            </div>

                            {{-- Columns --}}
                            <div class="col-sm-4">
                                <label class="form-label fw-semibold">Label Columns</label>
                                <div class="btn-group w-100" id="col-selector">
                                    <button type="button" class="col-count-btn btn btn-outline-secondary" data-cols="2">2
                                        Cols</button>
                                    <button type="button" class="col-count-btn btn btn-outline-secondary active"
                                        data-cols="3">3 Cols</button>
                                    <button type="button" class="col-count-btn btn btn-outline-secondary" data-cols="4">4
                                        Cols</button>
                                    <button type="button" class="col-count-btn btn btn-outline-secondary" data-cols="5">5
                                        Cols</button>
                                </div>
                                <input type="hidden" name="cols" id="cols-input" value="{{ request()->cols ?? 3 }}">
                            </div>

                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-success w-100 fw-bold"><i
                                        class="ri-qr-code-line me-1"></i> Generate Labels</button>
                            </div>

                            {{-- Hidden inputs container --}}
                            <div id="hidden-inputs-container"></div>
                        </form>

                        {{-- Selected Products Table --}}
                        <div class="mt-4">
                            <h5 class="fw-semibold mb-2">Selected Products List</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle" id="selected-products-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Barcode</th>
                                            <th>Price</th>
                                            <th style="width: 120px;">Copies</th>
                                            <th style="width: 50px;">Remove</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Pre-populated rows from backend --}}
                                        @foreach ($selected_items as $item)
                                            <tr data-id="{{ $item['id'] }}" data-type="{{ $item['type'] }}"
                                                data-name="{{ $item['name'] }}" data-barcode="{{ $item['pro_barcode'] }}"
                                                data-price="{{ $item['new_price'] }}" data-size="{{ $item['size'] }}"
                                                data-color="{{ $item['color'] }}">
                                                <td>
                                                    <strong>{{ $item['name'] }}</strong>
                                                    @if($item['size'] || $item['color'])
                                                        <span class="badge bg-secondary ms-1">
                                                            @if($item['size']){{ $item['size'] }}@endif
                                                            @if($item['size'] && $item['color'])/@endif
                                                            @if($item['color']){{ $item['color'] }}@endif
                                                        </span>
                                                    @endif
                                                </td>
                                                <td><code>{{ $item['pro_barcode'] }}</code></td>
                                                <td>৳ {{ number_format($item['new_price']) }}</td>
                                                <td>
                                                    <input type="number"
                                                        class="form-control form-control-sm copies-input text-center fw-bold"
                                                        value="{{ $item['copies'] }}" min="1" max="200">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-outline-danger btn-sm remove-row-btn"><i
                                                            class="ri-delete-bin-line"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr id="empty-table-row"
                                            style="{{ count($selected_items) > 0 ? 'display: none;' : '' }}">
                                            <td colspan="5" class="text-center text-muted py-3">No products selected. Choose
                                                a product and click "Add".</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (count($selected_items) > 0)
            @php
                $cols = max(2, min(5, (int) (request()->cols ?? 3)));
                $total_labels = 0;
                foreach ($selected_items as $item) {
                    $total_labels += $item['copies'];
                }
            @endphp

            {{-- Print Button --}}
            <div class="row no-print mb-3">
                <div class="col-12 text-center">
                    <button onclick="printFunction()" class="btn btn-success px-5 py-2 fw-bold" style="font-size:16px;">
                        <i class="ri-printer-line me-1"></i> Print {{ $total_labels }} Label{{ $total_labels > 1 ? 's' : '' }}
                    </button>
                    <a href="{{ route('products.barcode') }}" class="btn btn-outline-secondary ms-2">Reset / Clear All</a>
                </div>
            </div>

            {{-- Labels Grid Wrapper (for responsive horizontal scrolling on screen, normal printing on paper) --}}
            <div class="print-grid-container">
                <div class="print-grid" id="labels-grid" style="--cols: {{ $cols }}">
                    @foreach ($selected_items as $item)
                        @for ($i = 0; $i < $item['copies']; $i++)
                            <div class="label-card">
                                {{-- Product Name --}}
                                <div class="label-product-name" title="{{ $item['name'] }}">{{ $item['name'] }}</div>

                                {{-- Variant info (only for variant products) --}}
                                @if ($item['type'] == 0)
                                    <div class="label-variant">
                                        @if($item['size']) <span>Size: <strong>{{ $item['size'] }}</strong></span> @endif
                                        @if($item['color']) <span style="margin-left:4px">Color:
                                        <strong>{{ $item['color'] }}</strong></span> @endif
                                    </div>
                                @endif

                                {{-- Price --}}
                                <div class="label-price">৳ {{ number_format($item['new_price']) }}</div>

                                {{-- Barcode (Code 128 — better scanner compatibility) --}}
                                <div class="label-barcode" style="margin: 4px 0 2px">
                                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($item['pro_barcode'], 'C128') }}"
                                        alt="{{ $item['pro_barcode'] }}">
                                </div>

                                {{-- Numeric code under barcode --}}
                                <div class="label-code">{{ $item['pro_barcode'] }}</div>
                            </div>
                        @endfor
                    @endforeach
                </div>
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

            // Rebuild hidden inputs based on current table rows
            function syncHiddenInputs() {
                var container = $('#hidden-inputs-container');
                container.empty();

                var rows = $('#selected-products-table tbody tr').not('#empty-table-row');
                if (rows.length === 0) {
                    $('#empty-table-row').show();
                } else {
                    $('#empty-table-row').hide();
                    rows.each(function () {
                        var id = $(this).data('id');
                        var type = $(this).data('type');
                        var copies = $(this).find('.copies-input').val();

                        container.append('<input type="hidden" name="product_ids[]" value="' + id + '">');
                        container.append('<input type="hidden" name="types[]" value="' + type + '">');
                        container.append('<input type="hidden" name="copies[]" value="' + copies + '">');
                    });
                }
            }

            // Initialize hidden inputs on load
            syncHiddenInputs();

            // Add product to the table
            $('#add-to-list-btn').on('click', function () {
                var selector = $('#product_selector');
                var option = selector.find(':selected');
                var id = option.val();
                if (!id) return;

                var type = option.data('type');
                var name = option.data('name');
                var barcode = option.data('barcode');
                var price = option.data('price');
                var size = option.data('size') || '';
                var color = option.data('color') || '';

                // Check if already in the list
                var existingRow = null;
                $('#selected-products-table tbody tr').not('#empty-table-row').each(function () {
                    if ($(this).data('id') == id && $(this).data('type') == type) {
                        existingRow = $(this);
                        return false;
                    }
                });

                if (existingRow) {
                    // Increment quantity
                    var input = existingRow.find('.copies-input');
                    input.val(parseInt(input.val()) + 1);
                } else {
                    // Add new row
                    var badge = '';
                    if (size || color) {
                        badge = '<span class="badge bg-secondary ms-1">' + (size ? size : '') + (size && color ? '/' : '') + (color ? color : '') + '</span>';
                    }
                    var newRow = '<tr data-id="' + id + '" data-type="' + type + '" data-name="' + name + '" data-barcode="' + barcode + '" data-price="' + price + '" data-size="' + size + '" data-color="' + color + '">' +
                        '<td><strong>' + name + '</strong>' + badge + '</td>' +
                        '<td><code>' + barcode + '</code></td>' +
                        '<td>৳ ' + Number(price).toLocaleString() + '</td>' +
                        '<td><input type="number" class="form-control form-control-sm copies-input text-center fw-bold" value="1" min="1" max="200"></td>' +
                        '<td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="fe-trash-2"></i></button></td>' +
                        '</tr>';
                    $('#selected-products-table tbody').append(newRow);
                }

                // Reset selector
                selector.val('').trigger('change');
                syncHiddenInputs();
            });

            // Remove row
            $(document).on('click', '.remove-row-btn', function () {
                $(this).closest('tr').remove();
                syncHiddenInputs();
            });

            // Update hidden inputs on quantity change
            $(document).on('change keyup', '.copies-input', function () {
                syncHiddenInputs();
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