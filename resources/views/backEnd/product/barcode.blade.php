@extends('backEnd.layouts.master')
@section('title', 'Barcode Print')
@section('css')
    <link href="{{ asset('public/backEnd') }}/assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <style>
        /* ── CSS Custom Properties ───────────────────── */
        :root {
            --diecut-top-margin: 11.6mm;
            --diecut-left-margin: 9.7mm;
            --diecut-row-gap: 0mm;
            --diecut-col-gap: 0mm;
            --diecut-font-scale: 1;
            --diecut-barcode-height: 25px;

            --roll-top-margin: 0mm;
            --roll-left-margin: 0mm;
            --roll-barcode-height: 25px;
            --roll-font-scale: 1;
        }

        /* ── Common Print Styles ─────────────────────── */
        @page {
            size: A4 portrait;
            margin: 0 !important;
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
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body.layout-diecut {
                padding: 0 !important;
            }

            body.layout-roll {
                padding: 0 !important;
            }

            body.layout-standard {
                padding: 4mm !important;
            }

            body.layout-diecut .print-grid-container,
            body.layout-diecut .roll-container {
                display: none !important;
            }

            body.layout-roll .print-grid-container,
            body.layout-roll .diecut-container {
                display: none !important;
            }

            body.layout-standard .diecut-container,
            body.layout-standard .roll-container {
                display: none !important;
            }
        }

        /* ── A4 Die-Cut Label Design (38.1mm x 24.89mm)  */
        .diecut-page {
            width: 210mm;
            height: 297mm;
            padding-top: var(--diecut-top-margin);
            padding-left: var(--diecut-left-margin);
            padding-right: var(--diecut-left-margin);
            box-sizing: border-box;
            background: #fff;
            position: relative;
        }

        .diecut-grid {
            display: grid;
            grid-template-columns: repeat(5, 38.1mm);
            grid-auto-rows: 24.89mm;
            column-gap: var(--diecut-col-gap);
            row-gap: var(--diecut-row-gap);
            align-content: start;
        }

        .diecut-label {
            box-sizing: border-box;
            width: 38.1mm;
            height: 24.89mm;
            border: 1px dashed rgba(0, 0, 0, 0.15);
            padding: 1.5mm 2mm;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            background: #fff;
            color: #000;
        }

        .diecut-product-name {
            font-size: calc(7.5px * var(--diecut-font-scale));
            font-weight: 700;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0;
        }

        .diecut-variant {
            font-size: calc(6px * var(--diecut-font-scale));
            line-height: 1;
            color: #555;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0.5px 0;
        }

        .diecut-price {
            font-size: calc(8.5px * var(--diecut-font-scale));
            font-weight: 900;
            margin: 0;
            line-height: 1.1;
        }

        .diecut-barcode {
            margin: 0.5px 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .diecut-barcode img {
            max-width: 100%;
            height: var(--diecut-barcode-height);
            display: block;
        }

        .diecut-code {
            font-family: monospace;
            font-size: calc(6.5px * var(--diecut-font-scale));
            line-height: 1;
            letter-spacing: 0.5px;
            margin: 0;
        }

        /* ── Roll Label Design (40mm x 30mm) ── */
        .roll-container {
            display: none;
        }

        .roll-label {
            box-sizing: border-box;
            width: 40mm;
            height: 30mm;
            border-radius: 4px; /* Rounded corners (Die-Cut) */
            border: 1px dashed rgba(0, 0, 0, 0.15);
            background: #fff;
            padding: 2mm 3mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            overflow: hidden;
            color: #000;
        }

        .roll-product-name {
            font-size: calc(7.5px * var(--roll-font-scale));
            font-weight: 700;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            margin: 0 0 1px 0;
        }

        .roll-variant {
            font-size: calc(7.5px * var(--roll-font-scale));
            font-weight: 700;
            line-height: 1;
            color: #555;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            margin: 0 0 1px 0;
        }

        .roll-price {
            font-size: calc(8.5px * var(--roll-font-scale));
            font-weight: 900;
            margin: 0 0 1px 0;
            line-height: 1.1;
        }

        .roll-barcode {
            margin: 1px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .roll-barcode img {
            max-width: 100%;
            height: var(--roll-barcode-height);
            display: block;
            margin: 0 auto;
        }

        .roll-code {
            font-family: monospace;
            font-size: calc(8.5px * var(--roll-font-scale));
            font-weight: 900;
            line-height: 1;
            letter-spacing: 0.5px;
            margin: 1px 0 0 0;
        }

        /* ── Standard Grid Styles ───────────────────── */
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
            page-break-inside: avoid;
            break-inside: avoid;
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

        /* ── Screen Layout & Previews ───────────────── */
        @media screen {
            .diecut-container {
                background: #6c757d;
                padding: 30px 15px;
                border-radius: 8px;
                box-shadow: inset 0 0 12px rgba(0,0,0,0.3);
                overflow-x: auto;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 25px;
                max-height: 800px;
                overflow-y: auto;
                margin-bottom: 30px;
            }

            .diecut-page {
                box-shadow: 0 10px 25px rgba(0,0,0,0.35);
                border: 1px solid #495057;
                border-radius: 4px;
                flex-shrink: 0;
            }

            .diecut-label:hover {
                outline: 1px solid #4361ee;
                background-color: #f8f9ff;
            }

            /* Roll Screen View - continuous strip of stickers with rounded corners and 3mm gap */
            .roll-container {
                background: #495057;
                padding: 40px 20px;
                border-radius: 8px;
                box-shadow: inset 0 0 12px rgba(0,0,0,0.4);
                overflow-x: auto;
                display: flex;
                flex-direction: row;
                gap: 3mm; /* Gap height: 3mm */
                align-items: center;
                justify-content: start;
                margin-bottom: 30px;
            }

            .roll-label {
                box-shadow: 0 6px 15px rgba(0,0,0,0.3);
                border: 1px solid #ced4da;
                flex-shrink: 0;
                width: 40mm;
                height: 30mm;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
            }

            .roll-label:hover {
                outline: 2px solid #4361ee;
                background-color: #f8f9ff;
            }

            @media (max-width: 768px) {
                .diecut-container, .roll-container {
                    padding: 15px 5px;
                }
            }
        }

        @media print {
            .diecut-page {
                page-break-after: always;
                page-break-inside: avoid;
                break-inside: avoid;
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
            }

            .diecut-label {
                border: 1px dashed transparent !important;
            }

            .diecut-label-placeholder {
                display: block !important;
                visibility: hidden !important;
                border: none !important;
            }

            body.layout-roll .roll-container {
                display: block !important;
                background: transparent !important;
                padding: 0 !important;
                box-shadow: none !important;
                margin: 0 !important;
            }

            body.layout-roll .roll-label {
                width: 40mm !important;
                height: 30mm !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                box-sizing: border-box !important;
                padding: calc(2mm + var(--roll-top-margin)) calc(3mm + var(--roll-left-margin)) 2mm 3mm !important;
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                background: #fff !important;
                color: #000 !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: center !important;
                align-items: center !important;
                text-align: center !important;
            }
        }

        /* Controls */
        .print-controls {
            margin: 0 auto;
            border-top: 3px solid #4361ee;
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
                <div class="card print-controls shadow-sm">
                    <div class="card-body">
                        <form action="" id="print-barcode-form" method="GET" class="row g-3 align-items-end">
                            {{-- Product Selector --}}
                            <div class="col-sm-5">
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

                            {{-- Layout Type Selection --}}
                            <div class="col-sm-3">
                                <label class="form-label fw-semibold">Print Template</label>
                                <select class="form-select" id="layout-selector" name="layout">
                                    <option value="roll" {{ (request()->layout ?? 'roll') == 'roll' ? 'selected' : '' }}>Roll Label (40mm x 30mm)</option>
                                    <option value="diecut" {{ (request()->layout ?? 'roll') == 'diecut' ? 'selected' : '' }}>A4 Die-Cut Sheet</option>
                                    <option value="standard" {{ (request()->layout ?? 'roll') == 'standard' ? 'selected' : '' }}>Standard Grid (Custom Cols)</option>
                                </select>
                            </div>

                            {{-- Skip Labels (Only for Diecut Layout) --}}
                            <div class="col-sm-2" id="skip-group">
                                <label class="form-label fw-semibold">Skip Labels</label>
                                <input type="number" name="skip" id="skip-input" class="form-control" value="{{ request()->skip ?? 0 }}" min="0" max="54" placeholder="0">
                            </div>

                            {{-- Columns (Visible only when standard layout is active) --}}
                            <div class="col-sm-3" id="col-selector-group" style="display: none;">
                                <label class="form-label fw-semibold">Columns</label>
                                <div class="btn-group w-100" id="col-selector">
                                    <button type="button" class="col-count-btn btn btn-outline-secondary" data-cols="2">2</button>
                                    <button type="button" class="col-count-btn btn btn-outline-secondary active" data-cols="3">3</button>
                                    <button type="button" class="col-count-btn btn btn-outline-secondary" data-cols="4">4</button>
                                    <button type="button" class="col-count-btn btn btn-outline-secondary" data-cols="5">5</button>
                                </div>
                                <input type="hidden" name="cols" id="cols-input" value="{{ request()->cols ?? 3 }}">
                            </div>

                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-success w-100 fw-bold"><i
                                        class="ri-qr-code-line me-1"></i> Generate</button>
                            </div>

                            {{-- Hidden inputs container --}}
                            <div id="hidden-inputs-container"></div>
                        </form>

                        {{-- Spacing Calibrator for Die-Cut --}}
                        <div class="mt-3 p-3 bg-light border rounded-3" id="diecut-calibrator" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <h6 class="m-0 fw-semibold text-primary"><i class="ri-settings-4-line me-1"></i> A4 Sheet Printer Calibration</h6>
                                    <span class="badge bg-info ms-2 text-dark small" style="font-size:10px;">Instant Screen Update</span>
                                </div>
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" id="reset-diecut-btn" style="font-size: 11px;">Reset Defaults</button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-2 col-sm-4">
                                    <label class="form-label mb-1 small fw-semibold">Top Margin: <span id="top-margin-val">11.6</span>mm</label>
                                    <input type="range" class="form-range calibrator-input" id="top-margin-slider" min="0" max="30" step="0.1" value="11.6">
                                </div>
                                <div class="col-md-2 col-sm-4">
                                    <label class="form-label mb-1 small fw-semibold">Side Margin: <span id="left-margin-val">9.7</span>mm</label>
                                    <input type="range" class="form-range calibrator-input" id="left-margin-slider" min="0" max="30" step="0.1" value="9.7">
                                </div>
                                <div class="col-md-2 col-sm-4">
                                    <label class="form-label mb-1 small fw-semibold">Row Gap: <span id="row-gap-val">0.0</span>mm</label>
                                    <input type="range" class="form-range calibrator-input" id="row-gap-slider" min="0" max="10" step="0.1" value="0">
                                </div>
                                <div class="col-md-2 col-sm-4">
                                    <label class="form-label mb-1 small fw-semibold">Col Gap: <span id="col-gap-val">0.0</span>mm</label>
                                    <input type="range" class="form-range calibrator-input" id="col-gap-slider" min="0" max="10" step="0.1" value="0">
                                </div>
                                <div class="col-md-2 col-sm-4">
                                    <label class="form-label mb-1 small fw-semibold">Barcode Height: <span id="barcode-height-val">25</span>px</label>
                                    <input type="range" class="form-range calibrator-input" id="barcode-height-slider" min="15" max="45" step="1" value="25">
                                </div>
                                <div class="col-md-2 col-sm-4">
                                    <label class="form-label mb-1 small fw-semibold">Font Size: <span id="font-scale-val">100</span>%</label>
                                    <input type="range" class="form-range calibrator-input" id="font-scale-slider" min="70" max="130" step="5" value="100">
                                </div>
                            </div>
                        </div>

                        {{-- Spacing Calibrator for Roll Labels --}}
                        <div class="mt-3 p-3 bg-light border rounded-3" id="roll-calibrator" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <h6 class="m-0 fw-semibold text-primary"><i class="ri-settings-4-line me-1"></i> Roll Printer Calibration (40mm x 30mm)</h6>
                                    <span class="badge bg-info ms-2 text-dark small" style="font-size:10px;">Instant Screen Update</span>
                                </div>
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" id="reset-roll-btn" style="font-size: 11px;">Reset Defaults</button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label mb-1 small fw-semibold">Top Margin Offset: <span id="roll-top-margin-val">0.0</span>mm</label>
                                    <input type="range" class="form-range roll-calibrator-input" id="roll-top-margin-slider" min="-5" max="5" step="0.1" value="0.0">
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label mb-1 small fw-semibold">Left Margin Offset: <span id="roll-left-margin-val">0.0</span>mm</label>
                                    <input type="range" class="form-range roll-calibrator-input" id="roll-left-margin-slider" min="-5" max="5" step="0.1" value="0.0">
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label mb-1 small fw-semibold">Barcode Height: <span id="roll-barcode-height-val">25</span>px</label>
                                    <input type="range" class="form-range roll-calibrator-input" id="roll-barcode-height-slider" min="15" max="45" step="1" value="25">
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label mb-1 small fw-semibold">Font Size: <span id="roll-font-scale-val">100</span>%</label>
                                    <input type="range" class="form-range roll-calibrator-input" id="roll-font-scale-slider" min="70" max="130" step="5" value="100">
                                </div>
                            </div>
                        </div>

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
                $layout = request()->layout ?? 'roll';
                $skip = max(0, min(54, (int)(request()->skip ?? 0)));
                
                $total_labels = 0;
                $flat_labels = [];
                
                // Add empty placeholders for skip
                for ($i = 0; $i < $skip; $i++) {
                    $flat_labels[] = ['is_placeholder' => true];
                }
                
                foreach ($selected_items as $item) {
                    $total_labels += $item['copies'];
                    for ($i = 0; $i < $item['copies']; $i++) {
                        $item_copy = $item;
                        $item_copy['is_placeholder'] = false;
                        $flat_labels[] = $item_copy;
                    }
                }
                
                // Chunk the flat labels into pages of 55 labels (5 columns x 11 rows)
                $label_pages = array_chunk($flat_labels, 55);
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

            {{-- Die-Cut Layout Page Container (For A4 Die-Cut labels: 1.50" x 0.98") --}}
            <div class="diecut-container" id="diecut-wrapper">
                @foreach ($label_pages as $page_index => $page_labels)
                    <div class="diecut-page">
                        <div class="diecut-grid">
                            @foreach ($page_labels as $label)
                                @if ($label['is_placeholder'])
                                    <div class="diecut-label diecut-label-placeholder" style="visibility: hidden; border: none !important;"></div>
                                @else
                                    <div class="diecut-label">
                                        {{-- Product Name --}}
                                        <div class="diecut-product-name" title="{{ $label['name'] }}">{{ $label['name'] }}</div>

                                        {{-- Variant info (only for variant products) --}}
                                        @if ($label['type'] == 0)
                                            <div class="diecut-variant">
                                                @if($label['size']) <span>S: <strong>{{ $label['size'] }}</strong></span> @endif
                                                @if($label['color']) <span style="margin-left:4px">C: <strong>{{ $label['color'] }}</strong></span> @endif
                                            </div>
                                        @else
                                            <div class="diecut-variant">&nbsp;</div>
                                        @endif

                                        {{-- Price --}}
                                        <div class="diecut-price">৳ {{ number_format($label['new_price']) }}</div>

                                        {{-- Barcode (Code 128 — better scanner compatibility) --}}
                                        <div class="diecut-barcode">
                                            <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($label['pro_barcode'], 'C128') }}"
                                                alt="{{ $label['pro_barcode'] }}">
                                        </div>

                                        {{-- Numeric code under barcode --}}
                                        <div class="diecut-code">{{ $label['pro_barcode'] }}</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Roll Layout Container (For 1.5" x 1" thermal label rolls) --}}
            <div class="roll-container" id="roll-wrapper">
                @foreach ($flat_labels as $label)
                    @if (!$label['is_placeholder'])
                        <div class="roll-label">
                            {{-- Product Name --}}
                            <div class="roll-product-name" title="{{ $label['name'] }}">{{ $label['name'] }}</div>

                            {{-- Variant info (only for variant products) --}}
                            @if ($label['type'] == 0)
                                <div class="roll-variant">
                                    @if($label['size']) <span>Size: <strong>{{ $label['size'] }}</strong></span> @endif
                                    @if($label['color']) <span style="margin-left:4px">Color: <strong>{{ $label['color'] }}</strong></span> @endif
                                </div>
                            @else
                                <div class="roll-variant">&nbsp;</div>
                            @endif

                            {{-- Price --}}
                            <div class="roll-price">৳ {{ number_format($label['new_price']) }}</div>

                            {{-- Barcode (Code 128) --}}
                            <div class="roll-barcode">
                                <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($label['pro_barcode'], 'C128') }}"
                                    alt="{{ $label['pro_barcode'] }}">
                            </div>

                            {{-- Numeric code under barcode --}}
                            <div class="roll-code">{{ $label['pro_barcode'] }}</div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Standard Layout Container (For general dynamic columns) --}}
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

                                {{-- Barcode (Code 128) --}}
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
                        '<td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="ri-delete-bin-line"></i></button></td>' +
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

            // Column selector buttons for standard layout
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

            // ── Dynamic Layout Switching ──
            var initialLayout = $('#layout-selector').val() || 'roll';
            
            function updatePrintPageStyle(layout) {
                var styleEl = $('#print-page-style');
                if (styleEl.length === 0) {
                    styleEl = $('<style id="print-page-style">').appendTo('head');
                }
                
                if (layout === 'roll') {
                    styleEl.html('@page { size: 40mm 30mm; margin: 0 !important; }');
                } else if (layout === 'diecut') {
                    styleEl.html('@page { size: A4 portrait; margin: 0 !important; }');
                } else {
                    styleEl.html('@page { size: A4 portrait; margin: 0 !important; }');
                }
            }

            function toggleLayoutViews(layout) {
                $('body').removeClass('layout-diecut layout-standard layout-roll').addClass('layout-' + layout);
                updatePrintPageStyle(layout);

                if (layout === 'diecut') {
                    $('#diecut-wrapper').show();
                    $('#roll-wrapper').hide();
                    $('.print-grid-container').hide();
                    $('#col-selector-group').hide();
                    $('#diecut-calibrator').show();
                    $('#roll-calibrator').hide();
                    $('#skip-group').show();
                } else if (layout === 'roll') {
                    $('#diecut-wrapper').hide();
                    $('#roll-wrapper').show();
                    $('.print-grid-container').hide();
                    $('#col-selector-group').hide();
                    $('#diecut-calibrator').hide();
                    $('#roll-calibrator').show();
                    $('#skip-group').hide();
                } else {
                    $('#diecut-wrapper').hide();
                    $('#roll-wrapper').hide();
                    $('.print-grid-container').show();
                    $('#col-selector-group').show();
                    $('#diecut-calibrator').hide();
                    $('#roll-calibrator').hide();
                    $('#skip-group').hide();
                }
            }
            
            $('#layout-selector').on('change', function () {
                var layout = $(this).val();
                toggleLayoutViews(layout);
            });
            
            toggleLayoutViews(initialLayout);

            // ── A4 Die-Cut Calibration & Offset Settings ──
            var defaultCalibration = {
                topMargin: 11.6,
                leftMargin: 9.7,
                rowGap: 0.0,
                colGap: 0.0,
                barcodeHeight: 25,
                fontScale: 100
            };

            function loadCalibration() {
                var calibration = JSON.parse(localStorage.getItem('barcode_calibration') || '{}');
                return $.extend({}, defaultCalibration, calibration);
            }

            function saveCalibration(calibration) {
                localStorage.setItem('barcode_calibration', JSON.stringify(calibration));
            }

            function applyCalibration(cal) {
                // Set input values
                $('#top-margin-slider').val(cal.topMargin);
                $('#left-margin-slider').val(cal.leftMargin);
                $('#row-gap-slider').val(cal.rowGap);
                $('#col-gap-slider').val(cal.colGap);
                $('#barcode-height-slider').val(cal.barcodeHeight);
                $('#font-scale-slider').val(cal.fontScale);

                // Set labels text
                $('#top-margin-val').text(cal.topMargin);
                $('#left-margin-val').text(cal.leftMargin);
                $('#row-gap-val').text(cal.rowGap.toFixed(1));
                $('#col-gap-val').text(cal.colGap.toFixed(1));
                $('#barcode-height-val').text(cal.barcodeHeight);
                $('#font-scale-val').text(cal.fontScale);

                // Set CSS variables on root element
                var root = document.documentElement;
                root.style.setProperty('--diecut-top-margin', cal.topMargin + 'mm');
                root.style.setProperty('--diecut-left-margin', cal.leftMargin + 'mm');
                root.style.setProperty('--diecut-row-gap', cal.rowGap + 'mm');
                root.style.setProperty('--diecut-col-gap', cal.colGap + 'mm');
                root.style.setProperty('--diecut-barcode-height', cal.barcodeHeight + 'px');
                root.style.setProperty('--diecut-font-scale', (cal.fontScale / 100));
            }

            // Load and apply on ready
            var calSettings = loadCalibration();
            applyCalibration(calSettings);

            // Listeners for sliders
            $('.calibrator-input').on('input change', function () {
                var newCal = {
                    topMargin: parseFloat($('#top-margin-slider').val()),
                    leftMargin: parseFloat($('#left-margin-slider').val()),
                    rowGap: parseFloat($('#row-gap-slider').val()),
                    colGap: parseFloat($('#col-gap-slider').val()),
                    barcodeHeight: parseInt($('#barcode-height-slider').val()),
                    fontScale: parseInt($('#font-scale-slider').val())
                };
                applyCalibration(newCal);
                saveCalibration(newCal);
            });

            // Reset calibration to defaults
            $('#reset-diecut-btn').on('click', function () {
                applyCalibration(defaultCalibration);
                saveCalibration(defaultCalibration);
            });

            // ── Roll Calibration Settings ──
            var defaultRollCalibration = {
                topMargin: 0.0,
                leftMargin: 0.0,
                barcodeHeight: 25,
                fontScale: 100
            };

            function loadRollCalibration() {
                var calibration = JSON.parse(localStorage.getItem('roll_barcode_calibration') || '{}');
                return $.extend({}, defaultRollCalibration, calibration);
            }

            function saveRollCalibration(calibration) {
                localStorage.setItem('roll_barcode_calibration', JSON.stringify(calibration));
            }

            function applyRollCalibration(cal) {
                // Set input values
                $('#roll-top-margin-slider').val(cal.topMargin);
                $('#roll-left-margin-slider').val(cal.leftMargin);
                $('#roll-barcode-height-slider').val(cal.barcodeHeight);
                $('#roll-font-scale-slider').val(cal.fontScale);

                // Set labels text
                $('#roll-top-margin-val').text(cal.topMargin.toFixed(1));
                $('#roll-left-margin-val').text(cal.leftMargin.toFixed(1));
                $('#roll-barcode-height-val').text(cal.barcodeHeight);
                $('#roll-font-scale-val').text(cal.fontScale);

                // Set CSS variables on root element
                var root = document.documentElement;
                root.style.setProperty('--roll-top-margin', cal.topMargin + 'mm');
                root.style.setProperty('--roll-left-margin', cal.leftMargin + 'mm');
                root.style.setProperty('--roll-barcode-height', cal.barcodeHeight + 'px');
                root.style.setProperty('--roll-font-scale', (cal.fontScale / 100));
            }

            // Load and apply on ready
            var rollCalSettings = loadRollCalibration();
            applyRollCalibration(rollCalSettings);

            // Listeners for sliders
            $('.roll-calibrator-input').on('input change', function () {
                var newCal = {
                    topMargin: parseFloat($('#roll-top-margin-slider').val()),
                    leftMargin: parseFloat($('#roll-left-margin-slider').val()),
                    barcodeHeight: parseInt($('#roll-barcode-height-slider').val()),
                    fontScale: parseInt($('#roll-font-scale-slider').val())
                };
                applyRollCalibration(newCal);
                saveRollCalibration(newCal);
            });

            // Reset calibration to defaults
            $('#reset-roll-btn').on('click', function () {
                applyRollCalibration(defaultRollCalibration);
                saveRollCalibration(defaultRollCalibration);
            });
        });
    </script>
@endsection