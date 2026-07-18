@extends('backEnd.layouts.master')
@section('title', 'Order Invoice')
@section('content')
    <style>
        .customer-invoice {
            margin: 25px 0;
        }

        .invoice_btn {
            margin-bottom: 15px;
        }

        p {
            margin: 0;
        }

        td {
            font-size: 16px;
        }

        @page {
            margin: 0px;
        }

        @media print {
            .invoice-innter {
                margin-left: -120px !important;
            }

            .invoice_btn {
                margin-bottom: 0 !important;
            }

            td {
                font-size: 18px;
            }

            p {
                margin: 0;
            }

            header,
            footer,
            .no-print,
            .left-side-menu,
            .navbar-custom {
                display: none !important;
            }
        }
    </style>
    <section class="customer-invoice ">
        <div class="container">
            <div class="row">
                <div class="col-sm-6">
                    <a href="javascript:history.back()" class="no-print"><strong><i class="fe-arrow-left"></i> Back To
                            Order</strong></a>
                </div>
                <div class="col-sm-6">
                    <button onclick="printFunction()"class="no-print btn btn-xs btn-success waves-effect waves-light"><i
                            class="fa fa-print"></i></button>
                </div> 
                <div class="col-sm-12 mt-3">
                    <div class="invoice-innter"
                        style="width:760px;margin: 0 auto;background: #fff;overflow: hidden;padding: 30px;padding-top: 0;">
                        <table style="width:100%">
                            <tr>
                                <td style="width: 40%; float: left; padding-top: 15px;">
                                    <img src="{{ asset($generalsetting->dark_logo) }}"
                                        style="margin-top:25px !important;width:160px" alt="">
                                    <p style="font-size: 14px; margin-top:15px; color: #222; margin-bottom: 5px;"><strong>Payment Method:</strong>
                                        @if ($order->payments && $order->payments->count() > 1)
                                            <span style="text-transform: uppercase;">Multiple</span>
                                            <div style="font-size: 13px; color: #444; margin-top: 5px; line-height: 1.4;">
                                                @foreach($order->payments as $pm)
                                                    <div style="padding-left: 10px;">• {{ $pm->payment_method }}: ৳{{ $pm->amount }}</div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span style="text-transform: uppercase;">{{ $order->payment ? $order->payment->payment_method : '' }}</span>
                                            @if ($order->payment && $order->payment->payment_method === 'Cash' && $order->payment->received_amount > $order->amount)
                                                <div style="font-size: 13px; color: #555; margin-top: 3px;">
                                                    <div style="padding-left: 10px;">• Received: ৳{{ $order->payment->received_amount }}</div>
                                                    <div style="padding-left: 10px;">• Change Return: ৳{{ $order->payment->change_amount }}</div>
                                                </div>
                                            @endif
                                        @endif
                                    </p>
                                    @if ($order->payment && $order->payment->sender_number)
                                        <p style="margin: 0;"> Sender Number : {{ $order->payment->sender_number }}</p>
                                    @endif
                                    @if ($order->payment && $order->payment->trx_id)
                                        <p style="margin: 0;"> Trx ID : {{ $order->payment->trx_id }}</p>
                                    @endif
                                    @if ($order->payment && isset($order->payment->card_number) && $order->payment->card_number)
                                        <p style="margin: 0;"> Card Number : {{ $order->payment->card_number }}</p>
                                    @endif
                                    <div class="invoice_form mt-3">
                                        <p style="font-size:16px;line-height:1.8;color:#222"><strong>Invoice From:</strong>
                                        </p>
                                        <p style="font-size:16px;line-height:1.8;color:#222">{{ $generalsetting->name }}</p>
                                        <p style="font-size:16px;line-height:1.8;color:#222">{{ $contact->phone }}</p>
                                        <p style="font-size:16px;line-height:1.8;color:#222">{{ $contact->email }}</p>
                                        <p style="font-size:16px;line-height:1.8;color:#222">{{ $contact->address }}</p>
                                    </div>
                                </td>
                                <td style="width:60%;float: left;">
                                    <div class="invoice-bar"
                                        style=" background: #4DBC60; transform: skew(38deg); width: 100%; margin-left: 65px; padding: 20px 60px; ">
                                        <p
                                            style="font-size: 30px; color: #fff; transform: skew(-38deg); text-transform: uppercase; text-align: right; font-weight: bold;">
                                            Invoice</p>
                                    </div>
                                    <div class="invoice-bar"
                                        style="background: #fff; transform: skew(36deg); width: 72%; margin-left: 182px; padding: 12px 32px; margin-top: 6px;">
                                        <p
                                            style="font-size: 15px; color: #222;font-weight:bold; transform: skew(-36deg); text-align: right; padding-right: 18px">
                                            Invoice ID : <strong>#{{ $order->invoice_id }}</strong></p>
                                        <p
                                            style="font-size: 15px; color: #222;font-weight:bold; transform: skew(-36deg); text-align: right; padding-right: 32px">
                                            Invoice Date: <strong>{{ $order->created_at->format('d-m-y') }}</strong></span>
                                        </p>
                                    </div>
                                    <div class="invoice_to" style="padding-top: 20px;">
                                        <p style="font-size:16px;line-height:1.8;color:#222;text-align: right;">
                                            <strong>Invoice To:</strong></p>
                                        <p style="font-size:16px;line-height:1.8;color:#222;text-align: right;">
                                            {{ $order->shipping ? $order->shipping->name : '' }}</p>
                                        <p style="font-size:16px;line-height:1.8;color:#222;text-align: right;">
                                            {{ $order->shipping ? $order->shipping->phone : '' }}</p>
                                        <p style="font-size:16px;line-height:1.8;color:#222;text-align: right;">
                                            {{ $order->shipping ? $order->shipping->address : '' }}</p>
                                        <p style="font-size:16px;line-height:1.8;color:#222;text-align: right;">
                                            {{ $order->shipping ? $order->shipping->area : '' }}</p>
                                        @if ($order->note)
                                            <p style="font-size:16px;line-height:1.8;color:#222;text-align: right;">Note :
                                                {{ $order->note }}</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <table class="table" style="margin-top: 30px;margin-bottom: 0;">
                            <thead style="background: #4DBC60; color: #fff;">
                                <tr>
                                    <th>SL</th>
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->orderdetails as $key => $value)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            @if($value->image)
                                                <img src="{{ asset($value->image) }}" alt="{{ $value->product_name }}" style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                                            @else
                                                <span style="color:#aaa;font-size:12px;">No image</span>
                                            @endif
                                        </td>
                                        <td>{{ $value->product_name }} <br>
                                            @if ($value->product_size)
                                                <small>Size: {{ $value->product_size }}</small>
                                                @endif @if ($value->sku)
                                                    <small>SKU: {{ $value->sku }}</small>
                                                @endif
                                        </td>
                                        <td>৳{{ $value->sale_price }}</td>
                                        <td>{{ $value->qty }}</td>
                                        <td>৳{{ $value->sale_price * $value->qty }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="invoice-bottom">

                            <table class="table" style="width: 300px; float: right;    margin-bottom: 30px;">
                                <tbody style="background:#f1f9f8">
                                    <tr>
                                        <td><strong>SubTotal</strong></td>
                                        <td><strong>৳{{ $order->amount + $order->discount - $order->shipping_charge }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Shipping(+)</strong></td>
                                        <td><strong>৳{{ $order->shipping_charge }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Discount(-)</strong></td>
                                        <td><strong>৳{{ $order->discount }}</strong></td>
                                    </tr>
                                    <tr style="background:#4DBC60;color:#fff">
                                        <td><strong>Final Total</strong></td>
                                        <td><strong>৳{{ $order->amount }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="terms-condition"
                                style="overflow: hidden; width: 100%; text-align: center; padding: 20px 0; border-top: 1px solid #ddd;">
                                <h5 style="font-style: italic;"><a
                                        href="{{ route('page', ['slug' => 'terms-condition']) }}">Terms & Conditions</a></h5>
                                <p style="text-align: center; font-style: italic; font-size: 15px; margin-top: 10px;">* This
                                    is a computer generated invoice, does not require any signature.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        function printFunction() {
            window.print();
        }
    </script>
@endsection
