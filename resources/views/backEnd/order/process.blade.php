@extends('backEnd.layouts.master')
@section('title', 'Order Process')
@section('css')
    <style>
        .increment_btn,
        .remove_btn {
            margin-top: -17px;
            margin-bottom: 10px;
        }
    </style>
    <link href="{{ asset('public/backEnd') }}/assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('public/backEnd') }}/assets/libs/summernote/summernote-lite.min.css" rel="stylesheet"
        type="text/css" />
@endsection
@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Order Process [Invoice : #{{ $data->invoice_id }}]</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-shopping-bag me-1 text-primary"></i> Order Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%">SL</th>
                                        <th style="width: 10%">Image</th>
                                        <th style="width: 45%">Product</th>
                                        <th style="width: 10%" class="text-center">Qty</th>
                                        <th style="width: 15%" class="text-end">Price</th>
                                        <th style="width: 15%" class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $subtotal = 0; @endphp
                                    @foreach ($data->orderdetails as $key => $product)
                                        @php 
                                            $itemTotal = $product->sale_price * $product->qty;
                                            $subtotal += $itemTotal;
                                        @endphp
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>
                                                <img src="{{ asset($product->image ?: (optional($product->productImage)->image ?? 'public/images/no-image.png')) }}" 
                                                     height="50" width="50" class="rounded border" alt="">
                                            </td>
                                            <td>
                                                <strong class="text-dark">{{ $product->product_name }}</strong>
                                                @if($product->product_color || $product->product_size)
                                                    <div class="mt-1 small">
                                                        @if($product->product_size) <span class="me-2 text-muted">Size: <strong class="badge bg-light text-dark border">{{ $product->product_size }}</strong></span> @endif
                                                        @if($product->product_color) <span class="text-muted">Color: <strong class="badge bg-light text-dark border">{{ $product->product_color }}</strong></span> @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-center fw-semibold text-dark">{{ $product->qty }}</td>
                                            <td class="text-end">৳{{ number_format($product->sale_price, 2) }}</td>
                                            <td class="text-end fw-bold text-dark">৳{{ number_format($itemTotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="table-light fw-bold text-dark">
                                        <td colspan="5" class="text-end">Subtotal:</td>
                                        <td class="text-end">৳{{ number_format($subtotal, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-cog me-1 text-primary"></i> Order Processing & Status Update</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.order_change') }}" method="POST" class="row"
                            data-parsley-validate="" name="editForm" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{ $data->id }}">

                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label fw-semibold text-muted">Customer Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        name="name" id="name" value="{{ $data->shipping ? $data->shipping->name : '' }}"
                                        placeholder="Name">
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="phone" class="form-label fw-semibold text-muted">Customer Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                        name="phone" id="phone"
                                        value="{{ $data->shipping ? $data->shipping->phone : '' }}" placeholder="Phone Number">
                                    @error('phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group mb-3">
                                    <label for="address" class="form-label fw-semibold text-muted">Customer Address</label>
                                    <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror" placeholder="Customer address...">{{ $data->shipping ? $data->shipping->address : '' }}</textarea>
                                    @error('address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            @if ($data->order_type == 'goods')
                                <div class="col-sm-12">
                                    <div class="form-group mb-3">
                                        <label for="area" class="form-label fw-semibold text-muted">Delivery Area *</label>
                                        <select type="area" id="area"
                                            class="form-control @error('area') is-invalid @enderror" name="area"
                                            required>
                                            @foreach ($shippingcharge as $key => $value)
                                                <option @if (($data->shipping ? $data->shipping->area : '') == $value->name) selected @endif
                                                    value="{{ $value->id }}">{{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('area')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            <div class="col-sm-12">
                                <div class="form-group mb-3">
                                    <label for="status" class="form-label fw-semibold text-muted">Order Status</label>
                                    <select class="form-control select2 @error('status') is-invalid @enderror"
                                        name="status" required>
                                        <option value="">Select..</option>
                                        @foreach ($orderstatus as $value)
                                            <option value="{{ $value->id }}"
                                                @if ($data->order_status == $value->id) selected @endif>{{ $value->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group mb-3">
                                    <label for="admin_note" class="form-label fw-semibold text-muted">Admin Note</label>
                                    <textarea name="admin_note" rows="2" class="form-control" placeholder="Optional admin note...">{{ $data->admin_note }}</textarea>
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                                    <i class="fas fa-check-circle me-1"></i> Update Order Status
                                </button>
                            </div>

                        </form>

                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div> <!-- end col-->
        </div>
    </div>
@endsection


@section('script')
    <script src="{{ asset('public/backEnd/') }}/assets/libs/parsleyjs/parsley.min.js"></script>
    <script src="{{ asset('public/backEnd/') }}/assets/js/pages/form-validation.init.js"></script>
    <script src="{{ asset('public/backEnd/') }}/assets/libs/select2/js/select2.min.js"></script>
    <script src="{{ asset('public/backEnd/') }}/assets/js/pages/form-advanced.init.js"></script>
    <!-- Plugins js -->
    <script src="{{ asset('public/backEnd/') }}/assets/libs//summernote/summernote-lite.min.js"></script>
    <script>
        $(".summernote").summernote({
            placeholder: "Enter Your Text Here",

        });
    </script>
@endsection
