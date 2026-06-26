@extends('backEnd.layouts.master')
@section('title','Product Manage')
@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{route('products.create')}}" class="btn btn-danger rounded-pill"><i class="fe-shopping-cart"></i> Add Product</a>
                </div>
                <h4 class="page-title">Product Manage</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 
   <div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-8">
                        <ul class="action2-btn">
                            <li><a href="{{route('products.update_deals',['status'=>1])}}" class="btn rounded-pill btn-success hotdeal_update"><i class="fe-thumbs-up"></i> Deal</a></li>
                            <li><a href="{{route('products.update_deals',['status'=>0])}}" class="btn  rounded-pill btn-danger hotdeal_update"><i class="fe-thumbs-down"></i> Deal</a></li>
                            
                            <li><a href="{{route('products.update_status',['status'=>1])}}" class="btn rounded-pill btn-primary update_status"><i class="fe-thumbs-up"></i> Active</a></li>
                            <li><a href="{{route('products.update_status',['status'=>0])}}" class="btn  rounded-pill btn-warning update_status"><i class="fe-thumbs-down"></i> Inactive</a></li>
                        </ul>
                    </div>
                    <div class="col-sm-4">
                        <form class="custom_form">
                            <div class="form-group">
                                <input type="text" name="keyword" placeholder="Search">
                                <button class="btn  rounded-pill btn-info">Search</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table nowrap w-100">
                    <thead>
                        <tr>
                            <th style="width:2%"><div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input checkall" value=""></label>
                            <th style="width:2%">SL</th>
                                    </div></th>
                            <th style="width:10%">Action</th>
                            <th style="width:20%">Name</th>
                            <th style="width:10%">Category</th>
                            <th style="width:10%">Image</th>
                            <th style="width:10%">Price</th>
                            <th style="width:8%">Stock</th>
                            <th style="width:14%">Deal & Feature</th>
                            <th style="width:8%">Status</th>
                        </tr>
                    </thead>               
                
                    <tbody>
                        @foreach($data as $key=>$value)
                        <tr>
                            <td><input type="checkbox" class="checkbox" value="{{$value->id}}"></td>
                            <td>{{$loop->iteration}}</td>
                            <td>
                                <div class="button-list custom-btn-list">
                                    @if($value->status == 1)
                                    <form method="post" action="{{route('products.inactive')}}" class="d-inline">
                                    @csrf
                                    <input type="hidden" value="{{$value->id}}" name="hidden_id">
                                    <button type="button" class="change-confirm" title="Active"><i class="fe-thumbs-down"></i></button></form>
                                    @else
                                    <form method="post" action="{{route('products.active')}}" class="d-inline">
                                        @csrf
                                    <input type="hidden" value="{{$value->id}}" name="hidden_id">
                                    <button type="button" class="change-confirm" title="Inactive"><i class="fe-thumbs-up"></i></button></form>
                                    @endif

                                    <a href="{{route('products.edit',$value->id)}}" title="Edit"><i class="fe-edit"></i></a>

                                    <form method="post" action="{{route('products.destroy')}}" class="d-inline">
                                        @csrf
                                    <input type="hidden" value="{{$value->id}}" name="hidden_id">
                                    <button type="submit" class="delete-confirm" title="Delete"><i class="fe-trash-2"></i></button></form>

                                    <a href="{{route('products.purchase_history',$value->id)}}" title="Purchase History"><i class="fe-shopping-bag"></i></a>

                                    {{-- Barcode Print Button — immutable, always same barcode --}}
                                    @if($value->type == 1 && $value->pro_barcode)
                                        <a href="{{ route('products.barcode') }}?product_id={{ $value->id }}&type=1&copies=1"
                                           title="Barcode: {{ $value->pro_barcode }} — Click to print"
                                           style="color:#222">
                                            <i class="ri-barcode-line" style="font-size:17px;vertical-align:middle"></i>
                                        </a>
                                    @elseif($value->type == 0 && $value->firstVariable && $value->firstVariable->pro_barcode)
                                        <a href="{{ route('products.barcode') }}?product_id={{ $value->firstVariable->id }}&type=0&copies=1"
                                           title="Barcode: {{ $value->firstVariable->pro_barcode }} — Click to print (variants)"
                                           style="color:#222">
                                            <i class="ri-barcode-line" style="font-size:17px;vertical-align:middle"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                {{$value->name}}
                                {{-- Immutable barcode number shown under name for quick reference --}}
                                @if($value->type == 1 && $value->pro_barcode)
                                    <br><small style="font-family:monospace;font-size:10px;letter-spacing:1px;color:#888">{{ $value->pro_barcode }}</small>
                                @elseif($value->type == 0 && $value->firstVariable && $value->firstVariable->pro_barcode)
                                    <br><small style="font-family:monospace;font-size:10px;letter-spacing:1px;color:#888">{{ $value->firstVariable->pro_barcode }}<span style="color:#0dcaf0"> +variants</span></small>
                                @endif
                            </td>
                            <td>{{$value->category?$value->category->name:''}}</td>
                            <td><img src="{{asset($value->image?$value->image->image:'')}}" class="backend-image" alt=""></td>
                            <td>{{$value->variable?$value->variable->new_price: $value->new_price}}</td>
                            <td>{{ $value->type == 0 ? ($value->variables_sum_stock ?? 0) : $value->stock }}</td>
                            <td><p class="m-0">Hot Deals : {{$value->topsale==1?'Yes':'No'}}</p>
                                <p class="m-0">Top Feature : {{$value->feature_product==1?'Yes':'No'}}</p></td>
                            <td>@if($value->status==1)<span class="badge bg-soft-success text-success">Active</span> @else <span class="badge bg-soft-danger text-danger">Inactive</span> @endif</td>
                        </tr>
                        @endforeach
                     </tbody>
                    </table>
                </div>
                <div class="custom-paginate">
                    {{$data->links('pagination::bootstrap-4')}}
                </div>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
   </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $(".checkall").on('change',function(){
      $(".checkbox").prop('checked',$(this).is(":checked"));
    });
    
    $(document).on('click', '.hotdeal_update', function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        console.log('url',url);
        var product = $('input.checkbox:checked').map(function(){
          return $(this).val();
        });
        var product_ids=product.get();
        if(product_ids.length ==0){
            toastr.error('Please Select A Product First !');
            return ;
        }
        $.ajax({
           type:'GET',
           url:url,
           data:{product_ids},
           success:function(res){
               if(res.status=='success'){
                toastr.success(res.message);
                window.location.reload();
            }else{
                toastr.error('Failed something wrong');
            }
           }
        });
    });
    $(document).on('click', '.update_status', function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        var product = $('input.checkbox:checked').map(function(){
          return $(this).val();
        });
        var product_ids=product.get();
        if(product_ids.length ==0){
            toastr.error('Please Select A Product First !');
            return ;
        }
        $.ajax({
           type:'GET',
           url:url,
           data:{product_ids},
           success:function(res){
               if(res.status=='success'){
                toastr.success(res.message);
                window.location.reload();
            }else{
                toastr.error('Failed something wrong');
            }
           }
        });
    });
    $(document).on('click', '.update_status', function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        var product = $('input.checkbox:checked').map(function(){
          return $(this).val();
        });
        var product_ids=product.get();
        if(product_ids.length ==0){
            toastr.error('Please Select A Product First !');
            return ;
        }
        $.ajax({
           type:'GET',
           url:url,
           data:{product_ids},
           success:function(res){
               if(res.status=='success'){
                toastr.success(res.message);
                window.location.reload();
            }else{
                toastr.error('Failed something wrong');
            }
           }
        });
    });
    
    
})
</script>
@endsection