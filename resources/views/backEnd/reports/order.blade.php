@extends('backEnd.layouts.master')
@section('title','Order Report')
@section('content')
@section('css')
<link href="{{asset('public/backEnd')}}/assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
<link href="{{asset('public/backEnd/')}}/assets/libs/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css" />
<style>
    p{
        margin:0;
    }
   @page { 
        margin: 50px 0px 0px 0px;
    }
   @media print {
    td{
        font-size: 18px;
    }
    p{
        margin:0;
    }
    title {
        font-size: 25px;
    }
    header,footer,.no-print,.left-side-menu,.navbar-custom {
      display: none !important;
    }
  }

  /* Modern Filter Card Styling */
  .modern-filter-card {
      background: #ffffff;
      border: 1px solid #eef2f7;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
      padding: 24px;
      margin-bottom: 25px;
      position: relative;
  }
  .modern-filter-card .form-label {
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      font-weight: 700;
      color: #5c68ff;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
  }
  .modern-filter-card .form-label i {
      margin-right: 6px;
      font-size: 12px;
      color: #727cf5;
  }
  .modern-filter-card .form-control {
      border: 1px solid #d0d7de !important;
      border-radius: 8px !important;
      height: 38px !important;
      font-size: 13px !important;
      color: #313a46 !important;
      transition: all 0.2s ease-in-out !important;
  }
  .modern-filter-card .form-control:focus {
      border-color: #727cf5 !important;
      box-shadow: 0 0 0 3px rgba(114, 124, 245, 0.15) !important;
      outline: none !important;
  }
  
  /* Select2 Custom styling within Modern Filter Card */
  .modern-filter-card .select2-container--default .select2-selection--single {
      border: 1px solid #d0d7de !important;
      border-radius: 8px !important;
      height: 38px !important;
      transition: all 0.2s ease-in-out !important;
  }
  .modern-filter-card .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 36px !important;
      padding-left: 12px !important;
      font-size: 13px !important;
      color: #313a46 !important;
  }
  .modern-filter-card .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 36px !important;
  }
  .modern-filter-card .select2-container--default.select2-container--open .select2-selection--single {
      border-color: #727cf5 !important;
      box-shadow: 0 0 0 3px rgba(114, 124, 245, 0.15) !important;
  }
  .modern-filter-btn {
      transition: all 0.2s ease;
  }
  .modern-filter-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(114, 124, 245, 0.25) !important;
  }
