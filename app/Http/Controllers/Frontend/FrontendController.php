<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Service\ShurjopayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Session;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use App\Models\Product;
use App\Models\District;
use App\Models\CreatePage;
use App\Models\Campaign;
use App\Models\Banner;
use App\Models\CouponCode;
use App\Models\ShippingCharge;
use App\Models\Customer;
use App\Models\OrderDetails;
use App\Models\ProductVariable;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Review;
use App\Models\Brand;
use App\Models\Size;
use App\Models\GeneralSetting;
use Cache;
use DB;
use Log;
class FrontendController extends Controller
{

    public function product_feed()
    {
        $products = Product::select('id', 'name', 'slug', 'description', 'stock', 'type', 'new_price', 'category_id', 'brand_id')
            ->with(['image', 'category', 'brand'])
            ->where('status', 1)
            ->get();

        $xml = new \SimpleXMLElement('<rss/>');
        $xml->addAttribute('version', '2.0');
        $xml->addAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        $setting = GeneralSetting::where('status', 1)->first();
        $channel = $xml->addChild('channel');
        $channel->addChild('title', "Marvelfashion Product Feed");
        $channel->addChild('link', 'https://marvelfashionbd.com');
        $channel->addChild('description', htmlspecialchars($setting->meta_description, ENT_XML1, 'UTF-8'));

        foreach ($products as $product) {
            $item = $channel->addChild('item');
            $item->addChild('g:id', $product->id, 'http://base.google.com/ns/1.0');
            $item->addChild('g:title', htmlspecialchars($product->name, ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
            $item->addChild('g:description', htmlspecialchars(strip_tags($product->description), ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
            $item->addChild('g:link', url('product/' . $product->slug), 'http://base.google.com/ns/1.0');

            if ($product->category_id) {
                $item->addChild('g:product_type', htmlspecialchars($product->category->name, ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
            }

            if ($product->image) {
                $item->addChild('g:image_link', 'https://marvelfashionbd.com/' . $product->image->image, 'http://base.google.com/ns/1.0');
            }

            $item->addChild('g:condition', 'new', 'http://base.google.com/ns/1.0');
            if ($product->type == 0) {
                $totalStock = $product->variables->sum('stock');
                $availability = $totalStock > 0 ? 'in stock' : 'out of stock';
            } else {
                $availability = $product->stock > 0 ? 'in stock' : 'out of stock';
            }

            $item->addChild('g:availability', $availability, 'http://base.google.com/ns/1.0');

            $item->addChild('g:price', $product->new_price . ' BDT', 'http://base.google.com/ns/1.0');

            if ($product->brand) {
                $item->addChild('g:brand', htmlspecialchars($product->brand->name, ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
            }

            $item->addChild('g:identifier_exists', 'yes', 'http://base.google.com/ns/1.0');
            $item->addChild('g:item_group_id', $product->id, 'http://base.google.com/ns/1.0');
            $item->addChild('g:visibility', 'active', 'http://base.google.com/ns/1.0');

            $variantAttribute = $item->addChild('g:additional_variant_attribute', null, 'http://base.google.com/ns/1.0');
            $variantAttribute->addChild('label', 'Size', 'http://base.google.com/ns/1.0');
            $variantAttribute->addChild('value', 'L', 'http://base.google.com/ns/1.0'); // Optional: dynamically assign size
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function index()
    {
        $sections = \App\Models\HomepageSection::where('status', 1)->orderBy('sort_order', 'asc')->get();

        // Loop through sections and load data if needed
        foreach ($sections as $section) {
            $params = $section->params ?? [];

            if ($section->section_key == 'product_grid' || $section->section_key == 'product_slider' || $section->section_key == 'product_with_banner') {
                $limit = $params['limit'] ?? 10;
                if (isset($params['product_ids']) && is_array($params['product_ids'])) {
                    $section->data = Product::whereIn('id', $params['product_ids'])
                        ->where('status', 1)
                        ->select('id', 'name', 'slug', 'new_price', 'old_price', 'type')
                        ->with('image')
                        ->withCount('variable')
                        ->take($limit)
                        ->get();
                } else {
                    // Fallback for old/generic types if needed, or empty
                    $section->data = collect([]);
                }
            } elseif ($section->section_key == 'category') {
                if (isset($params['category_id'])) {
                    $category = Category::find($params['category_id']);
                    if ($category) {
                        $section->category = $category;
                        $section->data = Product::where('category_id', $category->id)
                            ->where('status', 1)
                            ->select('id', 'name', 'slug', 'new_price', 'old_price', 'type')
                            ->with('image')
                            ->withCount('variable')
                            ->take(10)
                            ->get();
                    }
                }
            } elseif ($section->section_key == 'category_bar') {
                $section->data = Category::where(['front_view' => 1, 'status' => 1])
                    ->select('id', 'name', 'slug', 'front_view', 'status', 'image')
                    ->orderBy('id', 'ASC')
                    ->get();
            } elseif ($section->section_key == 'brand_slider') {
                $section->data = Brand::where(['status' => 1])->orderBy('id', 'ASC')->get();
            }
            // Slider, Banner, Collection rely on params directly in view without extra DB queries
        }

        return view('frontEnd.layouts.pages.index', compact('sections'));
    }

    public function category($slug, Request $request)
    {
        $category = Category::where(['slug' => $slug, 'status' => 1])->first();
        $products = Product::where(['status' => 1, 'category_id' => $category->id])
            ->select('id', 'name', 'slug', 'new_price', 'old_price', 'category_id', 'stock', 'coupon_id', 'type')
            ->with('reviews')->withCount('variable');
        $subcategories = Subcategory::where('category_id', $category->id)->get();
        $sizes = Size::where('status', 1)->get();

        // return $request->sort;
        if ($request->sort == 1) {
            $products = $products->orderBy('created_at', 'desc');
        } elseif ($request->sort == 2) {
            $products = $products->orderBy('created_at', 'asc');
        } elseif ($request->sort == 3) {
            $products = $products->orderBy('new_price', 'desc');
        } elseif ($request->sort == 4) {
            $products = $products->orderBy('new_price', 'asc');
        } elseif ($request->sort == 5) {
            $products = $products->orderBy('name', 'asc');
        } elseif ($request->sort == 6) {
            $products = $products->orderBy('name', 'desc');
        } else {
            $products = $products->orderBy('sort', 'asc');
        }

        $min_price = $products->min('new_price');
        $max_price = $products->max('new_price');

        $step = ($max_price - $min_price) / 5;
        $price_ranges = [];
        for ($i = 0; $i < 5; $i++) {
            $start = $min_price + ($step * $i);
            $end = $start + $step;
            $price_ranges[] = [
                'start' => round($start),
                'end' => round($end),
            ];
        }

        if ($request->min_price && $request->max_price) {
            $minPrices = $request->min_price;
            $maxPrices = $request->max_price;

            $products = $products->where(function ($query) use ($minPrices, $maxPrices) {
                foreach ($minPrices as $key => $min) {
                    $max = $maxPrices[$key];
                    $query->orWhere(function ($q) use ($min, $max) {
                        $q->where('new_price', '>=', $min)
                            ->where('new_price', '<=', $max);
                    });
                }
            });
        }

        $selectedSizes = $request->input('size', []);
        $products = $products->when($selectedSizes, function ($query) use ($selectedSizes) {
            return $query->whereHas('variable', function ($variableQuery) use ($selectedSizes) {
                $variableQuery->whereIn('size', $selectedSizes);
            });
        });

        $selectedGender = $request->input('gender', []);
        $products = $products->when($selectedGender, function ($query) use ($selectedGender) {
            return $query->whereIn('gender', $selectedGender);
        });

        $selectedSubcategories = $request->input('subcategory', []);
        $products = $products->when($selectedSubcategories, function ($query) use ($selectedSubcategories) {
            return $query->whereHas('subcategory', function ($subQuery) use ($selectedSubcategories) {
                $subQuery->whereIn('id', $selectedSubcategories);
            });
        });

        $products = $products->paginate(24);
        return view('frontEnd.layouts.pages.category', compact('category', 'products', 'subcategories', 'min_price', 'max_price', 'price_ranges', 'sizes'));
    }

    public function subcategory($slug, Request $request)
    {
        $subcategory = Subcategory::where(['slug' => $slug, 'status' => 1])->first();
        $products = Product::where(['status' => 1, 'subcategory_id' => $subcategory->id])
            ->select('id', 'name', 'slug', 'new_price', 'old_price', 'type', 'category_id', 'stock', 'subcategory_id', 'coupon_id')->withCount('variable');
        $sizes = Size::where('status', 1)->get();

        // return $request->sort;
        if ($request->sort == 1) {
            $products = $products->orderBy('created_at', 'desc');
        } elseif ($request->sort == 2) {
            $products = $products->orderBy('created_at', 'asc');
        } elseif ($request->sort == 3) {
            $products = $products->orderBy('new_price', 'desc');
        } elseif ($request->sort == 4) {
            $products = $products->orderBy('new_price', 'asc');
        } elseif ($request->sort == 5) {
            $products = $products->orderBy('name', 'asc');
        } elseif ($request->sort == 6) {
            $products = $products->orderBy('name', 'desc');
        } else {
            $products = $products->latest();
        }

        $min_price = $products->min('new_price');
        $max_price = $products->max('new_price');

        $step = ($max_price - $min_price) / 5;
        $price_ranges = [];
        for ($i = 0; $i < 5; $i++) {
            $start = $min_price + ($step * $i);
            $end = $start + $step;
            $price_ranges[] = [
                'start' => round($start),
                'end' => round($end),
            ];
        }

        if ($request->min_price && $request->max_price) {
            $minPrices = $request->min_price;
            $maxPrices = $request->max_price;

            $products = $products->where(function ($query) use ($minPrices, $maxPrices) {
                foreach ($minPrices as $key => $min) {
                    $max = $maxPrices[$key];
                    $query->orWhere(function ($q) use ($min, $max) {
                        $q->where('new_price', '>=', $min)
                            ->where('new_price', '<=', $max);
                    });
                }
            });
        }

        $selectedSizes = $request->input('size', []);
        $products = $products->when($selectedSizes, function ($query) use ($selectedSizes) {
            return $query->whereHas('variable', function ($variableQuery) use ($selectedSizes) {
                $variableQuery->whereIn('size', $selectedSizes);
            });
        });

        $selectedGender = $request->input('gender', []);
        $products = $products->when($selectedGender, function ($query) use ($selectedGender) {
            return $query->whereIn('gender', $selectedGender);
        });


        $products = $products->paginate(36)->withQueryString();

        return view('frontEnd.layouts.pages.subcategory', compact('subcategory', 'products', 'min_price', 'max_price', 'price_ranges', 'sizes'));
    }

    public function products($slug, Request $request)
    {
        $childcategory = Childcategory::where(['slug' => $slug, 'status' => 1])->first();
        $products = Product::where(['status' => 1, 'childcategory_id' => $childcategory->id])->with('category')
            ->select('id', 'name', 'slug', 'new_price', 'old_price', 'type', 'stock', 'category_id', 'subcategory_id', 'childcategory_id', 'coupon_id', 'type')->withCount('variable');

        if ($request->sort == 1) {
            $products = $products->orderBy('created_at', 'desc');
        } elseif ($request->sort == 2) {
            $products = $products->orderBy('created_at', 'asc');
        } elseif ($request->sort == 3) {
            $products = $products->orderBy('new_price', 'desc');
        } elseif ($request->sort == 4) {
            $products = $products->orderBy('new_price', 'asc');
        } elseif ($request->sort == 5) {
            $products = $products->orderBy('name', 'asc');
        } elseif ($request->sort == 6) {
            $products = $products->orderBy('name', 'desc');
        } else {
            $products = $products->latest();
        }

        $products = $products->paginate(36)->withQueryString();

        return view('frontEnd.layouts.pages.childcategory', compact('childcategory', 'products'));
    }

    public function brand($slug, Request $request)
    {
        $brand = Brand::where(['slug' => $slug, 'status' => 1])->first();
        $products = Product::where(['status' => 1, 'brand_id' => $brand->id])
            ->select('id', 'name', 'slug', 'new_price', 'old_price', 'stock', 'type', 'brand_id', 'coupon_id')->withCount('variable');
        if ($request->sort == 1) {
            $products = $products->orderBy('created_at', 'desc');
        } elseif ($request->sort == 2) {
            $products = $products->orderBy('created_at', 'asc');
        } elseif ($request->sort == 3) {
            $products = $products->orderBy('new_price', 'desc');
        } elseif ($request->sort == 4) {
            $products = $products->orderBy('new_price', 'asc');
        } elseif ($request->sort == 5) {
            $products = $products->orderBy('name', 'asc');
        } elseif ($request->sort == 6) {
            $products = $products->orderBy('name', 'desc');
        } else {
            $products = $products->latest();
        }
        $products = $products->paginate(36)->withQueryString();
        return view('frontEnd.layouts.pages.brand', compact('brand', 'products'));
    }

    public function bestdeals(Request $request)
    {

        $products = Product::where(['status' => 1, 'topsale' => 1])
            ->orderBy('id', 'DESC')
            ->select('id', 'name', 'slug', 'new_price', 'old_price', 'type', 'stock', 'coupon_id')
            ->withCount('variable');

        if ($request->sort == 1) {
            $products = $products->orderBy('created_at', 'desc');
        } elseif ($request->sort == 2) {
            $products = $products->orderBy('created_at', 'asc');
        } elseif ($request->sort == 3) {
            $products = $products->orderBy('new_price', 'desc');
        } elseif ($request->sort == 4) {
            $products = $products->orderBy('new_price', 'asc');
        } elseif ($request->sort == 5) {
            $products = $products->orderBy('name', 'asc');
        } elseif ($request->sort == 6) {
            $products = $products->orderBy('name', 'desc');
        } else {
            $products = $products->latest();
        }
        $products = $products->paginate(36)->withQueryString();

        return view('frontEnd.layouts.pages.bestdeals', compact('products'));
    }
    public function all_collection(Request $request)
    {

        $products = Product::where(['status' => 1])
            ->orderBy('id', 'DESC')
            ->select('id', 'name', 'slug', 'new_price', 'old_price', 'type', 'stock', 'coupon_id')
            ->withCount('variable');

        if ($request->sort == 1) {
            $products = $products->orderBy('created_at', 'desc');
        } elseif ($request->sort == 2) {
            $products = $products->orderBy('created_at', 'asc');
        } elseif ($request->sort == 3) {
            $products = $products->orderBy('new_price', 'desc');
        } elseif ($request->sort == 4) {
            $products = $products->orderBy('new_price', 'asc');
        } elseif ($request->sort == 5) {
            $products = $products->orderBy('name', 'asc');
        } elseif ($request->sort == 6) {
            $products = $products->orderBy('name', 'desc');
        } else {
            $products = $products->latest();
        }
        $products = $products->paginate(36)->withQueryString();

        return view('frontEnd.layouts.pages.shop', compact('products'));
    }


    public function details($slug)
    {

        $details = Product::where(['slug' => $slug, 'status' => 1])
            ->with('image', 'images', 'category', 'subcategory', 'childcategory')
            ->withCount('variableimages', 'variable')
            ->firstOrFail();

        $discount = $details->old_price > 0
            ? (($details->old_price - $details->new_price) * 100) / $details->old_price
            : 0;
        $targetDiscount = round($discount);

        $products = Product::where(['category_id' => $details->category_id, 'status' => 1])
            ->with('image')
            ->select('id', 'name', 'slug', 'status', 'category_id', 'new_price', 'old_price', 'stock', 'type', 'coupon_id')
            ->withCount('variable')
            ->get();

        $products = $products->filter(function ($p) use ($targetDiscount) {
            $p->discount1 = $p->old_price > 0
                ? round((($p->old_price - $p->new_price) * 100) / $p->old_price)
                : null;

            return $p->discount1 !== null && $p->discount1 === $targetDiscount;
        })
            ->values();


        $shippingcharge = ShippingCharge::where('status', 1)->get();
        $reviews = Review::where('product_id', $details->id)->where('status', 'active')->get();

        $productcolors = ProductVariable::where('product_id', $details->id)->where('stock', '>', 0)
            ->whereNotNull('color')
            ->select('color')
            ->distinct()
            ->get();

        $productsizes = ProductVariable::where('product_id', $details->id)->where('stock', '>', 0)
            ->whereNotNull('size')
            ->select('size')
            ->distinct()
            ->get();

        return view('frontEnd.layouts.pages.details', compact('details', 'products', 'shippingcharge', 'productcolors', 'productsizes', 'reviews'));
    }
    public function stock_check(Request $request)
    {
        $product = ProductVariable::where(['product_id' => $request->id, 'color' => $request->color, 'size' => $request->size])->first();

        $status = $product ? true : false;
        $response = [
            'status' => $status,
            'product' => $product
        ];
        return response()->json($response);
    }

    public function reviewPopup(Request $request)
    {
        $review = Review::where(['id' => $request->id, 'status' => 'active'])->first();
        $rev_product = Product::where(['id' => $review->product_id, 'status' => 1])->with('images')->first();
        $customer = Customer::where(['id' => $review->customer_id])->first();
        return view('frontEnd.layouts.ajax.reviewPopup', compact('rev_product', 'review', 'customer'));

    }

    public function quickView(Request $request)
    {
        $details = Product::where(['id' => $request->id, 'status' => 1])
            ->with('image', 'images', 'category', 'subcategory', 'childcategory')
            ->withCount('variableimages', 'variable')
            ->firstOrFail();

        $productcolors = ProductVariable::where('product_id', $details->id)->where('stock', '>', 0)
            ->whereNotNull('color')
            ->select('color')
            ->distinct()
            ->get();

        $productsizes = ProductVariable::where('product_id', $details->id)->where('stock', '>', 0)
            ->whereNotNull('size')
            ->select('size')
            ->distinct()
            ->get();

        return view('frontEnd.layouts.ajax.quickview', compact('details', 'productcolors', 'productsizes'));
    }

    public function livesearch(Request $request)
    {
        $products = Product::select('id', 'name', 'slug', 'stock', 'type', 'new_price', 'old_price')
            ->where('status', 1)
            ->with('image');
        if ($request->keyword) {
            $products = $products->where('name', 'LIKE', '%' . $request->keyword . "%")
                ->Orwhere('sku', 'LIKE', '%' . $request->keyword . "%");
        }
        if ($request->category) {
            $products = $products->where('category_id', $request->category);
        }
        $product_count = $products->count();
        $products = $products->get();
        if (empty($request->category) && empty($request->keyword)) {
            $products = [];
        }
        $category = $request->category;
        $keyword = $request->keyword;
        return view('frontEnd.layouts.ajax.search', compact('products', 'product_count', 'category', 'keyword'));
    }
    public function search(Request $request)
    {
        $products = Product::where(['status' => 1])
            ->where('old_price', '>', 'new_price')
            ->select('id', 'name', 'slug', 'new_price', 'stock', 'type', 'old_price', 'category_id', 'topsale');

        // return $request->sort;
        if ($request->sort == 1) {
            $products = $products->orderBy('created_at', 'desc');
        } elseif ($request->sort == 2) {
            $products = $products->orderBy('created_at', 'asc');
        } elseif ($request->sort == 3) {
            $products = $products->orderBy('new_price', 'desc');
        } elseif ($request->sort == 4) {
            $products = $products->orderBy('new_price', 'asc');
        } elseif ($request->sort == 5) {
            $products = $products->orderBy('name', 'asc');
        } elseif ($request->sort == 6) {
            $products = $products->orderBy('name', 'desc');
        } else {
            $products = $products->latest();
        }
        if ($request->keyword) {
            $products = $products->where('name', 'LIKE', '%' . $request->keyword . "%");
        }
        if ($request->category) {
            $products = $products->where('category_id', $request->category);
        }
        $products = $products->paginate(36);
        $impproducts = Product::where(['status' => 1, 'topsale' => 1])
            ->with('image')
            ->limit(6)
            ->select('id', 'name', 'slug')
            ->get();
        $keyword = $request->keyword;
        return view('frontEnd.layouts.pages.search', compact('products', 'keyword', 'impproducts'));
    }


    public function shipping_charge(Request $request)
    {
        $subtotal = Cart::instance('shopping')->subtotal();
        $subtotal = str_replace(',', '', $subtotal);
        $subtotal = str_replace('.00', '', $subtotal);
        Session::put('shipping_id', $request->id);
        if ($subtotal >= 3000) {
            Session::put('shipping', 0);
        } else {
            $shipping_id = Session::get('shipping_id');
            $shipping = ShippingCharge::where(['id' => $shipping_id])->first();
            Session::put('shipping', $shipping->amount);
        }

        $data = Cart::instance('shopping')->content();
        if ($request->campaign == 1) {
            return view('frontEnd.layouts.ajax.cart_camp', compact('data'));
        }
        return view('frontEnd.layouts.ajax.cart', compact('data'));
    }


    public function contact(Request $request)
    {
        return view('frontEnd.layouts.pages.contact');
    }

    public function page($slug)
    {
        $page = CreatePage::where('slug', $slug)->firstOrFail();
        return view('frontEnd.layouts.pages.page', compact('page'));
    }
    public function districts(Request $request)
    {
        $areas = District::where(['district' => $request->id])->pluck('area_name', 'id');
        return response()->json($areas);
    }
    public function campaign($slug, Request $request)
    {

        $campaign = Campaign::where('slug', $slug)->with('images')->first();
        $product = Product::select('id', 'name', 'slug', 'new_price', 'old_price', 'purchase_price', 'type', 'stock', 'coupon_id')->where(['id' => $campaign->product_id])->first();
        $productcolors = ProductVariable::where('product_id', $campaign->product_id)->where('stock', '>', 0)
            ->whereNotNull('color')
            ->select('color')
            ->distinct()
            ->get();

        $productsizes = ProductVariable::where('product_id', $campaign->product_id)->where('stock', '>', 0)
            ->whereNotNull('size')
            ->select('size')
            ->distinct()
            ->get();

        Cart::instance('shopping')->destroy();

        $var_product = ProductVariable::where(['product_id' => $campaign->product_id])->first();
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
        $cartitem = Cart::instance('shopping')->content()->where('id', $product->id)->first();

        Cart::instance('shopping')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => $qty,
            'weight' => 1,
            'price' => $new_price,
            'options' => [
                'slug' => $product->slug,
                'image' => $product->image->image,
                'old_price' => $new_price,
                'purchase_price' => $purchase_price,
                'product_size' => $request->product_size,
                'product_color' => $request->product_color,
                'type' => $product->type,
                'coupon_id' => $product->coupon_id,
            ],
        ]);
        $districts = District::distinct()->select('id', 'name')->orderBy('id', 'asc')->get();
        Session::put('shipping', 0);
        return view('frontEnd.layouts.pages.campaign.campaign' . $campaign->template, compact('campaign', 'productsizes', 'productcolors', 'districts', 'old_price', 'new_price'));


    }
    public function campaign_stock(Request $request)
    {
        $product = Product::select('id', 'name', 'slug', 'new_price', 'old_price', 'purchase_price', 'type', 'stock')->where(['id' => $request->id])->first();

        $variable = ProductVariable::where(['product_id' => $request->id, 'color' => $request->color, 'size' => $request->size])->first();
        $qty = 1;
        $status = $variable ? true : false;

        if ($status == true) {
            Cart::instance('shopping')->destroy();
            Cart::instance('shopping')->add([
                'id' => $product->id,
                'name' => $product->name,
                'qty' => $qty,
                'weight' => 1,
                'price' => $variable->new_price,
                'options' => [
                    'slug' => $product->slug,
                    'image' => $product->image->image,
                    'old_price' => $variable->new_price,
                    'purchase_price' => $variable->purchase_price,
                    'product_size' => $request->size,
                    'product_color' => $request->color,
                    'type' => $product->type,
                    'coupon_id' => $product->coupon_id
                ],
            ]);
        }
        $data = Cart::instance('shopping')->content();
        return response()->json($status);
    }

    public function payment_success(Request $request)
    {
        $order_id = $request->order_id;
        $shurjopay_service = new ShurjopayService();
        $json = $shurjopay_service->verify($order_id);
        $data = json_decode($json);

        if ($data[0]->sp_code != 1000) {
            Toastr::error('Your payment failed, try again', 'Oops!');
            if ($data[0]->value1 == 'customer_payment') {
                return redirect()->route('home');
            } else {
                return redirect()->route('home');
            }
        }

        if ($data[0]->value1 == 'customer_payment') {

            $customer = Customer::find(Auth::guard('customer')->user()->id);

            // order data save
            $order = new Order();
            $order->invoice_id = $data[0]->id;
            $order->amount = $data[0]->amount;
            $order->customer_id = Auth::guard('customer')->user()->id;
            $order->order_status = $data[0]->bank_status;
            $order->save();

            // payment data save
            $payment = new Payment();
            $payment->order_id = $order->id;
            $payment->customer_id = Auth::guard('customer')->user()->id;
            $payment->payment_method = 'shurjopay';
            $payment->amount = $order->amount;
            $payment->trx_id = $data[0]->bank_trx_id;
            $payment->sender_number = $data[0]->phone_no;
            $payment->payment_status = 'paid';
            $payment->save();
            // order details data save
            foreach (Cart::instance('shopping')->content() as $cart) {
                $order_details = new OrderDetails();
                $order_details->order_id = $order->id;
                $order_details->product_id = $cart->id;
                $order_details->product_name = $cart->name;
                $order_details->purchase_price = $cart->options->purchase_price;
                $order_details->sale_price = $cart->price;
                $order_details->qty = $cart->qty;
                $order_details->save();
            }

            Cart::instance('shopping')->destroy();
            Toastr::error('Thanks, Your payment send successfully', 'Success!');
            return redirect()->route('home');
        }

        Toastr::error('Something wrong, please try agian', 'Error!');
        return redirect()->route('home');
    }
    public function payment_cancel(Request $request)
    {
        $order_id = $request->order_id;
        $shurjopay_service = new ShurjopayService();
        $json = $shurjopay_service->verify($order_id);
        $data = json_decode($json);

        Toastr::error('Your payment cancelled', 'Cancelled!');
        if ($data[0]->sp_code != 1000) {
            if ($data[0]->value1 == 'customer_payment') {
                return redirect()->route('home');
            } else {
                return redirect()->route('home');
            }
        }
    }


}
