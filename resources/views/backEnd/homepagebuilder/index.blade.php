@extends('backEnd.layouts.master')
@section('title','Homepage Builder')
@section('css')
<style>
    .cursor-move {
        cursor: move;
    }
</style>
@endsection
@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{route('homepagebuilder.create')}}" class="btn btn-primary rounded-pill"><i class="fe-plus"></i> Add New Section</a>
                </div>
                <h4 class="page-title">Homepage Section Manage (Drag to Reorder)</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 
   <div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Title</th>
                                <th>Heading</th>
                                <th>Type</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>               
                    
                        <tbody id="sortable">
                            @foreach($sections as $key=>$value)
                            <tr data-id="{{$value->id}}" class="cursor-move">
                                <td>{{$loop->iteration}}</td>
                                <td>{{$value->title}}</td>
                                <td>{{$value->heading ?? 'N/A'}}</td>
                                <td>{{ucfirst(str_replace('_', ' ', $value->section_key))}}</td>
                                <td>{{$value->sort_order}}</td>
                                <td>
                                    @if($value->status==1)
                                    <span class="badge bg-soft-success text-success">Active</span> 
                                    @else 
                                    <span class="badge bg-soft-danger text-danger">Inactive</span> 
                                    @endif
                                </td>
                                <td>
                                    <div class="button-list">
                                        <a href="{{route('homepagebuilder.edit',$value->id)}}" class="btn btn-xs btn-primary waves-effect waves-light"><i class="fe-edit-1"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
   </div>
</div>
@endsection


@section('script')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script>
    $( function() {
      $( "#sortable" ).sortable({
        update: function(event, ui) {
            var order = [];
            $('#sortable tr').each(function(index, element) {
                order.push({
                    id: $(this).data('id'),
                    position: index + 1
                });
            });

            $.ajax({
                type: "POST",
                dataType: "json",
                url: "{{ route('homepagebuilder.reorder') }}",
                data: {
                    order: order,
                    _token: '{{csrf_token()}}'
                },
                success: function(response) {
                    toastr.success('Order updated successfully');
                }
            });
        }
      });
    } );
</script>
@endsection