</style>
@endsection 
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Order Report</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 
   <div class="row">
    <div class="col-12">
        <div class="modern-filter-card no-print">
            <h5 class="mb-3 text-dark fw-bold d-flex align-items-center">
                <i class="fas fa-sliders-h text-primary me-2"></i> Report Filter Options
            </h5>
            <form>
                <div class="row align-items-end">   
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="form-group mb-0">
                           <label for="keyword" class="form-label"><i class="fas fa-search"></i> Keyword</label>
                            <input type="text" value="{{request()->get('keyword')}}" class="form-control" name="keyword" placeholder="Search customer...">
                        </div>
                    </div>
                    <!--col-->
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="form-group mb-0">
                            <label for="user_id" class="form-label"><i class="fas fa-user-tie"></i> Assign User</label>
                            <select class="form-control select2 @error('user_id') is-invalid @enderror" name="user_id" value="{{ old('user_id') }}" >
                                <option value="">Select..</option>
                                @foreach($users as $key=>$value)
                                    <option value="{{$value->id}}" @if(request()->get('user_id') == $value->id) selected @endif>{{$value->name}}</option>
                                @endforeach
                            </select>
                            @error('user_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <!--col-->
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="form-group mb-0">
                            <label for="order_status" class="form-label"><i class="fas fa-info-circle"></i> Order Status</label>
                            <select class="form-control select2" name="order_status">
                                <option value="">All Status</option>
                                @foreach($statuses as $st)
                                    <option value="{{$st->id}}" @if($status == $st->id) selected @endif>{{$st->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!--col-->
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="form-group mb-0">
                            <label for="filter" class="form-label"><i class="fas fa-filter"></i> Quick Filter</label>
                            <select class="form-control select2" name="filter" id="filter-select">
                                <option value="">Choose Period...</option>
                                <option value="today" @if(request()->get('filter') == 'today') selected @endif>Today</option>
                                <option value="yesterday" @if(request()->get('filter') == 'yesterday') selected @endif>Yesterday</option>
                                <option value="this_week" @if(request()->get('filter') == 'this_week') selected @endif>This Week</option>
                                <option value="last_week" @if(request()->get('filter') == 'last_week') selected @endif>Last Week</option>
                                <option value="this_month" @if(request()->get('filter') == 'this_month') selected @endif>This Month</option>
                                <option value="last_month" @if(request()->get('filter') == 'last_month') selected @endif>Last Month</option>
                                <option value="this_year" @if(request()->get('filter') == 'this_year') selected @endif>This Year</option>
                            </select>
                        </div>
                    </div>
                    <!--col-->
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="form-group mb-0">
                           <label for="start_date" class="form-label"><i class="fas fa-calendar-alt"></i> Start Date</label>
                            <input type="date" value="{{request()->get('start_date')}}"  class="form-control flatdate" name="start_date">
                        </div>
                    </div>
                    <!--col--> 
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="form-group mb-0">
                           <label for="end_date" class="form-label"><i class="fas fa-calendar-alt"></i> End Date</label>
                            <input type="date" value="{{request()->get('end_date')}}" class="form-control flatdate" name="end_date">
                        </div>
                    </div>
                    <!--col-->
                    <div class="col-sm-12 text-end mt-2 mb-3">
                        <div class="form-group mb-0">
                            <button class="btn btn-primary modern-filter-btn rounded-pill px-4 shadow-sm me-2"><i class="fas fa-search me-1"></i> Filter Report</button>
                            <a href="{{route('admin.order_report')}}" class="btn btn-outline-danger rounded-pill px-4"><i class="fas fa-undo me-1"></i> Reset</a>
                        </div>
                    </div>
                </div>  
            </form>
        </div>
                <div class="row mb-3">
                    <div class="col-sm-6 no-print">
                         {{$orders->links('pagination::bootstrap-4')}}
                    </div>
                    <div class="col-sm-6">
                        <div class="export-print text-end">
                            <button onclick="printFunction()"class="no-print btn btn-success"><i class="fa fa-print"></i> Print</button>
                            <button id="export-excel-button" class="no-print btn btn-info"><i class="fas fa-file-export"></i> Export</button>
                        </div>
                    </div>
                </div>
                <div id="content-to-export">
                    <div class="table-responsive">
                        <table class="table nowrap w-100">
                        <thead>
                            <tr>
                                <th style="width:5%">Invoice</th>
                                <th style="width:20%">Customer</th>
                                <th style="width:20%">Phone</th>
                                <th style="width:30%">Product</th>
                                <th style="width:10%">Purchase</th>
                                <th style="width:10%">Sale</th>
                                <th style="width:10%">Qty</th>
                                <th style="width:10%">Total</th>
                            </tr>
                        </thead>               
                    
                        <tbody>
                            @php
                                $total_purchase = 0;
                                $total_qty = 0;
                                $total_sale = 0;
                            @endphp
                            @foreach($orders as $key=>$value)
                            
                            <tr>
                                <td>{{$value->order?$value->order->invoice_id:''}}</td>
                                <td>{{$value->shipping?$value->shipping->name:''}}</td>
                                <td>{{$value->shipping?$value->shipping->phone:''}}</td>
                                <td>{{$value->product_name}}</td>
                                <td>{{$value->purchase_price}}</td>
                                <td>{{$value->sale_price}}</td>
                                <td>{{$value->qty}}</td>
                                <td>{{$value->qty*$value->sale_price}}</td>
                            </tr>
                            @php
                                $total_purchase += $value->qty*$value->purchase_price;
                                $total_qty += $value->qty;
                                $total_sale += $value->qty * $value->sale_price;
                            @endphp
                            @endforeach
                         </tbody>
                         <tfoot>
                             <tr>
                                 <td colspan="5" class="text-end"><strong>Total</strong></td>
                                 <td><strong>{{$total_purchase}}</strong></td>
                                 <td><strong>{{$total_qty}}</strong></td>
                                 <td><strong>{{$total_sale}}</strong></td>
                             </tr>
                             <tr>
                                 <td colspan="8" class="text-center">
                                     <h5><strong>Total Purchase = {{$total_purchase}}</strong></h5>
                                     <h5><strong>Total Sales = {{$total_sales}}</strong></h5>
                                     <h5><strong>Total Profit = {{$total_sales-$total_purchase}}</strong></h5>
                                 </td>
                             </tr>
                         </tfoot>
                        </table>
                    </div>

                </div>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
   </div>
</div>
@endsection
@section('script')
<script src="{{asset('public/backEnd/')}}/assets/libs/select2/js/select2.min.js"></script>
<script src="{{asset('public/backEnd/')}}/assets/js/pages/form-advanced.init.js"></script>
<script src="{{asset('public/backEnd/')}}/assets/libs/flatpickr/flatpickr.min.js"></script>
<script src="https://cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $('.select2').select2();
        flatpickr(".flatdate", {});

        $('#filter-select').on('change', function() {
            if ($(this).val()) {
                $('input[name="start_date"]').val('');
                $('input[name="end_date"]').val('');
                $(this).closest('form').submit();
            }
        });

        $('.flatdate').on('change', function() {
            if ($(this).val()) {
                $('#filter-select').val('').trigger('change.select2');
            }
        });
    });
</script>
<script>
    function printFunction() {
        window.print();
    }
</script>
<script>
    $(document).ready(function() {
        $('#export-excel-button').on('click', function() {
            var contentToExport = $('#content-to-export').html();
            var tempElement = $('<div>');
            tempElement.html(contentToExport);
            tempElement.find('.table').table2excel({
                exclude: ".no-export",
                name: "Order Report" 
            });
        });
    });
</script>

@endsection
