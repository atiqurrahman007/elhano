@extends('backEnd.layouts.master')
@section('title','Product Sort')
@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
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
                    <div class="col-sm-4 mb-3">
                        <form class="custom_form">
                            <div class="form-group d-flex">
                                <select class="form-control" name="category_id" placeholder="Search">
                                    <option value=""> Select</option>
                                    @foreach($categories as $key=>$category)
                                    <option value="{{$category->id}}">{{$category->name}}</option>
                                    @endforeach
                                </select>
                                <button class="btn  rounded-pill btn-info">Search</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table nowrap w-100" id="sortable">
                        <thead>
                            <tr>
                                <th style="width:2%">SL</th>
                                <th style="width:20%">Name</th>
                                <th style="width:10%">Category</th>
                                <th style="width:10%">Image</th>
                                <th style="width:10%">Price</th>
                                <th style="width:8%">Stock</th>
                                <th style="width:8%">Sort</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-list">
                            @foreach($data as $key => $value)
                            <tr data-id="{{$value->id}}" id="product-{{$value->id}}">
                                <td>{{$loop->iteration}}</td>
                                <td>{{$value->name}}</td>
                                <td>{{$value->category ? $value->category->name : ''}}</td>
                                <td><img src="{{asset($value->image ? $value->image->image : '')}}" class="backend-image" alt=""></td>
                                <td>{{$value->variable ? $value->variable->new_price : $value->new_price}}</td>
                                <td>{{$value->type == 0 ? $value->variables_sum_stock : $value->stock}}</td>
                                <td>{{$value->sort}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <!-- Hidden form to store the new sort order -->
                    <form action="{{ route('update-product-sort') }}" method="POST">
                        @csrf
                        <input type="hidden" id="sort-order-input" name="order">
                        <button type="submit" class="btn btn-primary">Save Order</button>
                    </form>



                </div>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
   </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

<script>
    // Make the list sortable using Sortable.js
    // var sortable = new Sortable(document.getElementById('sortable-list'), {
    //     animation: 150,
    //         onEnd: function(evt) {
    //             console.log("New order:", sortable.toArray());
    //             var order = sortable.toArray();

    //             fetch('{{ route('update-product-sort') }}', {
    //                     method: 'POST',
    //                     headers: {
    //                         'Content-Type': 'application/json',
    //                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
    //                     },
    //                     body: JSON.stringify({
    //                         order: order
    //                     })
    //                 })
    //                 .then(response => response.json())
    //                 .then(data => {
    //                     if (data.success) {
    //                         window.location.reload();
    //                         toastr.success('Banner Order Saved Successfully!!');
    //                     } else {
    //                         alert('Failed to save order.');
    //                     }
    //                 });
    //         }
    // });
    var sortable = new Sortable(document.getElementById('sortable-list'), {
        animation: 150,
        onSort: function() {
            var order = sortable.toArray();
            document.getElementById('sort-order-input').value = JSON.stringify(order);
        }
    });

</script>


@endsection