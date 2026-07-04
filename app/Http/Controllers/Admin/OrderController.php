<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\Customer;
use App\Models\OrderStatus;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Shipping;
use App\Models\ShippingCharge;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Courierapi;
use App\Models\Expense;
use App\Models\ExpenseCategories;
use App\Models\ProductVariable;
use App\Models\PurchaseDetails;
use App\Models\District;

class OrderController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:order-list|order-create|order-edit|order-delete', ['only' => ['index', 'order_store', 'order_edit']]);
        $this->middleware('permission:order-create', ['only' => ['order_store', 'order_create']]);
        $this->middleware('permission:order-edit', ['only' => ['order_edit', 'order_update']]);
        $this->middleware('permission:order-delete', ['only' => ['destroy']]);
        $this->middleware('permission:order-invoice', ['only' => ['invoice']]);
        $this->middleware('permission:order-process', ['only' => ['process', 'order_process']]);
        $this->middleware('permission:order-process', ['only' => ['process']]);
    }
    public function search(Request $request)
    {
        $qty = 1;
        $keyword = $request->keyword;

        $product = null;
        $var_product = null;
        if ($keyword) {
            $product = Product::where('pro_barcode', $keyword)->with('image')->first();
            $var_product = !$product ? ProductVariable::where('pro_barcode', $keyword)->with('product.image')->first() : null;
        }

        if ($product || $var_product) {
            $product_data = $product ?: $var_product->product;
            $purchase_price = $product ? $product->purchase_price : $var_product->purchase_price;
            $old_price = $product ? $product->old_price : $var_product->old_price;
            $new_price = $product ? $product->new_price : $var_product->new_price;
            $stock = $product ? $product->stock : $var_product->stock;
            $product_id = $product ? $product->id : $var_product->product_id;
            $product_name = $product ? $product->name : ($var_product->product ? $var_product->product->name : '');
            $product_slug = $product ? $product->slug : ($var_product->product ? $var_product->product->slug : '');
            $product_image = $product 
                ? ($product->image ? $product->image->image : null) 
                : (($var_product->product && $var_product->product->image) ? $var_product->product->image->image : null);
            $product_type = $product ? $product->type : ($var_product->product ? $var_product->product->type : 0);
            $product_size = $var_product->size ?? null;
            $product_color = $var_product->color ?? null;

            $cartitem = Cart::instance('sale')->content()->where('id', $product_id)->first();
            $cart_qty = $cartitem ? $cartitem->qty + $qty : $qty;

            if ($stock < $cart_qty) {
                Toastr::error('Product stock limit over', 'Failed!');
                return response()->json(['status' => 'limitover', 'message' => 'Your stock limit is over']);
            }

            $cartinfo = Cart::instance('sale')->add([
                'id' => $product_id,
                'name' => $product_name,
                'qty' => $qty,
                'price' => $new_price,
                'weight' => 1,
                'options' => [
                    'slug' => $product_slug,
                    'image' => $product_image,
                    'old_price' => $old_price,
                    'purchase_price' => $purchase_price,
                    'product_size' => $product_size,
                    'product_color' => $product_color,
                    'type' => $product_type
                ],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Your Product Added'
            ]);
        } else {
            // If not found by exact barcode, search for products by name or barcode
            $products = Product::select('id', 'name', 'slug', 'new_price', 'old_price', 'type', 'stock')
                ->with('image', 'variables');

            if ($keyword) {
                $products = $products->where('name', 'LIKE', '%' . $keyword . "%")
                    ->orWhere('pro_barcode', 'LIKE', '%' . $keyword . "%");
            }

            $products = $products->get();
            if (empty($request->keyword)) {
                $products = [];
            }
            return view('backEnd.order.search', compact('products'));
        }
    }


    public function product_variable_search(Request $request)
    {
        $product = Product::with(['variables', 'image'])->find($request->id);
        
        if ($product && $product->type == 0) {
            return response()->json([
                'status' => 'success',
                'product_image' => ($product->image && $product->image->image) ? asset($product->image->image) : asset('public/images/no-image.png'),
                'data' => $product->variables
            ]);
        }
        return response()->json(['status' => 'failed', 'message' => 'Not a variable product']);
    }

    public function index($slug, Request $request)
    {
        if ($slug == 'all') {
            $order_status = (object) [
                'name' => 'All',
                'orders_count' => Order::count(),
            ];
            $show_data = Order::latest()->with('shipping', 'status');
            if ($request->keyword) {
                $show_data = $show_data->where(function ($query) use ($request) {
                    $query->orWhere('invoice_id', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhereHas('shipping', function ($subQuery) use ($request) {
                            $subQuery->where('phone', $request->keyword);
                        });
                });
            }
            $show_data = $show_data->paginate(50);
        } else {
            $order_status = OrderStatus::where('slug', $slug)->withCount('orders')->first();
            $show_data = Order::where(['order_status' => $order_status->id])->latest()->with('shipping', 'status')->paginate(50);
        }
        $users = User::get();
        $steadfast = Courierapi::where(['status' => 1, 'type' => 'steadfast'])->first();
        $pathao_info = Courierapi::where(['status' => 1, 'type' => 'pathao'])->select('id', 'type', 'url', 'token', 'status')->first();
        // pathao courier
        if ($pathao_info) {
            $response = Http::get($pathao_info->url . '/api/v1/countries/1/city-list');
            $pathaocities = $response->json();
            $response2 = Http::withHeaders([
                'Authorization' => 'Bearer ' . $pathao_info->token,
                'Content-Type' => 'application/json',
            ])->get($pathao_info->url . '/api/v1/stores');
            $pathaostore = $response2->json();
        } else {
            $pathaocities = [];
            $pathaostore = [];
        }
        return view('backEnd.order.index', compact('show_data', 'order_status', 'users', 'steadfast', 'pathaostore', 'pathaocities'));
    }

    public function extraorders($slug, Request $request)
    {
        if ($slug == 'all') {
            $order_status = (object) [
                'name' => 'All',
                'orders_count' => Order::where('order_stype', '>', 0)->count(),
            ];

            $show_data = Order::where('order_stype', '>', 0)
                ->latest()
                ->with('shipping', 'status');

            // Filter by keyword if provided
            if ($request->keyword) {
                $show_data = $show_data->where(function ($query) use ($request) {
                    $query->orWhere('invoice_id', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhereHas('shipping', function ($subQuery) use ($request) {
                            $subQuery->where('phone', $request->keyword);
                        });
                });
            }
            $show_data = $show_data->paginate(50);
        } else {
            $order_status = OrderStatus::where('slug', $slug)->withCount('orders')->first();
            $show_data = Order::where(['order_status' => $order_status->id])
                ->where('order_stype', '>', 0)
                ->latest()
                ->with('shipping', 'status')
                ->paginate(50);
        }

        $users = User::get();
        $steadfast = Courierapi::where(['status' => 1, 'type' => 'steadfast'])->first();
        $pathao_info = Courierapi::where(['status' => 1, 'type' => 'pathao'])->select('id', 'type', 'url', 'token', 'status')->first();

        // Pathao courier handling
        if ($pathao_info) {
            $response = Http::get($pathao_info->url . '/api/v1/countries/1/city-list');
            $pathaocities = $response->json();
            $response2 = Http::withHeaders([
                'Authorization' => 'Bearer ' . $pathao_info->token,
                'Content-Type' => 'application/json',
            ])->get($pathao_info->url . '/api/v1/stores');
            $pathaostore = $response2->json();
        } else {
            $pathaocities = [];
            $pathaostore = [];
        }

        return view('backEnd.extraorder.index', compact('show_data', 'order_status', 'users', 'steadfast', 'pathaostore', 'pathaocities'));
    }

    public function pathaocity(Request $request)
    {
        $pathao_info = Courierapi::where(['status' => 1, 'type' => 'pathao'])->select('id', 'type', 'url', 'token', 'status')->first();
        if ($pathao_info) {
            $response = Http::get($pathao_info->url . '/api/v1/cities/' . $request->city_id . '/zone-list');
            $pathaozones = $response->json();
            return response()->json($pathaozones);
        } else {
            return response()->json([]);
        }
    }
    public function pathaozone(Request $request)
    {
        $pathao_info = Courierapi::where(['status' => 1, 'type' => 'pathao'])->select('id', 'type', 'url', 'token', 'status')->first();
        if ($pathao_info) {
            $response = Http::get($pathao_info->url . '/api/v1/zones/' . $request->zone_id . '/area-list');
            $pathaoareas = $response->json();
            return response()->json($pathaoareas);
        } else {
            return response()->json([]);
        }
    }

    public function order_pathao(Request $request)
    {

        $order = Order::with('shipping')->find($request->id);
        $order_count = OrderDetails::select('order_id')->where('order_id', $order->id)->count();
        // pathao
        $pathao_info = Courierapi::where(['status' => 1, 'type' => 'pathao'])->select('id', 'type', 'url', 'token', 'status')->first();
        if ($pathao_info) {
            $response = Httpin::withHeaders([
                'Authorization' => 'Bearer ' . $pathao_info->token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($pathao_info->url . '/api/v1/orders', [
                'store_id' => $request->pathaostore,
                'merchant_order_id' => $order->invoice_id,
                'sender_name' => 'Test',
                'sender_phone' => $order->shipping ? $order->shipping->phone : '',
                'recipient_name' => $order->shipping ? $order->shipping->name : '',
                'recipient_phone' => $order->shipping ? $order->shipping->phone : '',
                'recipient_address' => $order->shipping ? $order->shipping->address : '',
                'recipient_city' => $request->pathaocity,
                'recipient_zone' => $request->pathaozone,
                'recipient_area' => $request->pathaoarea,
                'delivery_type' => 48,
                'item_type' => 2,
                'special_instruction' => 'Special note- product must be check after delivery',
                'item_quantity' => $order_count,
                'item_weight' => 0.5,
                'amount_to_collect' => round($order->amount),
                'item_description' => 'Special note- product must be check after delivery',
            ]);
        }
        if ($response->status() == '200') {
            $order->order_status = 5;
            $order->save();
            Toastr::success('order send to pathao successfully');
            return redirect()->back();
        } else {
            Toastr::error($response['message'], 'Courier Order Faild');
            return redirect()->back();
        }
    }

    public function invoice($invoice_id)
    {
        $order = Order::where(['id' => $invoice_id])->with('orderdetails', 'payment', 'shipping', 'customer')->firstOrFail();
        return view('backEnd.order.invoice', compact('order'));
    }

    public function process($invoice_id)
    {
        $data = Order::where(['id' => $invoice_id])->select('id', 'invoice_id', 'order_status', 'order_type')->with('orderdetails', 'shipping')->first();
        $shippingcharge = Shippingcharge::get();
        return view('backEnd.order.process', compact('data', 'shippingcharge'));
    }

    public function order_process(Request $request)
    {

        $link = OrderStatus::find($request->status)->slug;
        $order = Order::with('payment')->find($request->id);
        $order_status = $order->order_status;
        $order->order_status = $request->status;
        $order->admin_note = $request->admin_note;
        $order->save();

        $shipping_update = Shipping::where('order_id', $order->id)->first();
        //return $shipping_update;
        $shipping_update->name = $request->name;
        $shipping_update->phone = $request->phone;
        $shipping_update->address = $request->address;
        // $shipping_update->district = $request->district;
        $shipping_update->area = $request->area;
        //return $shipping_update;
        $shipping_update->save();

        // if ($request->status == 5 && $order_status != 5) {
        //     $courier_info = Courierapi::where(['status' => 1, 'type' => 'steadfast'])->first();
        //     if ($courier_info) {
        //         $consignmentData = [
        //             'invoice' => $order->invoice_id,
        //             'recipient_name' => $order->shipping ? $order->shipping->name : 'InboxHat',
        //             'recipient_phone' => $order->shipping ? $order->shipping->phone : '01750578495',
        //             'recipient_address' => $order->shipping ? $order->shipping->address : '01750578495',
        //             'cod_amount' => $order->amount
        //         ];
        //         $client = new Client();
        //         $response = $client->post('$courier_info->url', [
        //             'json' => $consignmentData,
        //             'headers' => [
        //                 'Api-Key' => '$courier_info->api_key',
        //                 'Secret-Key' => '$courier_info->secret_key',
        //                 'Accept' => 'application/json',
        //             ],
        //         ]);

        //         $responseData = json_decode($response->getBody(), true);
        //     }
        // }
        \App\Service\InventoryService::adjustStockForStatusChange($order, $order_status, $request->status);


        Toastr::success('Success', 'Order status change successfully');
        return redirect('admin/order/' . $link);
    }

    public function order_return(Request $request)
    {
        $order_detail = OrderDetails::select('id', 'order_id', 'product_id', 'sale_price', 'qty', 'is_return')->where('id', $request->id)->first();
        $order_detail->is_return = 1;
        $order_detail->save();

        $order = Order::find($order_detail->order_id);
        if ($order->order_status == 5) {
            $order->amount -= $order_detail->sale_price;
            $order->save();
        }

        if (\App\Service\InventoryService::isDeductedStatus($order->order_status)) {
            \App\Service\InventoryService::returnItemStock($order_detail);
        }

        Toastr::success('Success', 'Order item return successfully');
        return back();
    }

    public function destroy(Request $request)
    {
        $order = Order::find($request->id);
        if ($order) {
            $order->delete();
        }
        $order_details = OrderDetails::where('order_id', $request->id)->delete();
        $shipping = Shipping::where('order_id', $request->id)->delete();
        $payment = Payment::where('order_id', $request->id)->delete();
        Toastr::success('Success', 'Order delete success successfully');
        return redirect()->back();
    }

    public function order_assign(Request $request)
    {
        $products = Order::whereIn('id', $request->input('order_ids'))->update(['user_id' => $request->user_id]);
        return response()->json(['status' => 'success', 'message' => 'Order user id assign']);
    }

    public function order_status(Request $request)
    {
        $orderIds = $request->input('order_ids');
        if (is_array($orderIds) && count($orderIds) > 0) {
            $orders = Order::whereIn('id', $orderIds)->get();
            foreach ($orders as $order) {
                $oldStatus = $order->order_status;
                $order->order_status = $request->order_status;
                $order->save();

                \App\Service\InventoryService::adjustStockForStatusChange($order, $oldStatus, $request->order_status);
            }
        }
        return response()->json(['status' => 'success', 'message' => 'Order status change successfully']);
    }

    public function bulk_destroy(Request $request)
    {
        $orders_id = $request->order_ids;
        foreach ($orders_id as $order_id) {
            $order = Order::find($order_id);
            if ($order) {
                $order->delete();
            }
            $order_details = OrderDetails::where('order_id', $order_id)->delete();
            $shipping = Shipping::where('order_id', $order_id)->delete();
            $payment = Payment::where('order_id', $order_id)->delete();
        }
        return response()->json(['status' => 'success', 'message' => 'Order delete successfully']);
    }
    public function order_print(Request $request)
    {
        $orders = Order::whereIn('id', $request->input('order_ids'))->with('orderdetails', 'payment', 'shipping', 'customer')->get();
        if ($request->ajax() || $request->wantsJson()) {
            $view = view('backEnd.order.print', ['orders' => $orders])->render();
            return response()->json(['status' => 'success', 'view' => $view]);
        }
        return view('backEnd.order.print', ['orders' => $orders]);
    }
    public function bulk_courier($slug, Request $request)
    {
        $courier_info = Courierapi::where(['status' => 1, 'type' => $slug])->first();
        if ($courier_info) {
            $orders_id = $request->order_ids;
            foreach ($orders_id as $order_id) {
                $order = Order::find($order_id);
                $courier = $order->order_status;
                if ($courier != 5) {
                    $consignmentData = [
                        'invoice' => $order->invoice_id,
                        'recipient_name' => $order->shipping ? $order->shipping->name : '',
                        'recipient_phone' => $order->shipping ? $order->shipping->phone : '',
                        'recipient_address' => $order->shipping ? $order->shipping->address : '',
                        'cod_amount' => $order->amount
                    ];
                    $client = new Client();
                    $response = $client->post($courier_info->url, [
                        'json' => $consignmentData,
                        'headers' => [
                            'Api-Key' => $courier_info->api_key,
                            'Secret-Key' => $courier_info->secret_key,
                            'Accept' => 'application/json',
                        ],
                    ]);
                    $responseData = json_decode($response->getBody(), true);
                    if ($responseData['status'] == 200) {
                        $message = 'Your order place to courier successfully';
                        $status = 'success';
                        $order->order_status = 5;
                        $order->save();
                    } else {
                        $message = 'Your order place to courier failed';
                        $status = 'failed';
                    }
                    return response()->json(['status' => $status, 'message' => $message]);
                }
                
            }
        } 
    }
    public function order_create()
    {
        $products = Product::select('id', 'name', 'new_price', 'old_price', 'slug', 'type', 'stock', 'pro_barcode', 'category_id')
            ->where('status', 1)
            ->with('image', 'firstVariable', 'variables')
            ->withSum('variables', 'stock')
            ->get();
        $categories = Category::where('status', 1)->select('id', 'name')->get();
        $cartinfo = Cart::instance('sale')->content();
        $shippingcharge = Shippingcharge::where('status', 1)->get();
        Session::put('pos_shipping', 0);
        Session::forget('pos_discount');
        Session::forget('pos_discount_type');
        Session::forget('product_discount');
        Session::forget('cpaid');
        Session::forget('cdue');
        return view('backEnd.order.create', compact('products', 'categories', 'cartinfo', 'shippingcharge'));
    }

    public function order_store(Request $request)
    {
        // return $request->all();
        if ($request->guest_customer) {
            $customer = Customer::find($request->guest_customer);
            if (!$customer) {
                $customer = Customer::where('phone', '01700000000')->first();
            }
            if (!$customer) {
                $customer = Customer::first();
            }
            if (!$customer) {
                $customer = new Customer();
                $customer->name = 'Guest Customer';
                $customer->phone = '01700000000';
                $customer->password = bcrypt(rand(100000, 999999));
                $customer->status = 'active';
                $customer->save();
            }

            $area = ShippingCharge::where('pos', 1)->first();
            if (!$area) {
                $area = ShippingCharge::first();
            }
            if (!$area) {
                $area = new ShippingCharge();
                $area->name = 'POS Area';
                $area->amount = 0;
                $area->pos = 1;
                $area->status = 1;
                $area->save();
            }

            $name = $customer->name;
            $phone = $customer->phone;
            $address = $area->name;
            $area = $area->id;
        } else {
            $this->validate($request, [
                'name' => 'required',
                'phone' => 'required',
            ]);
            $name = $request->name;
            $phone = $request->phone;
            $address = $request->address ?? 'POS';
            $area = $request->area ?? 0;
        }

        if (Cart::instance('sale')->count() <= 0) {
            Toastr::error('Your shopping empty', 'Failed!');
            return redirect()->back();
        }

        $subtotal = Cart::instance('sale')->subtotal();
        $subtotal = str_replace(',', '', $subtotal);
        $subtotal = str_replace('.00', '', $subtotal);
        
        $pos_discount_val = Session::get('pos_discount') ?? 0;
        $pos_discount_type = Session::get('pos_discount_type') ?? 'flat';
        if ($pos_discount_type == 'percent') {
            $pos_discount = ($subtotal * $pos_discount_val) / 100;
        } else {
            $pos_discount = $pos_discount_val;
        }
        $discount = $pos_discount + (Session::get('product_discount') ?? 0);

        $shipping_area = ShippingCharge::where('id', $area)->first();
        if (!$shipping_area) {
            $shipping_area = new ShippingCharge();
            $shipping_area->id = 0;
            $shipping_area->name = 'Free Shipping';
            $shipping_area->amount = 0;
        }

        $exits_customer = Customer::where('phone', $phone)->select('phone', 'id')->first();
        if ($exits_customer) {
            $customer_id = $exits_customer->id;
        } else {
            $password = rand(111111, 999999);
            $store = new Customer();
            $store->name = $name;
            $store->slug = $name;
            $store->phone = $phone;
            $store->password = bcrypt($password);
            $store->verify = 1;
            $store->status = 'active';
            $store->save();
            $customer_id = $store->id;
        }

        $customer = Customer::find($customer_id);

        // order data save
        $order = new Order();
        $order->invoice_id = rand(11111, 99999);
        $order->amount = $subtotal - $discount;
        $order->discount = $discount ? $discount : 0;
        $order->shipping_charge = 0;
        $order->customer_id = $customer_id;
        $order->order_status = 6;
        $order->user_id = Auth::user()->id;
        $order->note = $request->note;
        $order->save();

        // shipping data save
        $shipping = new Shipping();
        $shipping->order_id = $order->id;
        $shipping->customer_id = $customer_id;
        if ($request->guest_customer) {
            $shipping->name = null;
            $shipping->phone = null;
            $shipping->address = 'POS';
            $shipping->area = null;
        } else {
            $shipping->name = $name;
            $shipping->phone = $phone;
            $shipping->address = $address;
            $shipping->area = $request->area ? $shipping_area->name : 'Free Shipping';
        }
        $shipping->save();

        // payment data save
        $payment = new Payment();
        $payment->order_id = $order->id;
        $payment->customer_id = $customer_id;
        $payment->payment_method = 'Cash On Delivery';
        $payment->amount = $order->amount;
        $payment->payment_status = 'pending';
        $payment->save();

        // order details data save
        foreach (Cart::instance('sale')->content() as $cart) {
            // return $cart;
            $order_details = new OrderDetails();
            $order_details->order_id = $order->id;
            $order_details->product_id = $cart->id;
            $order_details->product_name = $cart->name;
            $order_details->purchase_price = $cart->options->purchase_price;
            $order_details->product_discount = $cart->options->product_discount;
            $order_details->sale_price = $cart->price;
            $order_details->product_color = $cart->options->product_color;
            $order_details->product_size = $cart->options->product_size;
            $order_details->product_type = $cart->options->type ?? (Product::where('id', $cart->id)->value('type') ?? 0);
            $order_details->qty = $cart->qty;
            $order_details->save();
        }

        \App\Service\InventoryService::deductStock($order);
        Cart::instance('sale')->destroy();
        Session::forget('sale');
        Session::forget('pos_discount');
        Session::forget('pos_discount_type');
        Session::forget('product_discount');
        Session::forget('cpaid');
        Session::forget('cdue');
        Toastr::success('Thanks, Your order place successfully', 'Success!');
        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Order placed successfully',
                'order_id' => $order->id
            ]);
        }
        return redirect()->route('admin.orders', ['slug' => 'all']);
    }
    public function cart_add(Request $request)
    {
        $product = Product::select('id', 'name', 'slug', 'new_price', 'old_price', 'purchase_price', 'type', 'stock')->where(['id' => $request->id])->first();

        $var_product = ProductVariable::where(['product_id' => $request->id, 'color' => $request->color, 'size' => $request->size])->first();
        if ($product->type == 0) {
            $purchase_price = $var_product ? $var_product->purchase_price : 0;
            $old_price = $var_product ? $var_product->old_price : 0;
            $new_price = $var_product ? $var_product->new_price : 0;
            $stock = $var_product ? $var_product->stock : 0;
        } else {
            $purchase_price = $product->purchase_price;
            $old_price = $product->old_price;
            $new_price = $product->new_price;
            $stock = $product->stock;
        }

        $qty = 1;

        $cartitem = Cart::instance('sale')->content()->where('id', $product->id)->first();
        if ($cartitem) {
            $cart_qty = $cartitem->qty + $qty;
        } else {
            $cart_qty = $qty;
        }
        if ($stock < $cart_qty) {
            Toastr::error('Product stock limit over', 'Failed!');
            return response()->json(['status' => 'limitover', 'message' => 'Your stock limit is over']);
        }
        $cartinfo = Cart::instance('sale')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => $qty,
            'price' => $new_price,
            'weight' => 1,
            'options' => [
                'slug' => $product->slug,
                'image' => $product->image->image,
                'old_price' => $new_price,
                'purchase_price' => $purchase_price,
                'product_size' => $request->size,
                'product_color' => $request->color,
                'type' => $product->type
            ],
        ]);
        //  return Cart::instance('sale')->content();
        return response()->json(compact('cartinfo'));
    }
    public function cart_content()
    {
        $cartinfo = Cart::instance('sale')->content();
        return view('backEnd.order.cart_content', compact('cartinfo'));
    }
    public function cart_details()
    {
        $cartinfo = Cart::instance('sale')->content();
        $discount = 0;
        foreach ($cartinfo as $cart) {
            $discount += $cart->options->product_discount * $cart->qty;
        }
        Session::put('product_discount', $discount);
        return view('backEnd.order.cart_details', compact('cartinfo'));
    }
    public function cart_increment(Request $request)
    {
        $item = Cart::instance('sale')->get($request->id);
        if (!$item) {
            return response()->json(['status' => 'error', 'message' => 'Item not found']);
        }
        
        $product = Product::find($item->id);
        if ($product->type == 0) {
            $var_product = ProductVariable::where([
                'product_id' => $item->id,
                'color' => $item->options->product_color,
                'size' => $item->options->product_size
            ])->first();
            $stock = $var_product ? $var_product->stock : 0;
        } else {
            $stock = $product->stock;
        }
        
        $qty = $request->qty + 1;
        if ($qty > $stock) {
            return response()->json([
                'status' => 'limitover',
                'message' => 'Product stock limit over'
            ]);
        }

        $cartinfo = Cart::instance('sale')->update($request->id, $qty);
        return response()->json($cartinfo);
    }
    public function cart_decrement(Request $request)
    {
        $qty = $request->qty - 1;
        $cartinfo = Cart::instance('sale')->update($request->id, $qty);
        return response()->json($cartinfo);
    }
    public function cart_remove(Request $request)
    {
        $remove = Cart::instance('sale')->remove($request->id);
        $cartinfo = Cart::instance('sale')->content();
        return response()->json($cartinfo);
    }
    public function product_discount(Request $request)
    {
        $discount = $request->discount;
        $cart = Cart::instance('sale')->content()->where('rowId', $request->id)->first();
        $cartinfo = Cart::instance('sale')->update($request->id, [
            'options' => [
                'slug' => $cart->slug,
                'image' => $cart->options->image,
                'purchase_price' => $cart->options->old_price,
                'purchase_price' => $cart->options->purchase_price,
                'product_size' => $cart->options->size,
                'product_color' => $cart->options->color,
                'product_discount' => $request->discount,
                'type' => $cart->options->type,
                'details_id' => $cart->options->details_id
            ],
        ]);
        return response()->json($cartinfo);
    }
    public function cart_shipping(Request $request)
    {
        $shipping = ShippingCharge::where(['status' => 1, 'id' => $request->id])->first()->amount;
        Session::put('pos_shipping', $shipping);
        return response()->json($shipping);
    }

    public function cart_clear(Request $request)
    {
        $cartinfo = Cart::instance('sale')->destroy();
        Session::forget('pos_shipping');
        Session::forget('pos_discount');
        Session::forget('pos_discount_type');
        Session::forget('product_discount');
        return redirect()->back();
    }

    public function pos_discount(Request $request)
    {
        $discount = max(0, floatval($request->discount));
        $type = $request->type ?? 'flat';
        Session::put('pos_discount', $discount);
        Session::put('pos_discount_type', $type);
        return response()->json([
            'discount' => $discount,
            'type' => $type
        ]);
    }

    public function cart_json()
    {
        $content = Cart::instance('sale')->content()->values();
        return response()->json([
            'items' => $content,
            'pos_discount' => Session::get('pos_discount') ?? 0,
            'pos_discount_type' => Session::get('pos_discount_type') ?? 'flat',
            'pos_shipping' => Session::get('pos_shipping') ?? 0,
            'product_discount' => Session::get('product_discount') ?? 0,
        ]);
    }

    public function cart_hold_retrieve(Request $request)
    {
        Cart::instance('sale')->destroy();
        Session::forget('pos_shipping');
        Session::forget('pos_discount');
        Session::forget('product_discount');

        $items = $request->items ?? [];
        foreach ($items as $item) {
            Cart::instance('sale')->add([
                'id' => $item['id'],
                'name' => $item['name'],
                'qty' => (int)$item['qty'],
                'price' => floatval($item['price']),
                'weight' => 1,
                'options' => [
                    'slug' => $item['options']['slug'] ?? '',
                    'image' => $item['options']['image'] ?? '',
                    'old_price' => floatval($item['price']),
                    'purchase_price' => floatval($item['options']['purchase_price'] ?? 0),
                    'product_size' => $item['options']['product_size'] ?? '',
                    'product_color' => $item['options']['product_color'] ?? '',
                    'product_discount' => floatval($item['options']['product_discount'] ?? 0),
                    'type' => $item['options']['type'] ?? 1
                ],
            ]);
        }

        if ($request->has('pos_discount')) {
            Session::put('pos_discount', floatval($request->pos_discount));
        }
        if ($request->has('pos_discount_type')) {
            Session::put('pos_discount_type', $request->pos_discount_type);
        }
        if ($request->has('pos_shipping')) {
            Session::put('pos_shipping', floatval($request->pos_shipping));
        }

        return response()->json(['status' => 'success']);
    }

    public function order_edit($invoice_id){
        $data = Order::where(['id' => $invoice_id])->select('id', 'invoice_id', 'order_status', 'order_type')->with('orderdetails', 'shipping')->first();
        $products = Product::select('id', 'name', 'new_price')->where(['status' => 1])->get();
        $shippingcharge = Shippingcharge::get();
        $order = Order::where('id', $invoice_id)->first();
        $cartinfo = Cart::instance('sale')->destroy();
        $shippinginfo = Shipping::where('order_id', $order->id)->first();
        Session::put('product_discount', $order->discount);
        Session::put('pos_shipping', $order->shipping_charge);
        $orderdetails = OrderDetails::where('order_id', $order->id)->get();
        foreach ($orderdetails as $ordetails) {
            // Use saved image column first; fall back to the product image relationship for older orders
            $imageUrl = $ordetails->getRawOriginal('image') ?: optional($ordetails->productImage)->image ?? '';
            $cartinfo = Cart::instance('sale')->add([
                'id' => $ordetails->product_id,
                'name' => $ordetails->product_name,
                'qty' => $ordetails->qty,
                'price' => $ordetails->sale_price,
                'weight' => $ordetails->weight ?? 1,
                'options' => [
                    'image' => $imageUrl,
                    'purchase_price' => $ordetails->purchase_price,
                    'product_discount' => $ordetails->product_discount,
                    'product_size' => $ordetails->product_size,
                    'product_color' => $ordetails->product_color,
                    'details_id' => $ordetails->id,
                    'type' => $ordetails->product_type,
                ],
            ]);
        }
        $cartinfo = Cart::instance('sale')->content();
        return view('backEnd.order.edit', compact('products', 'cartinfo', 'shippingcharge', 'shippinginfo', 'order', 'data'));
    }

    public function order_update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'phone' => 'required',
        ]);

        if (Cart::instance('sale')->count() <= 0) {
            Toastr::error('Your shopping empty', 'Failed!');
            return redirect()->back();
        }

        $subtotal = Cart::instance('sale')->subtotal();
        $subtotal = str_replace(',', '', $subtotal);
        $subtotal = str_replace('.00', '', $subtotal);
        
        $pos_discount_val = Session::get('pos_discount') ?? 0;
        $pos_discount_type = Session::get('pos_discount_type') ?? 'flat';
        if ($pos_discount_type == 'percent') {
            $pos_discount = ($subtotal * $pos_discount_val) / 100;
        } else {
            $pos_discount = $pos_discount_val;
        }
        $discount = $pos_discount + (Session::get('product_discount') ?? 0);
        $shipping_area = Shippingcharge::where('id', $request->area)->first();
        $exits_customer = Customer::where('phone', $request->phone)->select('phone', 'id')->first();
        if ($exits_customer) {
            $customer_id = $exits_customer->id;
        } else {
            $password = rand(111111, 999999);
            $store = new Customer();
            $store->name = $request->name;
            $store->slug = $request->name;
            $store->phone = $request->phone;
            $store->password = bcrypt($password);
            $store->verify = 1;
            $store->status = 'active';
            $store->save();
            $customer_id = $store->id;
        }

        // order data save
        $order = Order::where('id', $request->order_id)->first();
        $order->amount = ($subtotal + $shipping_area->amount) - $discount;
        $order->discount = $discount ? $discount : 0;
        $order->shipping_charge = $shipping_area->amount;
        $order->customer_id = $customer_id;
        $order->note = $request->note;
        $order->save();


        // shipping data save
        $shipping = Shipping::where('order_id', $request->order_id)->first();
        $shipping->order_id = $order->id;
        $shipping->customer_id = $customer_id;
        $shipping->name = $request->name;
        $shipping->phone = $request->phone;
        $shipping->address = $request->address;
        $shipping->area = $request->area ? $shipping_area->name : 'Free Shipping';
        $shipping->save();

        // payment data save
        $payment = Payment::where('order_id', $request->order_id)->first();
        $payment->order_id = $order->id;
        $payment->customer_id = $customer_id;
        $payment->payment_method = 'Cash On Delivery';
        $payment->amount = $order->amount;
        $payment->payment_status = 'pending';
        $payment->save();

        // order details data save
        $isDeducted = \App\Service\InventoryService::isDeductedStatus($order->order_status);
        if ($isDeducted) {
            \App\Service\InventoryService::revertStock($order);
        }

        foreach ($order->orderdetails as $orderdetail) {
            $item = Cart::instance('sale')->content()->where('id', $orderdetail->product_id)->first();
            if (!$item) {
                $orderdetail->delete();
            }
        }
        foreach (Cart::instance('sale')->content() as $cart) {
            $exits = OrderDetails::where('id', $cart->options->details_id)->first();
            if ($exits) {
                $order_details = OrderDetails::find($exits->id);
                $order_details->product_discount = $cart->options->product_discount;
                $order_details->sale_price = $cart->price;
                $order_details->qty = $cart->qty;
                $order_details->save();
            } else {
                $order_details = new OrderDetails();
                $order_details->order_id = $order->id;
                $order_details->product_id = $cart->id;
                $order_details->product_name = $cart->name;
                $order_details->purchase_price = $cart->options->purchase_price;
                $order_details->product_discount = $cart->options->product_discount;
                $order_details->sale_price = $cart->price;
                $order_details->qty = $cart->qty;
                $order_details->product_size = $cart->options->product_size;
                $order_details->product_color = $cart->options->product_color;
                $order_details->product_type = $cart->options->type ?? (Product::where('id', $cart->id)->value('type') ?? 0);
                $order_details->save();
            }
        }

        $order->load('orderdetails');
        if ($isDeducted) {
            \App\Service\InventoryService::deductStock($order);
        }
        Cart::instance('sale')->destroy();
        Session::forget('pos_shipping');
        Session::forget('pos_discount');
        Session::forget('pos_discount_type');
        Session::forget('product_discount');
        Session::forget('cpaid');
        Toastr::success('Thanks, Your order place successfully', 'Success!');
        return redirect('admin/order/pending');
    }

    public function purchase_report()
    {
        $purchase = PurchaseDetails::with('product')->latest()->paginate(100);
        return view('backEnd.reports.purchase', compact('purchase'));
    }
    public function order_report(Request $request)
    {
        $users = User::where('status', 1)->get();
        $orders = OrderDetails::with('shipping', 'order')->whereHas('order', function ($query) {
            $query->where('order_status', 6);
        });
        if ($request->keyword) {
            $orders = $orders->where('name', 'LIKE', '%' . $request->keyword . "%");
        }
        if ($request->user_id) {
            $orders = $orders->whereHas('order', function ($query) use ($request) {
                $query->where('user_id', $request->user_id);
            });
        }
        if ($request->start_date && $request->end_date) {
            $orders = $orders->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }
        $total_purchases = $orders->sum(DB::raw('purchase_price * qty'));
        $total_item = $orders->sum('qty');
        $total_sales = $orders->sum(DB::raw('sale_price * qty'));
        $orders = $orders->paginate(50);
        return view('backEnd.reports.order', compact('orders', 'users', 'total_purchases', 'total_item', 'total_sales'));
    }
    public function return_report(Request $request)
    {
        $orders = OrderDetails::where('is_return', '=', 1)->with('shipping', 'order')->whereHas('order', function ($query) {
            $query->where('order_status', 5);
        });
        if ($request->keyword) {
            $orders = $orders->where('name', 'LIKE', '%' . $request->keyword . "%");
        }
        if ($request->start_date && $request->end_date) {
            $orders = $orders->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }
        $total_purchases = $orders->sum(DB::raw('purchase_price * qty'));
        $total_item = $orders->sum('qty');
        $total_sales = $orders->sum(DB::raw('sale_price * qty'));
        $orders = $orders->paginate(100);
        return view('backEnd.reports.return_report', compact('orders', 'total_purchases', 'total_item', 'total_sales'));
    }
    public function stock_report(Request $request)
    {
        $products = Product::select('id', 'name', 'new_price', 'purchase_price', 'stock', 'type')
            ->where('status', 1);
        if ($request->keyword) {
            $products = $products->where('name', 'LIKE', '%' . $request->keyword . "%");
        }
        if ($request->category_id) {
            $products = $products->where('category_id', $request->category_id);
        }
        if ($request->start_date && $request->end_date) {
            $products = $products->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }
        $total_purchase = $products->sum(DB::raw('purchase_price * stock'));
        $total_stock = $products->sum('stock');
        $total_price = $products->sum(DB::raw('new_price * stock'));
        $products = $products->with('variables')->paginate(50);
        $categories = Category::where('status', 1)->get();
        return view('backEnd.reports.stock', compact('products', 'categories', 'total_purchase', 'total_stock', 'total_price'));
    }
    public function expense_report(Request $request)
    {
        $data = Expense::where('status', 1);
        if ($request->keyword) {
            $data = $data->where('name', 'LIKE', '%' . $request->keyword . "%");
        }
        if ($request->category_id) {
            $data = $data->where('expense_cat_id', $request->category_id);
        }
        if ($request->start_date && $request->end_date) {
            $data = $data->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }
        $data = $data->paginate(10);
        $categories = ExpenseCategories::where('status', 1)->get();
        return view('backEnd.reports.expense', compact('data', 'categories'));
    }
    public function loss_profit(Request $request)
    {
        if ($request->start_date && $request->end_date) {
            $total_expense = Expense::where('status', 1)->whereBetween('created_at', [$request->start_date, $request->end_date])->sum('amount');
            $total_purchase = OrderDetails::whereHas('order', function ($query) use ($request) {
                $query->where('order_status', 6)
                    ->whereBetween('created_at', [$request->start_date, $request->end_date]);
            })->sum(DB::raw('purchase_price * qty'));

            $total_sales = OrderDetails::whereHas('order', function ($query) use ($request) {
                $query->where('order_status', 6)
                    ->whereBetween('created_at', [$request->start_date, $request->end_date]);
            })->sum(DB::raw('sale_price * qty'));
        } else {
            $total_expense = Expense::where('status', 1)->sum('amount');
            $total_purchase = OrderDetails::whereHas('order', function ($query) {
                $query->where('order_status', 6);
            })->sum(DB::raw('purchase_price * qty'));

            $total_sales = OrderDetails::whereHas('order', function ($query) {
                $query->where('order_status', 6);
            })->sum(DB::raw('sale_price * qty'));
        }

        return view('backEnd.reports.loss_profit', compact('total_expense', 'total_purchase', 'total_sales'));
    }

    public function order_paid(Request $request)
    {
        $customer = Customer::select('id', 'phone', 'due')->where('phone', $request->phone)->first();
        if ($customer) {
            Session::put('cdue', $customer->due);
        }
        $amount = $request->amount;
        Session::put('cpaid', $amount);
        return response()->json($amount);
    }
}
