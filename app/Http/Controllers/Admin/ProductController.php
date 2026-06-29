<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Product;
use App\Models\Productimage;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use App\Models\Brand;
use App\Models\Color;
use App\Models\ProductVariable;
use App\Models\PurchaseDetails;
use App\Models\Size;
use App\Models\CouponCode;
use File;

class ProductController extends Controller
{
    
    function __construct()
    {
        $this->middleware('permission:product-list|product-create|product-edit|product-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:product-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:product-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:product-delete', ['only' => ['destroy']]);
    }
    public function getSubcategory(Request $request)
    {
        $subcategory = DB::table("subcategories")
            ->where("category_id", $request->category_id)
            ->pluck('name', 'id');
        return response()->json($subcategory);
    }
    public function getChildcategory(Request $request)
    {
        $childcategory = DB::table("childcategories")
            ->where("subcategory_id", $request->subcategory_id)
            ->pluck('name', 'id');
        return response()->json($childcategory);
    }


    public function index(Request $request)
    {
        $data = Product::latest()->select('id','name','category_id','new_price','topsale','feature_product','type','pro_barcode','sku','status')->with('image', 'category', 'firstVariable')->withSum('variables', 'stock');
        
        if ($request->keyword) {
            $data = $data->where(function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->keyword . '%')
                      ->orWhere('sku', 'LIKE', '%' . $request->keyword . '%');
            });
        }
        $data = $data->paginate(50);
        return view('backEnd.product.index', compact('data'));
    }
    
    public function stock_alert(){
        $products = Product::select('id','name','type','stock','stock_alert')->where('type',1)->where('stock', '<=', DB::raw('stock_alert'))->with('image')->get();
        $variables = ProductVariable::whereHas('product', function($query) {
            $query->whereRaw('product_variables.stock <= products.stock_alert');
        })->with('product','image')
        ->get();
        return view('backEnd.product.stock_alert', compact('products','variables'));
    }

    public function barcode(Request $request)
    {
        if ($request->keyword) {
            $data = Product::select('id', 'name', 'status', 'pro_barcode', 'new_price', 'type')
                ->orderBy('id', 'DESC')
                ->where('name', 'LIKE', '%' . $request->keyword . "%")
                ->with('variables')
                ->get();
        } else {
            $data = Product::select('id', 'name', 'status', 'pro_barcode', 'new_price', 'type')
                ->orderBy('id', 'DESC')
                ->with('variables')
                ->get();
        } 

        $selected_items = [];
        
        // Handle single product quick print link
        if ($request->product_id) {
            $type = $request->type ?? 1;
            $qty = max(1, (int)($request->copies ?? 1));
            
            if ($type == 1) {
                $item = Product::select('id', 'name', 'pro_barcode', 'new_price', 'type')->find($request->product_id);
                if ($item) {
                    $selected_items[] = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'pro_barcode' => $item->pro_barcode,
                        'new_price' => $item->new_price,
                        'size' => null,
                        'color' => null,
                        'copies' => $qty,
                        'type' => 1
                    ];
                }
            } else {
                $item = ProductVariable::with('product')->find($request->product_id);
                if ($item && $item->product) {
                    $selected_items[] = [
                        'id' => $item->id,
                        'name' => $item->product->name,
                        'pro_barcode' => $item->pro_barcode,
                        'new_price' => $item->new_price,
                        'size' => $item->size,
                        'color' => $item->color,
                        'copies' => $qty,
                        'type' => 0
                    ];
                }
            }
        }
        
        // Handle multi-product print batch form submission
        if ($request->product_ids) {
            foreach ($request->product_ids as $index => $id) {
                $type = $request->types[$index] ?? 1;
                $qty = max(1, (int)($request->copies[$index] ?? 1));
                
                // Avoid duplicating if already added by single product quick print
                $already_added = false;
                foreach ($selected_items as $sel) {
                    if ($sel['id'] == $id && $sel['type'] == $type) {
                        $already_added = true;
                        break;
                    }
                }
                if ($already_added) continue;

                if ($type == 1) {
                    $item = Product::select('id', 'name', 'pro_barcode', 'new_price', 'type')->find($id);
                    if ($item) {
                        $selected_items[] = [
                            'id' => $item->id,
                            'name' => $item->name,
                            'pro_barcode' => $item->pro_barcode,
                            'new_price' => $item->new_price,
                            'size' => null,
                            'color' => null,
                            'copies' => $qty,
                            'type' => 1
                        ];
                    }
                } else {
                    $item = ProductVariable::with('product')->find($id);
                    if ($item && $item->product) {
                        $selected_items[] = [
                            'id' => $item->id,
                            'name' => $item->product->name,
                            'pro_barcode' => $item->pro_barcode,
                            'new_price' => $item->new_price,
                            'size' => $item->size,
                            'color' => $item->color,
                            'copies' => $qty,
                            'type' => 0
                        ];
                    }
                }
            }
        }

        return view('backEnd.product.barcode', compact('data', 'selected_items'));
    }



    public function create()
    {
        $categories = Category::where('status', 1)->select('id', 'name', 'status')->get();
        $brands = Brand::where('status', '1')->select('id', 'name', 'status')->get();
        $colors = Color::where('status', '1')->get();
        $sizes = Size::where('status', '1')->get();
        $coupons = CouponCode::where(['status' => 1])
        ->orderBy('id', 'ASC')
        ->get();
        return view('backEnd.product.create', compact('categories', 'brands','coupons', 'colors', 'sizes'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'category_id' => 'required',
            'description' => 'required',
        ]);
        // image with intervention
        $images2 = $request->file('size_chart');
        if ($images2) {
            $name2 = time() . '-' . $images2->getClientOriginalName();
            $name2 = strtolower(preg_replace('/\s+/', '-', $name2));
            $uploadPath2 = 'public/uploads/product/';
            $images2->move($uploadPath2, $name2);
            $imageUrl2 = $uploadPath2 . $name2;
        } else {
            $imageUrl2 = null;
        }

        $max_id = DB::table('products')->max('id');
        $max_id = $max_id? $max_id+1 : '1';
        $input = $request->except(['image', 'product_type', 'files', 'sizes', 'colors', 'purchase_prices', 'old_prices', 'new_prices', 'stocks', 'images', 'pro_barcodes']);
        $input['slug'] = strtolower(preg_replace('/[\/\s]+/', '-', $request->name . '-' . $max_id));
        $input['coupon_id'] = $request->coupon_id??null;

        

        $input['status'] = $request->status ? 1 : 0;
        $input['topsale'] = $request->topsale ? 1 : 0;
        $input['size_chart'] = $imageUrl2;
        if($request->type == 0){
            $input['purchase_price'] = $request->purchase_prices[0];
            $input['old_price'] = $request->old_prices[0];
            $input['new_price'] = $request->new_prices[0];
            $input['stock']     = 0;
        }else{
            $input['pro_barcode'] = $request->pro_barcode??$this->barcode_generate();
        }
        $save_data = Product::create($input);
 
        $pro_image = $request->file('image');
        if ($pro_image) {
            foreach ($pro_image as $key => $image) {
                $name =  time() . '-' . $image->getClientOriginalName();
                $name = strtolower(preg_replace('/\s+/', '-', $name));
                $uploadPath = 'public/uploads/product/';
                $image->move($uploadPath, $name);
                $imageUrl = $uploadPath . $name;
                $pimage             = new Productimage();
                $pimage->product_id = $save_data->id;
                $pimage->image      = $imageUrl;
                $pimage->save();
            }
        }
        if ($request->stocks) {
            $size      = $request->sizes;
            $color      = $request->colors;
            $stocks      = array_filter($request->stocks);
            $purchase   = $request->purchase_prices;
            $old_price  = $request->old_prices;
            $new_price  = $request->new_prices;
            $pro_barcode = $request->pro_barcodes;
            $images     = $request->file('images');
            if (is_array($stocks)) {
                foreach ($stocks as $key => $stock) {
                    $imageUrl = null;

                    if (isset($images[$key]) && $images[$key] != null) {
                        $image = $images[$key];
                        $name = time() . '-' . $image->getClientOriginalName();
                        $name = strtolower(preg_replace('/\s+/', '-', $name));
                        $uploadPath = 'public/uploads/product/';
                        $image->move($uploadPath, $name);
                        $imageUrl = $uploadPath . $name;
                    }

                    $variable = new ProductVariable();
                    $variable->product_id = $save_data->id;
                    $variable->size = isset($size[$key]) ? $size[$key] : null;
                    $variable->color = isset($color[$key]) ? $color[$key] : null;
                    $variable->purchase_price = isset($purchase[$key]) ? $purchase[$key] : null;
                    $variable->old_price = isset($old_price[$key]) ? $old_price[$key] : null;
                    $variable->new_price = isset($new_price[$key]) ? $new_price[$key] : null;
                    $variable->pro_barcode = $pro_barcode[$key]??$this->barcode_generate();
                    $variable->stock = $stock;
                    $variable->image = $imageUrl;
                    $variable->save();

                    $parchase_var                   = new PurchaseDetails();
                    $parchase_var->product_id       = $save_data->id;
                    $parchase_var->color            = $variable->color;
                    $parchase_var->size             = $variable->size;
                    $parchase_var->purchase_price   = $variable->purchase_price;
                    $parchase_var->old_price        = $variable->old_price;
                    $parchase_var->new_price        = $variable->new_price;
                    $parchase_var->stock            = $variable->stock;
                    $parchase_var->save();
                }
            }
        }
        if($request->type == 1){
            $parchase                   = new PurchaseDetails();
            $parchase->product_id       = 1;
            $parchase->purchase_price   = $request->purchase_price;
            $parchase->old_price        = $request->old_price;
            $parchase->new_price        = $request->new_price;
            $parchase->stock            = $request->stock;
            $parchase->save();   
        }

        Toastr::success('Success', 'Data insert successfully');
        return redirect()->route('products.index');
    }


    public function edit($id)
    {
        $edit_data = Product::with('images')->find($id);
        $categories = Category::where('status', 1)->select('id', 'name', 'status')->get();
        $subcategory = Subcategory::where('category_id', '=', $edit_data->category_id)->select('id', 'name','category_id', 'status')->get();
        $childcategory = Childcategory::where('subcategory_id', '=', $edit_data->subcategory_id)->select('id', 'name','subcategory_id', 'status')->get();
        $brands = Brand::where('status', '1')->select('id', 'name', 'status')->get();
        $colors = Color::where('status', '1')->get();
        $sizes = Size::where('status', '1')->get();
        $coupons = CouponCode::where(['status' => 1])->orderBy('id', 'ASC')->get();
        $variables = ProductVariable::where('product_id', $id)->get();
        return view('backEnd.product.edit', compact('edit_data', 'categories', 'subcategory','coupons', 'childcategory', 'brands', 'sizes', 'colors', 'variables'));
    }

    public function update(Request $request)
    {
        // return $request->all();
        $this->validate($request, [
            'name' => 'required',
            'category_id' => 'required',
            'description' => 'required',
        ]);
        
        

        $update_data = Product::find($request->id);
        $input = $request->except(['image', 'qty', 'product_type', 'files', 'sizes', 'colors', 'purchase_prices', 'old_prices', 'new_prices', 'stocks', 'images', 'up_id', 'up_sizes', 'up_colors', 'up_purchase_prices', 'up_old_prices', 'up_new_prices', 'up_stocks', 'up_images', 'pro_barcodes', 'up_pro_barcodes']);
        
        
         // image with intervention
        $images2 = $request->file('size_chart');
        if ($images2) {
            $name2 = time() . '-' . $images2->getClientOriginalName();
            $name2 = strtolower(preg_replace('/\s+/', '-', $name2));
            $uploadPath2 = 'public/uploads/product/';
            $images2->move($uploadPath2, $name2);
            $imageUrl2 = $uploadPath2 . $name2;

        } else {
            $imageUrl2 = $update_data->size_chart;
        }
        
        $last_id = Product::orderBy('id', 'desc')->select('id')->first();
        $input['slug'] = strtolower(preg_replace('/[\/\s]+/', '-', $request->name . '-' . $update_data->id));
        $input['status'] = $request->status ? 1 : 0;
        $input['topsale'] = $request->topsale ? 1 : 0;
        $input['size_chart'] = $imageUrl2;
        
        if($request->type == 0){
            $input['purchase_price'] = $request->up_purchase_prices ? $request->up_purchase_prices[0] : ($request->purchase_prices ? $request->purchase_prices[0] : 0);
            $input['old_price'] = $request->up_old_prices ? $request->up_old_prices[0] : ($request->old_prices ? $request->old_prices[0] : 0);
            $input['new_price'] = $request->up_new_prices ? $request->up_new_prices[0] : ($request->new_prices ? $request->new_prices[0] : 0);
            $input['stock']     = 0;
        }
        
       
        $input['coupon_id'] = $request->coupon_id??null;
        // return $update_data;
        $update_data->update($input);

        $images = $request->file('image');
        if ($images) {
            foreach ($images as $key => $image) {
                $name =  time() . '-' . $image->getClientOriginalName();
                $name = strtolower(preg_replace('/\s+/', '-', $name));
                $uploadPath = 'public/uploads/product/';
                $image->move($uploadPath, $name);
                $imageUrl = $uploadPath . $name;

                $pimage             = new Productimage();
                $pimage->product_id = $update_data->id;
                $pimage->image      = $imageUrl;
                $pimage->save();
            }
        }

        if ($request->up_id) {
            $update_ids = array_filter($request->up_id);
            $up_color   = $request->up_colors;
            $up_size    = $request->up_sizes;
            $up_size    = $request->up_sizes;
            $up_stock   = $request->up_stocks;
            $up_purchase     = $request->up_purchase_prices;
            $up_old_price    = $request->up_old_prices;
            $up_new_price    = $request->up_new_prices;
            $up_pro_barcode    = $request->up_pro_barcodes;
            $images     = $request->file('up_images');
            if ($update_ids) {
                foreach ($update_ids as $key => $update_id) {
                    $upvariable =  ProductVariable::find($update_id);
                    if (isset($images[$key])) {
                        $image = $images[$key];
                        $name  =  time() . '-' . $image->getClientOriginalName();
                        $name  = strtolower(preg_replace('/\s+/', '-', $name));
                        $uploadPath = 'public/uploads/product/';
                        $image->move($uploadPath, $name);
                        $imageUrl = $uploadPath . $name;
                        File::delete($upvariable->image);
                    } else {
                        $imageUrl = $upvariable->image;
                    }

                    $upvariable->product_id       = $update_data->id;
                    $upvariable->size             = $up_size ? $up_size[$key] : NULL;
                    $upvariable->color            = $up_color ? $up_color[$key] : NULL;
                    $upvariable->purchase_price   = $up_purchase[$key];
                    $upvariable->old_price        = $up_old_price ? $up_old_price[$key] : NULL;
                    $upvariable->new_price        = $up_new_price[$key];
                    $upvariable->pro_barcode      = $up_pro_barcode?$up_pro_barcode[$key]:NULL;
                    $upvariable->stock            = $up_stock[$key];
                    $upvariable->image            = $imageUrl;
                    $upvariable->save();
                }
            }
        }


        if ($request->stocks) {
            $size       = $request->sizes;
            $color      = $request->colors;
            $stocks     = array_filter($request->stocks);
            $purchase   = $request->purchase_prices;
            $old_price  = $request->old_prices;
            $new_price  = $request->new_prices;
            $pro_barcode = $request->pro_barcodes;
            $images     = $request->file('images');
            if (is_array($stocks)) {
                foreach ($stocks as $key => $stock) {
                    
                    if (isset($images[$key])) {
                        $image = $images[$key];
                        $name =  time() . '-' . $image->getClientOriginalName();
                        $name = strtolower(preg_replace('/\s+/', '-', $name));
                        $uploadPath = 'public/uploads/product/';
                        $image->move($uploadPath, $name);
                        $imageUrl = $uploadPath . $name;
                    } else {
                        $imageUrl = NULL;
                    }

                    $variable                   = new ProductVariable();
                    $variable->product_id       = $update_data->id;
                    $variable->size             = $size ? $size[$key] : NULL;
                    $variable->color            = $color ? $color[$key] : NULL;
                    $variable->purchase_price   = $purchase[$key];
                    $variable->old_price        = $old_price ? $old_price[$key] : NULL;
                    $variable->new_price        = $new_price[$key];
                    $variable->stock            = $stock;
                    $variable->pro_barcode      = $pro_barcode[$key];
                    $variable->image            = $imageUrl;
                    $variable->save();

                }
            }
        }

        Toastr::success('Success', 'Data update successfully');
        return redirect()->route('products.index');
    }

    public function inactive(Request $request)
    {
        $inactive = Product::find($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();
        Toastr::success('Success', 'Data inactive successfully');
        return redirect()->back();
    }
    public function active(Request $request)
    {
        $active = Product::find($request->hidden_id);
        $active->status = 1;
        $active->save();
        Toastr::success('Success', 'Data active successfully');
        return redirect()->back();
    }
    public function destroy(Request $request)
    {
        $delete_data = Product::find($request->hidden_id);
        foreach ($delete_data->variables as $variable) {
            File::delete($variable->image);
            $variable->delete();
        }
        foreach ($delete_data->images as $pimage) {
            File::delete($pimage->image);
            $pimage->delete();
        }
        $delete_data->delete();
        Toastr::success('Success', 'Data delete successfully');
        return redirect()->back();
    }
    public function imgdestroy(Request $request)
    {
        $delete_data = Productimage::find($request->id);
        File::delete($delete_data->image);
        $delete_data->delete();
        Toastr::success('Success', 'Data delete successfully');
        return redirect()->back();
    }
    public function pricedestroy(Request $request)
    {
        $delete_data = ProductVariable::find($request->id);
        File::delete($delete_data->image);
        $delete_data->delete();
        Toastr::success('Success', 'Product price delete successfully');
        return redirect()->back();
    }
    public function update_deals(Request $request)
    {
        $products = Product::whereIn('id', $request->input('product_ids'))->update(['topsale' => $request->status]);
        return response()->json(['status' => 'success', 'message' => 'Hot deals product status change']);
    }
    public function update_feature(Request $request)
    {
        $products = Product::whereIn('id', $request->input('product_ids'))->update(['feature_product' => $request->status]);
        return response()->json(['status' => 'success', 'message' => 'Feature product status change']);
    }
    public function update_status(Request $request)
    {
        $products = Product::whereIn('id', $request->input('product_ids'))->update(['status' => $request->status]);
        return response()->json(['status' => 'success', 'message' => 'Product status change successfully']);
    }
    public function barcode_update(Request $request)
    {
        $products = ProductVariable::whereIn('id', $request->input('product_ids'))->update(['status' => $request->status]);
        Toastr::success('Success', 'Data delete successfully');
        return redirect()->back();
    }
    
    public function barcodess(Request $request)
    {
        $products = ProductVariable::get();
        foreach ($products as $product) {
            $product->pro_barcode = str_pad($product->id, 8, '1', STR_PAD_LEFT); 
            $product->save();
        }
    }

    public function purchase_list(){   
        $purchase = PurchaseDetails::with('product')->latest()->paginate(100);
        return view('backEnd.product.purchase_list',compact('purchase'));
    }
    public function purchase_create(){   
        $data = Product::select('id', 'name', 'status', 'new_price', 'type')->latest()->get();
        return view('backEnd.product.purchase_create',compact('data'));
    }
    public function purchase_store(Request $request)
    {
        $product = Product::select('id', 'name','old_price', 'status', 'purchase_price', 'new_price', 'type')->where('id', $request->product_id)->first();
        if($product){
            $product = Product::select('id', 'name','old_price', 'status', 'purchase_price', 'new_price', 'type')->where('id', $request->product_id)->first();
            $product->stock =+ $request->qty;
            $product->save();

            $parchase                   = new PurchaseDetails();
            $parchase->product_id       = $product->id;
            $parchase->purchase_price   = $product->purchase_price;
            $parchase->old_price        = $product->old_price;
            $parchase->new_price        = $product->new_price;
            $parchase->stock            = $request->qty;
            $parchase->save();
            
        }else{
            $product = ProductVariable::where('id', $request->product_id)->first();  
            $product->stock =+ $request->qty;
            $product->save();

            $parchase                   = new PurchaseDetails();
            $parchase->product_id       = $product->product_id;
            $parchase->color            = $product->color;
            $parchase->size             = $product->size;
            $parchase->purchase_price   = $product->purchase_price;
            $parchase->old_price        = $product->old_price;
            $parchase->new_price        = $product->new_price;
            $parchase->stock            = $request->qty;
            $parchase->save();
        }
        Toastr::success('Success', 'Product purchase successfully');
        return redirect()->back();
    }
    public function purchase_history($id){   
        $purchase = PurchaseDetails::where('product_id',$id)->with('product')->latest()->get();
        return view('backEnd.product.purchase_history',compact('purchase'));
    }

    function barcode_generate(){
        $max_product = DB::table('products')->max(DB::raw('CAST(pro_barcode AS UNSIGNED)'));
        $max_variable = DB::table('product_variables')->max(DB::raw('CAST(pro_barcode AS UNSIGNED)'));
        $max_barcode = max($max_product, $max_variable);
        return $max_barcode ? $max_barcode + 1 : 100001;
    }
    public function product_sort(Request $request)
    {
        $data = Product::orderBy('sort')->select('id','name','category_id','new_price','topsale','feature_product','type','status','sort')->with('image', 'category')->withSum('variables', 'stock')->where('category_id',$request->category_id)->get();
        return view('backEnd.product.sort', compact('data'));
    }
    
    public function updateSort(Request $request)
    {
        $order = json_decode($request->input('order'), true); 
        // dd($request->input('order'));
        if (!is_array($order)) {
            Toastr::error('Invalid order data.', 'Sorry');
            return redirect()->back();
        }
        
        foreach ($order as $sortOrder => $id) {
            Product::where('id', $id)->update(['sort' => $sortOrder + 1]);
        }
        
        Toastr::success('Order updated Successfully!', 'Success');
        return redirect()->back();
    }

    
}
