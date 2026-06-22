<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderStatus;
use App\Models\ProductVariable;
use App\Models\SmsGateway;
use App\Models\GeneralSetting;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['locked', 'unlocked']);
    }
    public function dashboard(Request $request)
    {
        $order_statuses = OrderStatus::where('status', 1)->withCount('orders')->get();
        $total_sale = Order::where('order_status', 6)->sum('amount');
        $today_order = Order::whereDate('created_at',  Carbon::today())->count();
        $today_sales = Order::where('order_status', 6)->whereDate('created_at',  Carbon::today())->sum('amount');
        $current_month_sale = Order::where('order_status', 6)->whereMonth('created_at', Carbon::now()->month)->sum('amount');
        $total_order = Order::count();
        $current_month_order = Order::whereMonth('created_at', Carbon::now()->month)->count();
        $total_customer = Customer::count();
        $latest_order = Order::latest()->limit(5)->with('customer', 'product', 'product.productImage')->get();
        $latest_customer = Customer::latest()->limit(5)->get();
        $dates = [];
        $startDate = Carbon::now()->subDays(29);
        for ($i = 0; $i < 30; $i++) {
            $dates[] = $startDate->copy()->addDays($i)->format('Y-m-d');
        }
        $payments = Order::selectRaw('DATE(created_at) as date, SUM(amount) as total_amount')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->pluck('total_amount', 'date');
        $totals = [];
        foreach ($dates as $date) {
            $totals[] = isset($payments[$date]) ? $payments[$date] : 0;
        }
        $dates_json = json_encode($dates);
        $totals_json = json_encode($totals);
        $products = Product::select('id', 'name', 'type', 'stock', 'stock_alert')->where('type', 1)->where('stock', '<=', DB::raw('stock_alert'))->with('image')->limit(10)->get();
        $variables = ProductVariable::whereHas('product', function ($query) {
            $query->whereRaw('product_variables.stock <= products.stock_alert');
        })->with('product', 'image')
            ->limit(10)->get();

        if ($request->start_date && $request->end_date) {
            $total_order = Order::whereBetween('created_at', [$request->start_date, $request->end_date])->count();
            $return_amount = Order::where('order_status', '8')->whereBetween('created_at', [$request->start_date, $request->end_date])->sum('amount');
            $total_complete = Order::where('order_status', '6')->whereBetween('created_at', [$request->start_date, $request->end_date])->count();
            $total_process = Order::whereNotIn('order_status', ['1', '6', '7', '8'])->whereBetween('created_at', [$request->start_date, $request->end_date])->count();
            $total_return = Order::where('order_status', '9')->whereBetween('created_at', [$request->start_date, $request->end_date])->count();
            $delivery_amount = Order::where('order_status', '6')->whereBetween('created_at', [$request->start_date, $request->end_date])->sum('amount');
            $total_amount = Order::whereBetween('created_at', [$request->start_date, $request->end_date])->sum('amount');
            $process_amount = Order::whereNotIn('order_status', ['1', '6', '7', '8'])->whereBetween('created_at', [$request->start_date, $request->end_date])->sum('amount');
        } else {
            $total_order = Order::count();
            $return_amount = Order::where('order_status', '8')->sum('amount');
            $total_complete = Order::where('order_status', '6')->count();
            $total_process = Order::whereNotIn('order_status', ['1', '6', '7', '8'])->count();
            $total_return = Order::where('order_status', '9')->count();
            $delivery_amount = Order::where('order_status', '6')->sum('amount');
            $total_amount = Order::sum('amount');
            $process_amount = Order::whereNotIn('order_status', ['1', '6', '7', '8'])->sum('amount');
        }
        return view('backEnd.admin.dashboard', compact(
            'order_statuses',
            'total_sale',
            'today_order',
            'today_sales',
            'current_month_sale',
            'total_order',
            'current_month_order',
            'total_customer',
            'latest_order',
            'dates_json',
            'totals_json',
            'products',
            'variables',
            'total_order',
            'return_amount',
            'total_complete',
            'total_process',
            'delivery_amount',
            'total_amount',
            'process_amount',
            'total_return'
        ));
    }
    public function changepassword()
    {
        return view('backEnd.admin.changepassword');
    }
    public function newpassword(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required_with:new_password|same:new_password|'
        ]);

        $user = User::find(Auth::id());
        $hashPass = $user->password;

        if (Hash::check($request->old_password, $hashPass)) {
            $user->fill([
                'password' => Hash::make($request->new_password)
            ])->save();

            Toastr::success('Success', 'Password changed successfully!');
            return redirect()->route('dashboard');
        } else {
            Toastr::error('Failed', 'Old password not match!');
            return back();
        }
    }
    public function locked()
    {
        // only if user is logged in

        Session::put('locked', true);
        return view('backEnd.auth.locked');
        return redirect()->route('login');
    }

    public function unlocked(Request $request)
    {
        if (!Auth::check())
            return redirect()->route('login');
        $password = $request->password;
        if (Hash::check($password, Auth::user()->password)) {
            Session::forget('locked');
            Toastr::success('Success', 'You are logged in successfully!');
            return redirect()->route('dashboard');
        }
        Toastr::error('Failed', 'Your password not match!');
        return back();
    }

    public function send_sms()
    {
        $customers = Customer::all();
        return view('backEnd.smssend.index', compact('customers'));
    }

    public function send_sms_post(Request $request)
    {
        $this->validate($request, [
            'customer_id' => 'required',
            'text' => 'required',
        ]);

        if ($request->customer_id == 'all') {
            $customers = Customer::all();
            foreach ($customers as $customer) {
                $customer_info = Customer::find($customer->id);

                $site_setting = GeneralSetting::where('status', 1)->first();
                $sms_gateway = SmsGateway::where(['status' => 1])->first();
                if ($sms_gateway) {
                    $url = "$sms_gateway->url";
                    $data = [
                        "api_key" => "$sms_gateway->api_key",
                        "number" => $customer_info->phone,
                        "type" => 'text',
                        "senderid" => "$sms_gateway->serderid",
                        "message" => "$request->text. \r\nThank you for using $site_setting->name"
                    ];
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $response = curl_exec($ch);
                    curl_close($ch);
                }
            }
        } else {
            $customer_info = Customer::find($request->customer_id);

            $site_setting = GeneralSetting::where('status', 1)->first();
            $sms_gateway = SmsGateway::where(['status' => 1])->first();
            if ($sms_gateway) {
                $url = "$sms_gateway->url";
                $data = [
                    "api_key" => "$sms_gateway->api_key",
                    "number" => $customer_info->phone,
                    "type" => 'text',
                    "senderid" => "$sms_gateway->serderid",
                    "message" => "$request->text. \r\nThank you for using $site_setting->name"
                ];
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                curl_close($ch);
            }
        }

        Toastr::success('Success', 'Data sent successfully');
        return redirect()->route('admin.send_sms');
    }
}
