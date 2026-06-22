@extends('frontEnd.layouts.master')
@section('title', 'Order Success')
@section('content')
    <section class="customer-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-sm-8">
                    <div class="success-img">
                        <img src="{{ asset('public/frontEnd/images/order-success.png') }}" alt="">
                    </div>
                    <div class="success-title">
                        <h2>Your order has reached us successfully, our representative will call your number shortly</h2>
                    </div>

                    <h5 class="my-3">Your Order Details</h5>
                    <div class="success-table">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td>
                                        <p>Invoice ID</p>
                                        <p><strong>{{ $order->invoice_id }}</strong></p>
                                    </td>
                                    <td>
                                        <p>Date</p>
                                        <p><strong>{{ $order->created_at->format('d-m-y') }}</strong></p>
                                    </td>
                                    <td>
                                        <p>Phone</p>
                                        <p><strong>{{ $order->shipping ? $order->shipping->phone : '' }}</strong></p>
                                    </td>
                                    <td>
                                        <p>Total</p>
                                        <p><strong>৳ {{ $order->amount }}</strong></p>
                                    </td>
                                </tr>
                                <tr>
                                    @php
                                        $payments = App\Models\Payment::where('order_id', $order->id)->first();
                                    @endphp
                                    <td colspan="4">
                                        <p>Payment Method</p>
                                        <p><strong>{{ $payments->payment_method }}</strong></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- success table -->
                    <h5 class="my-4">Pay with cash upon delivery</h5>
                    <div class="success-table">
                        <h6 class="mb-3">Order Delivery</h6>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->orderdetails as $key => $value)
                                    <tr>
                                        <td>
                                            <p>{{ $value->product_name }} x {{ $value->qty }}</p>
                                            @if($value->sku)
                                            <p><small>{{$value->sku}}</small></p>
                                            @endif

                                        </td>
                                        <td>
                                            <p><strong>৳ {{ $value->sale_price }}</strong></p>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <th class="text-end px-4">Net Total</th>
                                    <td><strong id="net_total">৳{{ $order->amount - $order->shipping_charge }}</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-end px-4">Shipping Cost</th>
                                    <td>
                                        <strong id="cart_shipping_cost">৳{{ $order->shipping_charge }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-end px-4">Grand Total</th>
                                    <td>
                                        <strong id="grand_total">৳{{ $order->amount }}</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td>
                                        <h5 class="my-4">Billing Address</h5>
                                        <p>{{ $order->shipping ? $order->shipping->name : '' }}</p>
                                        <p>{{ $order->shipping ? $order->shipping->phone : '' }}</p>
                                        <p>{{ $order->shipping ? $order->shipping->address : '' }}</p>
                                        <p>{{ $order->shipping ? $order->shipping->area : '' }}</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                     <div class="button-wrapper">
                </div>
                    <!-- success table -->
                    <a href="{{ route('home') }}" class=" my-5 btn btn-primary">Go To Home</a>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('script')
    <script src="{{ asset('public/frontEnd/') }}/js/parsley.min.js"></script>
    <script src="{{ asset('public/frontEnd/') }}/js/form-validation.init.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
      window.onload = () => {
          let canvas = document.createElement("canvas");
          let container = document.getElementsByClassName("button-wrapper")[0];
          canvas.width = 0;
          canvas.height = 0;

          container.appendChild(canvas);

          let confetti_button = confetti.create(canvas);
          
          confetti_button().then(() => {
              container.removeChild(canvas);
          });

          var count = 1000;
          var defaults = {
              origin: { y: 0.7 }
          };

          function fire(particleRatio, opts) {
              confetti({
                  ...defaults,
                  ...opts,
                  particleCount: Math.floor(count * particleRatio)
              });
          }

           fire(0.25, {
              spread: 26,
              startVelocity: 55,
              decay: 0.9, 
          });
          fire(0.2, {
              spread: 60,
              decay: 0.88, 
          });
          fire(0.35, {
              spread: 100,
              decay: 0.89, 
              scalar: 0.8
          });
          fire(0.1, {
              spread: 120,
              startVelocity: 25,
              decay: 0.9, 
              scalar: 1.2
          });
          fire(0.1, {
              spread: 120,
              startVelocity: 45,
              decay: 0.9, 
          });

          // Delay before the second round of confetti
          setTimeout(() => {
              // Create a new canvas and fire confetti again
              let canvas2 = document.createElement("canvas");
              container.appendChild(canvas2);

              let confetti_button2 = confetti.create(canvas2);
              confetti_button2().then(() => {
                  container.removeChild(canvas2);
              });

         fire(0.25, {
              spread: 26,
              startVelocity: 55,
              decay: 0.9, 
          });
          fire(0.2, {
              spread: 60,
              decay: 0.88, 
          });
          fire(0.35, {
              spread: 100,
              decay: 0.89, 
              scalar: 0.8
          });
          fire(0.1, {
              spread: 120,
              startVelocity: 25,
              decay: 0.9, 
              scalar: 1.2
          });
          fire(0.1, {
              spread: 120,
              startVelocity: 45,
              decay: 0.9, 
          });

            }, 500); 
 
      };
    </script>
    @if (Session::get('purchase_event'))
        <script type="text/javascript">
             window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                ecommerce: null
            });
            dataLayer.push({
                event: "purchase",
                ecommerce: {
                    transaction_id: "{{ $order->invoice_id }}",
                    value: {{ $order->amount }},
                    tax: 0,
                    shipping: {{ $order->shipping_charge }},
                    currency: "BDT",
                    coupon: "ecommerce",
                    items: [
                        @foreach ($order->orderdetails as $key => $value)
                            {
                                item_id: "{{ $value->product_id }}",
                                item_name: "{{ $value->product_name }}",
                                coupon: "ecommerce",
                                discount: {{ $value->product_discount }},
                                index: {{ $value->id }},
                                item_brand: "ecommerce",
                                item_category: "@if ($value->product) {{ $value->product->category ? $value->product->category->name : '' }} @endif",
                                item_variant: "{{ $value->product_size }}",
                                price: {{ $value->sale_price }},
                                quantity: {{ $value->qty }}
                            },
                        @endforeach
                    ]
                },
                 customer:{
                    first_name:"{{ $order->shipping ? $order->shipping->name : '' }}",
                    phone:"{{ $order->shipping ? $order->shipping->phone : '' }}",
                    email:"{{ $order->shipping ? $order->shipping->email : '' }}",
                    address:"{{ $order->shipping ? $order->shipping->address : '' }}",
                    }
            });
        </script>
        {{ Session::forget('purchase_event') }}
    @endif
@endpush
