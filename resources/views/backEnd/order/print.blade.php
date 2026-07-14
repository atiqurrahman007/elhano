<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Receipt</title>
    <style>
        body {
            background: #fff;
            color: #000;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        
        .receipt-container {
            width: 80mm;
            max-width: 100%;
            margin: 0 auto;
            padding: 10px;
            box-sizing: border-box;
        }

        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        .bold {
            font-weight: bold;
        }

        .store-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }
        .store-tagline {
            font-size: 10px;
            margin-bottom: 5px;
        }
        .store-info {
            font-size: 11px;
            margin-bottom: 2px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .bill-title {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            font-size: 11px;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .info-table td {
            padding: 1px 0;
            vertical-align: top;
        }

        .items-table {
            width: 100%;
            font-size: 11px;
            border-collapse: collapse;
        }
        .items-table th {
            border-bottom: 1px dashed #000;
            text-align: left;
            padding: 3px 0;
            font-weight: bold;
        }
        .items-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .totals-table {
            width: 100%;
            font-size: 11px;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .totals-table td {
            padding: 2px 0;
        }

        .total-row {
            font-size: 13px;
            font-weight: bold;
        }

        .payment-section {
            font-size: 11px;
            margin-top: 5px;
            text-align: center;
        }

        .footer-section {
            font-size: 11px;
            text-align: center;
            margin-top: 10px;
        }

        .no-print-btn {
            background: #28a745;
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            margin: 10px auto;
            display: block;
            font-family: sans-serif;
            font-size: 14px;
        }

        @media print {
            .no-print-btn {
                display: none !important;
            }
            body {
                background: #fff;
            }
            .receipt-container {
                width: 80mm;
                padding: 0;
                margin: 0;
            }
            @page {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    @foreach($orders as $order)
    @php
        $cashier = \App\Models\User::find($order->user_id)->name ?? 'Admin';
        $subtotal = $order->amount + $order->discount - $order->shipping_charge;
    @endphp
    
    <button class="no-print-btn" onclick="window.print()">Print Receipt</button>

    <div class="receipt-container">
        <!-- Header -->
        <div class="text-center">
            <h1 class="store-name">{{ $generalsetting->name }}</h1>
            <div class="store-tagline">— E S S E N T I A L  W E A R —</div>
            <div class="store-info">{{ $contact->address }}</div>
            <div class="store-info">Phone: {{ $contact->phone }}</div>
            <div class="store-info">Email: {{ $contact->email }}</div>
        </div>

        <div class="divider"></div>

        <!-- POS Bill Details -->
        <div class="text-center bill-title">POS BILL</div>
        <table class="info-table">
            <tr>
                <td>Bill No: #{{ $order->invoice_id }}</td>
                <td class="text-right">Terminal: POS-01</td>
            </tr>
            <tr>
                <td>Date: {{ $order->created_at->format('d M Y') }}</td>
                <td class="text-right">Cashier: {{ $cashier }}</td>
            </tr>
            <tr>
                <td>Time: {{ $order->created_at->format('h:i A') }}</td>
                <td class="text-right"></td>
            </tr>
            @if($order->shipping && $order->shipping->name)
            <tr>
                <td colspan="2">Customer: {{ $order->shipping->name }} ({{ $order->shipping->phone }})</td>
            </tr>
            @endif
        </table>

        <div class="divider"></div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 8%">SL</th>
                    <th style="width: 42%">ITEM</th>
                    <th style="width: 15%" class="text-center">SIZE</th>
                    <th style="width: 10%" class="text-center">QTY</th>
                    <th style="width: 12%" class="text-right">PRICE</th>
                    <th style="width: 13%" class="text-right">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderdetails as $key => $value)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{ $value->product_name }}
                        @if($value->product_color)
                        <br><span style="font-size: 9px; color: #555;">{{ $value->product_color }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $value->product_size ?? '-' }}</td>
                    <td class="text-center">{{ $value->qty }}</td>
                    <td class="text-right">{{ number_format($value->sale_price, 2) }}</td>
                    <td class="text-right">{{ number_format($value->sale_price * $value->qty, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- Totals Table -->
        <table class="totals-table">
            <tr>
                <td>SUBTOTAL</td>
                <td class="text-right">{{ number_format($subtotal, 2) }}</td>
            </tr>
            @if($order->discount > 0)
            <tr>
                <td>DISCOUNT</td>
                <td class="text-right">-{{ number_format($order->discount, 2) }}</td>
            </tr>
            @endif
            @if($order->shipping_charge > 0)
            <tr>
                <td>SHIPPING CHARGE</td>
                <td class="text-right">{{ number_format($order->shipping_charge, 2) }}</td>
            </tr>
            @endif
            <tr class="divider">
                <td colspan="2" style="padding: 0;"></td>
            </tr>
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-right">{{ number_format($order->amount, 2) }}</td>
            </tr>
            <tr class="divider">
                <td colspan="2" style="padding: 0;"></td>
            </tr>
            <tr>
                <td>AMOUNT PAID</td>
                <td class="text-right">{{ number_format($order->payment ? $order->payment->received_amount : $order->amount, 2) }}</td>
            </tr>
            <tr>
                <td>CHANGE</td>
                <td class="text-right">{{ number_format($order->payment ? $order->payment->change_amount : 0, 2) }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <!-- Payment Method Details -->
        <div class="payment-section">
            <div class="bold">PAYMENT METHOD</div>
            @if ($order->payments && $order->payments->count() > 1)
                <div>Paid by: Multiple</div>
                @foreach($order->payments as $pm)
                    <div style="padding-left: 10px;">- {{ $pm->payment_method }}: ৳{{ number_format($pm->amount, 2) }}</div>
                @endforeach
            @else
                <div>Paid by {{ $order->payment ? $order->payment->payment_method : 'Cash' }}</div>
                @if($order->payment && $order->payment->trx_id)
                <div>TXN ID: {{ $order->payment->trx_id }}</div>
                @endif
                @if($order->payment && isset($order->payment->card_number) && $order->payment->card_number)
                <div>CARD: {{ $order->payment->card_number }}</div>
                @endif
            @endif
        </div>

        <div class="divider"></div>

        <!-- Footer -->
        <div class="footer-section">
            <div class="bold" style="text-transform: uppercase;">Thank you for shopping with us!</div>
            <div>Your style. Your choice.</div>
            <div style="margin-top: 5px; font-size: 9px; font-style: italic;">
                Exchange & Return within 7 days with original tag & invoice.
            </div>
        </div>
    </div>
    @endforeach

    <script>
        // Auto trigger print dialog on page load
        window.onload = function() {
            window.print();
        }
        window.onafterprint = function() {
            if (window.parent && typeof window.parent.restoreFullScreen === 'function') {
                window.parent.restoreFullScreen();
            }
        }
    </script>
</body>
</html>
