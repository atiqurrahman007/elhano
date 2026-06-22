@extends('backEnd.layouts.master')
@section('title', 'Facebook Catelogue')
@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">

                    <h4 class="page-title">Facebook Catelogue</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <p>Click to regenerate the Facebook product XML feed to keep your Facebook Shop catalogue updated with your latest products.</p>
                        <p>ফেসবুক ক্যাটালগ আপডেট করার জন্য নতুন করে প্রোডাক্ট XML ফাইল তৈরি করতে এখানে ক্লিক করুন।</p>
                        <a href="{{ route('products.product_feed') }}" class="btn btn-success" >
                            <i class="fas fa-file-download"></i> Update Product Feed
                        </a>



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
    <script src="{{ asset('public/backEnd/') }}/assets/js/switchery.min.js"></script>
    <script>
        $(document).ready(function() {
            var elem = document.querySelector('.js-switch');
            var init = new Switchery(elem);
        });
    </script>
@endsection
