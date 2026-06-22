<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\ProductVariable;
use App\Models\Product;
use App\Models\District;
use App\Models\CouponCode;

class ShoppingController extends Controller
{
    
    public function cart_left_count(Request $request)
    {
        $count = Cart::instance('shopping')->count();
        $total = Cart::instance('shopping')->subtotal(); // Returns a string like "1200.00"
    
        return view('frontEnd.layouts.ajax.cart_left_count', compact('count', 'total'));
    }
    public function cart_right_count(Request $request)
    {
        $count = Cart::instance('shopping')->count();
        $total = Cart::instance('shopping')->subtotal();
    
        return view('frontEnd.layouts.ajax.cart_right_data', compact('count', 'total'));
    }
    public function cart_clear(Request $request)
    {
        Cart::instance('shopping')->destroy();
    
        return response()->json([
            'success' => true,
        ]);
    }



     public function wishlist_store(Request $request){
        $product = Product::select('id','name','slug','old_price','new_price','purchase_price')->where(['id' => $request->id])->first();
        Cart::instance('wishlist')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => $request->qty,
            'price' => $product->new_price,
             'weight' => 1,
            'options' => [
                'slug' => $product->slug,
                'image' => $product->image->image,
                'old_price' => $product->new_price,
                'purchase_price' => $product->purchase_price,
            ],
        ]);
        $data = Cart::instance('wishlist')->content();
        return response()->json('data');
         
    }
    public function wishlist_show() {
        $data = Cart::instance('wishlist')->content();
        return view('frontEnd.layouts.pages.wishlist',compact('data'));
    } 
    public function wishlist_remove(Request $request) {
        $remove = Cart::instance('wishlist')->update($request->id,0);
        $data   = Cart::instance('wishlist')->content();
        return view('frontEnd.layouts.ajax.wishlist',compact('data'));
    }    
    public function wishlist_count(Request $request) {
        $data   = Cart::instance('wishlist')->count();
        return view('frontEnd.layouts.ajax.wishlist_count',compact('data'));
    } 
    
    public function addTocartGet($id, Request $request)
    {
        $qty = 1;
        $product = DB::table('products')->where('id', $id)->first();
        $productImage = DB::table('productimages')->where('product_id', $id)->first();
        $find_coupon = CouponCode::select('id','category_id')->where('category_id',$product->category_id)->first();
        $cartinfo = Cart::instance('shopping')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => $qty,
            'price' => $product->new_price,
            'weight' => 1,
            'options' => [
                'image' => $productImage->image,
                'old_price' => $product->old_price,
                'slug' => $product->slug,
                'purchase_price' => $product->purchase_price,
                'category_id' => $product->category_id,
                'coupon_id' => $product->coupon_id,
                 'enable_coupon' => $find_coupon ? 1 : 0,
                'sku' => $product->sku,
            ]
        ]);

        return response()->json($cartinfo);
    }

    public function cart_store(Request $request)
    {
        $product = Product::select('id', 'name', 'slug', 'new_price', 'old_price', 'purchase_price', 'type', 'stock','category_id','coupon_id','sku')->where(['id' => $request->id])->first();
        $var_product = ProductVariable::where(['product_id' => $request->id, 'color' => $request->product_color, 'size' => $request->product_size])->first();
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
        $cartitem = Cart::instance('shopping')->content()->where('id', $product->id)->first();
        if ($cartitem) {
            $cart_qty = $cartitem->qty + $request->qty??1;
        } else {
            $cart_qty = $request->qty??1;
        }
        if ($stock < $cart_qty) {
            Toastr::error('Product stock limit over', 'Failed!');
            return back();
        }
        $find_coupon = CouponCode::select('id','category_id')->where('category_id',$product->category_id)->first();
        
        $carts = Cart::instance('shopping')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => $request->qty??1,
            'price' => $new_price,
            'weight' => 1,
            'options' => [
                'slug' => $product->slug,
                'image' => $product->image->image,
                'old_price' => $new_price,
                'purchase_price' => $purchase_price,
                'product_size' => $request->product_size,
                'product_color' => $request->product_color,
                'sku' => $product->sku,
                'coupon_id' => $product->coupon_id,
                'enable_coupon' => $find_coupon ? 1 : 0,
                'type' => $product->type,
                'category_id' => $product->category_id,
                'free_shipping' =>  0
            ],
        ]);
        //   return $carts;
       
        if ($request->ajax()) {
             return response()->json(['status' => 'success', 'message' => 'Product successfully add to cart']);
        }
        Toastr::success('Product successfully add to cart', 'Success!');
        if ($request->add_cart) {
            return back();
        }
        return redirect()->route('customer.checkout');
    }
    public function campaign_stock(Request $request)
    {
        $product = ProductVariable::where(['product_id' => $request->id, 'color' => $request->color, 'size' => $request->size])->first();

        $status = $product ? true : false;
        $response = [
            'status' => $status,
            'product' => $product
        ];
        return response()->json($response);
    }
    public function cart_content(Request $request)
    {
        $data = Cart::instance('shopping')->content();
        return view('frontEnd.layouts.ajax.cart', compact('data'));
    }
    public function cart_remove(Request $request)
    {
        $data = Cart::instance('shopping')->update($request->id, 0);
        $data = Cart::instance('shopping')->content();
        $this->coupon_check();
        return view('frontEnd.layouts.ajax.cart', compact('data'));
    }
    
    public function cart_increment(Request $request)
    {
        $item = Cart::instance('shopping')->get($request->id);
        $qty = $item->qty + 1;
        $increment = Cart::instance('shopping')->update($request->id, $qty);
        $data = Cart::instance('shopping')->content();
        $this->coupon_check();
        return view('frontEnd.layouts.ajax.cart', compact('data'));
    }
    
    public function cart_decrement(Request $request)
    {
        $item = Cart::instance('shopping')->get($request->id);
        $qty = $item->qty - 1;
        $decrement = Cart::instance('shopping')->update($request->id, $qty);
        $data = Cart::instance('shopping')->content();
        $this->coupon_check();
        return view('frontEnd.layouts.ajax.cart', compact('data'));
    }
    public function cart_count(Request $request)
    {
        $data = Cart::instance('shopping')->count();
        return view('frontEnd.layouts.ajax.cart_count', compact('data'));
    }
    public function mobilecart_qty(Request $request)
    {
        $data = Cart::instance('shopping')->count();
        return view('frontEnd.layouts.ajax.mobilecart_qty', compact('data'));
    }

    
    public function cart_increment_camp(Request $request)
    {
        $item = Cart::instance('shopping')->get($request->id);
        $qty = $item->qty + 1;
        $increment = Cart::instance('shopping')->update($request->id, $qty);
        $data = Cart::instance('shopping')->content();
        return view('frontEnd.layouts.ajax.cart_camp', compact('data'));
    }
    public function cart_decrement_camp(Request $request)
    {
        $item = Cart::instance('shopping')->get($request->id);
        $qty = $item->qty - 1;
        $decrement = Cart::instance('shopping')->update($request->id, $qty);
        $data = Cart::instance('shopping')->content();
        return view('frontEnd.layouts.ajax.cart_camp', compact('data'));
    }
    public function cart_content_camp(Request $request)
    {
        $data = Cart::instance('shopping')->content();
        return view('frontEnd.layouts.ajax.cart_camp', compact('data'));
    }
    public function coupon_check(){
        $findcoupon = CouponCode::where('coupon_code', Session::get('coupon_used'))->first();

        if ($findcoupon == NULL) {
            return response()->json(['status'=>'failed','message'=>'Opps! your entered promo code is not valid']);
        } else {
            $currentdata = date('Y-m-d');
            $expiry_date = $findcoupon->expiry_date;
            if ($currentdata <= $expiry_date) {
             $totalcart = Cart::instance('shopping')->content()->filter(function ($item) {
                return isset($item->options['enable_coupon']) && $item->options['enable_coupon'] == 1;
            });
             
                $totalcart = (int)$totalcart->sum('price')*(int)$totalcart->sum('qty');
                $totalcart = str_replace('.00', '', $totalcart);
                $totalcart = str_replace(',', '', $totalcart);
    
                if ($totalcart >= $findcoupon->buy_amount) {
                    if ($findcoupon->offer_type == 1) {
                        $discountammount =  (($totalcart * $findcoupon->amount) / 100);
                        Session::forget('coupon_amount');
                        Session::put('coupon_amount', $discountammount);
                        Session::put('coupon_used', $findcoupon->coupon_code);
                    } else {
                        Session::put('coupon_amount', $findcoupon->amount);
                        Session::put('coupon_used', $findcoupon->coupon_code);
                    }
                    $data = Cart::instance('shopping')->content();
                   return view('frontEnd.layouts.ajax.cart', compact('data'));         
                } else {
                   $data = Cart::instance('shopping')->content();
                   return view('frontEnd.layouts.ajax.cart', compact('data'));
                }
            } else {
                $data = Cart::instance('shopping')->content();
                 return view('frontEnd.layouts.ajax.cart', compact('data'));
            }
        }
    }
}
